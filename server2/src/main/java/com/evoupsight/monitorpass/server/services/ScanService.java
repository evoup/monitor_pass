package com.evoupsight.monitorpass.server.services;

import com.evoupsight.monitorpass.server.cache.FunctionCache;
import com.evoupsight.monitorpass.server.cache.ItemCache;
import com.evoupsight.monitorpass.server.cache.ServerCache;
import com.evoupsight.monitorpass.server.cache.TriggerCache;
import com.evoupsight.monitorpass.server.dao.mapper.EventMapper;
import com.evoupsight.monitorpass.server.dao.mapper.RelationServerServerGroupMapper;
import com.evoupsight.monitorpass.server.dao.mapper.RelationTemplateServerGroupMapper;
import com.evoupsight.monitorpass.server.dao.model.*;
import com.evoupsight.monitorpass.server.dto.memcache.HostTemplateDto;
import com.evoupsight.monitorpass.server.dto.memcache.TriggerDto;
import com.evoupsight.monitorpass.server.dto.opentsdb.QueryDto;
import com.evoupsight.monitorpass.server.exporession.MainVisitor;
import com.evoupsight.monitorpass.server.exporession.TriggerLexer;
import com.evoupsight.monitorpass.server.exporession.TriggerParser;
import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;
import org.antlr.v4.runtime.ANTLRInputStream;
import org.antlr.v4.runtime.CharStream;
import org.antlr.v4.runtime.CommonTokenStream;
import org.antlr.v4.runtime.tree.ParseTree;
import org.apache.commons.collections.CollectionUtils;
import org.apache.commons.lang.StringUtils;
import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.hbase.TableName;
import org.apache.hadoop.hbase.client.Connection;
import org.apache.hadoop.hbase.client.ConnectionFactory;
import org.apache.hadoop.hbase.client.Put;
import org.apache.hadoop.hbase.client.Table;
import org.apache.hadoop.hbase.util.Bytes;
import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.util.EntityUtils;
import org.joda.time.Period;
import org.joda.time.format.PeriodFormatter;
import org.joda.time.format.PeriodFormatterBuilder;
import org.opentsdb.client.PoolingHttpClient;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.stereotype.Service;
import redis.clients.jedis.Jedis;
import redis.clients.jedis.JedisPool;

import java.io.IOException;
import java.io.InputStream;
import java.util.*;
import java.util.regex.Matcher;
import java.util.regex.Pattern;
import java.util.stream.Collectors;

import static com.evoupsight.monitorpass.server.constants.Constants.*;
import static com.evoupsight.monitorpass.server.constants.Constants.ServerStatus.NOT_MONITORING;


/**
 * @author evoup
 */
@SuppressWarnings({"Duplicates", "SpringJavaAutowiredFieldsWarningInspection", "SpringJavaInjectionPointsAutowiringInspection"})
@Service
public class ScanService {
    private static final Logger LOG = LoggerFactory.getLogger(ScanService.class);
    @Value("${opentsdb.serverurl}")
    private String opentsdbUrl;

    private final Configuration hbaseConf;

    private final JedisPool jedisPool;

    private final PoolingHttpClient httpClient;

    @Autowired
    private ScanService(JedisPool jedisPool, Configuration hbaseConf, PoolingHttpClient httpClient) {
        this.jedisPool = jedisPool;
        this.hbaseConf = hbaseConf;
        this.httpClient = httpClient;
    }

    @Autowired
    private TriggerCache triggerCache;
    @Autowired
    private ServerCache serverCache;
    @Autowired
    private FunctionCache functionCache;
    @Autowired
    private ItemCache itemCache;
    @Autowired
    private RelationServerServerGroupMapper relationServerServerGroupMapper;
    @Autowired
    private RelationTemplateServerGroupMapper relationTemplateServerGroupMapper;
    @Autowired
    private EventMapper eventMapper;

    /**
     * 执行所有工作
     */
    void doAllJobs() {
        // TODO写入扫描时间

        checkHosts();
    }

    /**
     * 保存上次扫描时间(Hbase中，已经没用了)
     *
     * @throws IOException 异常
     */
    @Deprecated
    private void saveHBaseLastScanTime() throws IOException {
        LOG.debug("save scan time");
        try (Connection connection = ConnectionFactory.createConnection(hbaseConf);
             Table table = connection.getTable(TableName.valueOf(MDB_TAB_ENGINE))) {
            Put p = new Put(Bytes.toBytes(KEY_SCAN_DURATION));
            p.addColumn(Bytes.toBytes("scan"), Bytes.toBytes("duration"), Bytes.toBytes(System.currentTimeMillis()));
            table.put(p);
        }
    }


    /**
     * 检查主机
     */
    private void checkHosts() {
        // server -> server_group -> template -> trigger -> function -> 计算function数值 -> 返回function表达式 -> 判断真假
        List<Server> servers = serverCache.fetchAll();
        servers.stream().filter(Objects::nonNull).filter(s -> !new Integer(NOT_MONITORING.ordinal()).equals(s.getStatus())).forEach(s -> {
            RelationServerServerGroupExample example = new RelationServerServerGroupExample();
            example.createCriteria().andServerIdEqualTo(s.getId());
            List<RelationServerServerGroup> relationServerServerGroups = relationServerServerGroupMapper.selectByExample(example);
            if (CollectionUtils.isNotEmpty(relationServerServerGroups)) {
                for (RelationServerServerGroup relation : relationServerServerGroups) {
                    if (s.getId().equals(relation.getServerId())) {
                        Integer servergroupId = relation.getServergroupId();
                        RelationTemplateServerGroupExample example1 = new RelationTemplateServerGroupExample();
                        example1.createCriteria().andServergroupIdEqualTo(servergroupId);
                        List<RelationTemplateServerGroup> relationTemplateServerGroup = relationTemplateServerGroupMapper.selectByExample(example1);
                        for (RelationTemplateServerGroup relation1 : relationTemplateServerGroup) {
                            Long templateId = relation1.getTemplateId();
                            List<Trigger> triggers = triggerCache.getByTemplate(templateId);
                            if (CollectionUtils.isNotEmpty(triggers)) {
                                for (Trigger trigger : triggers) {
                                    String expression = trigger.getExpression();
                                    // 找出表达式中的function，进行演算
                                    Pattern p = Pattern.compile("\\{([^}]*)\\}");
                                    Matcher m = p.matcher(expression);
                                    StringBuffer sb = new StringBuffer();
                                    while (m.find()) {
                                        m.appendReplacement(sb, getOpentsdbValue(m.group(1), s));
                                    }
                                    m.appendTail(sb);
                                    LOG.info("key是：" + trigger.getExpression());
                                    LOG.info("最终表达式是：" + sb.toString());
                                    if (antlrTrueFalse(sb.toString())) {
                                        LOG.warn("条件成立，进入事件逻辑");
                                        processEvent(trigger);
                                        LOG.info("事件逻辑结束");
                                    }
                                }
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * 检查事件，是否存在该事件，事件是否已经恢复
     *
     * @param trigger
     */
    private void processEvent(Trigger trigger) {
        // 选择最近的一条事件记录
        // 1.如果不存在事件，则生成事件
        // 2.如果存在事件，事件已经恢复（且超过5分钟），则新建事件
        List<Event> events = eventMapper.selectByExample(
                new EventExample().limit(1).createCriteria().andTargetIdEqualTo(trigger.getId().intValue())
                        .example().orderBy(Event.Column.time.desc())
        );
        if (CollectionUtils.isEmpty(events)) {
            Event event = Event.builder().event("").time(new Long(System.currentTimeMillis() / 1000).intValue()).acknowledged(false).targetId(trigger.getId().intValue()).type(EventState.PROBLEM.ordinal()).build();
            eventMapper.insertSelective(event);
        } else if (events.get(0).getType() != null && events.get(0).getType() > EventState.OK.ordinal()) {
            Event event = Event.builder().event("").time(new Long(System.currentTimeMillis() / 1000).intValue()).acknowledged(false).targetId(trigger.getId().intValue()).type(EventState.PROBLEM.ordinal()).build();
            eventMapper.insertSelective(event);
        }

    }

    /**
     * 返回opentsdb的数值
     */
    private String getOpentsdbValue(String functionId, Server server) {
        LOG.info("functionId:" + functionId);
        String dbValue = "";
        Function f = functionCache.get(new Long(functionId));
        if (f != null) {
            {
                // f.name = 'avg'  f.parameter = '5m'
                PeriodFormatter formatter = new PeriodFormatterBuilder()
                        .appendDays().appendSuffix("d ")
                        .appendHours().appendSuffix("h ")
                        .appendMinutes().appendSuffix("m")
                        .toFormatter();
                Period period = null;
                try {
                    period = formatter.parsePeriod(f.getParameter());
                } catch (Exception e) {
                    LOG.warn(f.getParameter() + "不是时间参数");
                }
                // 获取监控项的数值
                Integer itemId = f.getItemId();
                Item item = itemCache.get(itemId);
                HttpGet httpGet;
                try {
                    String key = item.getKey();
                    String minutes = period != null ? String.valueOf(period.getMinutes()) : "15";
                    String apiUrl = opentsdbUrl + "/api/query?start=" + minutes + "m-ago&m=sum:apps.backend." + server.getName() + "." + key;
                    httpGet = new HttpGet(apiUrl);
                    HttpResponse httpResponse = httpClient.execute(httpGet);
                    if (httpResponse != null && httpResponse.getStatusLine().getStatusCode() == 200) {
                        HttpEntity entity = httpResponse.getEntity();
                        // 将entity当中的数据转换为字符串
                        String response = EntityUtils.toString(entity, "utf-8");
                        ArrayList<QueryDto> queryDtos = new Gson().fromJson(response, new TypeToken<ArrayList<QueryDto>>() {
                        }.getType());
                        if (queryDtos != null && queryDtos.size() > 0 && queryDtos.get(0).getDps() != null && queryDtos.get(0).getDps().size() > 0) {
                            HashMap<String, Object> dataPoints = queryDtos.get(0).getDps();
                            for (Map.Entry<String, Object> entry : dataPoints.entrySet()) {
//                                dbValue = entry.getValue().toString();
                                // 在线
                                serverCache.makeUp(server.getId());
                                break;
                            }
                            List<Double> primes = dataPoints.entrySet().stream().filter(Objects::nonNull).map(x -> (Double) x.getValue()).collect(Collectors.toList());
                            DoubleSummaryStatistics stats = primes.stream()
                                    .mapToDouble((p) -> p)
                                    .summaryStatistics();
                            return Double.toString(stats.getAverage());
//                            return dbValue;
                        } else {
                            // 宕机
                            serverCache.makeDown(server.getId());
                        }
                    }
                } catch (IOException e) {
                    LOG.error(e.getMessage(), e);
                }
            }
        }
        return "";
    }

    /**
     * 通过antlr判断逻辑真假
     *
     * @param expression
     * @return
     */
    private Boolean antlrTrueFalse(String expression) {
        try {
            CharStream input = new ANTLRInputStream(expression);
            TriggerLexer lexer = new TriggerLexer(input);
            CommonTokenStream tokens = new CommonTokenStream(lexer);
            TriggerParser parser = new TriggerParser(tokens);
            ParseTree tree = parser.expr();
            MainVisitor.Visitor eval = new MainVisitor.Visitor();
            Object visit = eval.visit(tree);
            LOG.info("Trigger result:" + visit);
            System.out.println("check done");
            return Boolean.valueOf(visit.toString());
        } catch (Exception e) {
            LOG.error(e.getMessage(), e);
            return false;
        }

    }

    /**
     * 返回指定trigger对应的服务器
     *
     * @param triggerId
     * @return
     */
    private List<Server> getTriggerBelongServers(Integer triggerId) {
        List<Trigger> triggers = triggerCache.fetchAll();
        Set<Server> checkServers = new HashSet<>();
        triggers.stream().filter(Objects::nonNull).filter(t -> triggerId.equals(t.getId().intValue())).forEach(x -> {
            System.out.println(x.getTemplateId());
            Long templateId = x.getTemplateId();
            List<Server> servers = serverCache.getByTemplate(templateId);
            servers.stream().filter(Objects::nonNull).forEach(checkServers::add);
        });
        checkServers.stream().filter(Objects::nonNull).forEach(s -> {
            LOG.info("server:{} will be checked", s.getName());
        });
        return new ArrayList<>(checkServers);
    }

    /**
     * 检查是否宕机
     */
    @Deprecated
    private void scanHostDownOld() {
        HttpResponse httpResponse = null;
        Gson gson = new Gson();
        try (Jedis resource = jedisPool.getResource()) {
            String value1 = resource.get("key1");
            List<HostTemplateDto> hostTemplateDtos = new Gson().fromJson(value1,
                    new TypeToken<ArrayList<HostTemplateDto>>() {
                    }.getType());
            if (hostTemplateDtos != null) {
                for (HostTemplateDto hostTemplateDto : hostTemplateDtos) {
                    String hostStatus = HOST_STATUS_DOWN;
                    String host = hostTemplateDto.getHost();
                    if (StringUtils.isNotEmpty(host)) {
                        String myhost = StringUtils.remove(host, "-");
                        String apiUrl = opentsdbUrl +
                                "/api/query?start=5m-ago&m=sum:apps.backend." + myhost +
                                ".proc.loadavg.5min%7Bhost=" + host + "%7D";
                        HttpGet httpGet = new HttpGet(apiUrl);
                        httpResponse = httpClient.execute(httpGet);
                        if (httpResponse != null && httpResponse.getStatusLine().getStatusCode() == 200) {
                            HttpEntity entity = httpResponse.getEntity();
                            //将entity当中的数据转换为字符串
                            String response = EntityUtils.toString(entity, "utf-8");
                            ArrayList<QueryDto> queryDtos = gson.fromJson(response, new TypeToken<ArrayList<QueryDto>>() {
                            }.getType());
                            if (queryDtos != null && queryDtos.size() > 0 && queryDtos.get(0).getDps() != null && queryDtos.get(0).getDps().size() > 0) {
                                hostStatus = HOST_STATUS_UP;
                            }
                        }
                    }
                    if (hostStatus.equals(HOST_STATUS_UP)) {
                        goThroughTriggers(host, hostTemplateDto.getTemplateIds());
                    }
                    // saveHbaseHostStatus(hostStatus, host);
                }
            }
        } catch (IOException e) {
            LOG.error(e.getMessage(), e);
        } finally {
            releaseResponse(httpResponse);
        }
    }


    /**
     * 检查表达式
     */
    private void goThroughTriggers(String host, List<String> templateIds) {
        try (Jedis resource = jedisPool.getResource()) {
            String value = resource.get("key6");
            // key为triggerid
            HashMap<String, TriggerDto> triggerDtos = new Gson().fromJson(value,
                    new TypeToken<HashMap<String, TriggerDto>>() {
                    }.getType());
            // key为triggerid，用不到
            if (triggerDtos != null) {
                triggerDtos.forEach((triggerid, triggerinfo) -> runExpression(host, templateIds, triggerinfo));
            }
        }
    }

    /**
     * 执行表达式
     *
     * @param host            　主机名
     * @param hostTemplateIds 　主机对应的模板
     * @param trigger         　触发器
     */
    private void runExpression(String host, List<String> hostTemplateIds, TriggerDto trigger) {
        if (trigger != null && StringUtils.isNotEmpty(trigger.getHostId())) {
            if (hostTemplateIds.contains(trigger.getHostId())) {
                String myhost = StringUtils.remove(host, "-");
                LOG.info("host:{} call expression:{}", host, trigger.getExpression());
                // {13078}>5 <=> system.cpu.load[allcpu,avg1].avg(5m)>5 => proc.loadavg.1min>5
                // 例子：如果表达式为{13078}>5，视作是查询proc.loadavg.1min，函数为avg,参数为5m，就是调用
                // opentsdb的http://opentsdb2:14242/api/query?start=5m-ago&m=avg:apps.backend.evoupzhanqi.proc.loadavg.1min
                if ("{13078}>5".equals(trigger.getExpression())) {
                    HttpResponse httpResponse = null;
                    HttpGet httpGet = new HttpGet(opentsdbUrl +
                            "/api/query?start=5m-ago&m=sum:apps.backend." + myhost +
                            ".proc.loadavg.1min");
                    try {
                        httpResponse = httpClient.execute(httpGet);
                        if (httpResponse != null && httpResponse.getStatusLine().getStatusCode() == 200) {
                            HttpEntity entity = httpResponse.getEntity();
                            //将entity当中的数据转换为字符串
                            String response = EntityUtils.toString(entity, "utf-8");
                            ArrayList<QueryDto> queryDtos = new Gson().fromJson(response, new TypeToken<ArrayList<QueryDto>>() {
                            }.getType());
                            if (queryDtos != null && queryDtos.get(0).getDps() != null) {
                                List<Object> list = new ArrayList<>();
                                queryDtos.get(0).getDps().forEach((time, datapoint) -> list.add(datapoint));
                                DoubleSummaryStatistics stats = list.stream().mapToDouble((x) -> new Double(x.toString())).summaryStatistics();
                                double average = stats.getAverage();
                                LOG.info("average:" + average);
                                CharStream input = new ANTLRInputStream("{" + average + "}>0.82 AND TRUE");
                                TriggerLexer lexer = new TriggerLexer(input);
                                CommonTokenStream tokens = new CommonTokenStream(lexer);
                                TriggerParser parser = new TriggerParser(tokens);
                                ParseTree tree = parser.expr();
                                MainVisitor.Visitor eval = new MainVisitor.Visitor();
                                Object visit = eval.visit(tree);
                                LOG.info("Trigger result:" + visit);
                            }
                        }
                    } catch (IOException e) {
                        LOG.error(e.getMessage(), e);
                    } finally {
                        releaseResponse(httpResponse);
                    }
                }
            }
        }
    }


    /**
     * 保存host状态
     *
     * @param hostStatus host状态
     * @param host       　host名字
     */
    @Deprecated
    private void saveHbaseHostStatus(String hostStatus, String host) {
        try (Connection connection = ConnectionFactory.createConnection(hbaseConf);
             Table table = connection.getTable(TableName.valueOf(MDB_TAB_HOST))) {
            Put p = new Put(Bytes.toBytes(host));
            p.addColumn(Bytes.toBytes("info"), Bytes.toBytes("status"), Bytes.toBytes(hostStatus));
            table.put(p);
        } catch (Exception e) {
            LOG.error(e.getMessage(), e);
        }
    }


    /**
     * 清理
     *
     * @param rp 回复
     */
    private void releaseResponse(HttpResponse rp) {
        if (rp != null) {
            try {
                HttpEntity entity = rp.getEntity();
                if (entity != null) {
                    InputStream instream = entity.getContent();
                    try {
                        // do something useful
                    } finally {
                        instream.close();
                    }
                }
            } catch (Exception e) {
                LOG.error(e.getMessage(), e);
            }
        }
    }
}
