package com.evoupsight.monitorpass.datacollector.sender;


import com.evoupsight.monitorpass.constants.ServerStatusEnum;
import com.evoupsight.monitorpass.datacollector.dao.mapper.ServerMapper;
import com.evoupsight.monitorpass.datacollector.dao.model.DataCollector;
import com.evoupsight.monitorpass.datacollector.dao.model.Server;
import com.evoupsight.monitorpass.datacollector.services.DataCollectorService;
import com.evoupsight.monitorpass.datacollector.services.ServerService;
import com.google.common.cache.LoadingCache;
import org.apache.commons.lang.StringUtils;
import org.opentsdb.client.PoolingHttpClient;
import org.opentsdb.client.builder.MetricBuilder;
import org.opentsdb.client.response.SimpleHttpResponse;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.beans.factory.annotation.Qualifier;
import org.springframework.stereotype.Component;

import javax.annotation.PostConstruct;
import java.io.IOException;
import java.util.Date;
import java.util.HashMap;
import java.util.Map;

/**
 * @author evoup
 */

/**
 * 发送数据到opentsdb
 *
 * 加component是注入失败，要么从spring的上下文获取bean，要么采用本类做成组件，定义构造函数，在初始化该类的时候，
 * 预先加载到本类的成员变量里的变通方式
 *
 * @author evoup
 */
@SuppressWarnings({"unused"})
@Component
public class Sender {

    private PoolingHttpClient httpClient;
    private String message;
    private String opentsdbServerUrl;
    private String dataCollectorServerName;
    private static final Logger LOG = LoggerFactory.getLogger(Sender.class);

    @Autowired
    private ServerService serverService;
    @Autowired
    private DataCollectorService dataCollectorService;
    @Autowired
    private ServerMapper serverMapper;
    @Autowired
    @Qualifier("guava_server_cache")
    private LoadingCache loadingCache;

    private static Sender sender;

    public Sender() {
    }

    /**
     * 解决注入问题
     */
    @PostConstruct
    public void init() {
        sender = this;
        sender.serverService = this.serverService;
        sender.dataCollectorService = this.dataCollectorService;
        sender.serverMapper = this.serverMapper;
        sender.loadingCache = this.loadingCache;
    }

    public Sender(String message, String opentsdbServerUrl, PoolingHttpClient httpClient, String dataCollectorServerName) {
        this.message = message;
        this.opentsdbServerUrl = opentsdbServerUrl;
        this.httpClient = httpClient;
        this.dataCollectorServerName = dataCollectorServerName;
    }

    public void myProcessMsgBag() throws IOException {
        String m = this.message;
        String opentsdbServerUrl = this.opentsdbServerUrl;
        //HttpClient client = new HttpClientImpl(opentsdbServerUrl);
        //MetricBuilder builder = MetricBuilder.getInstance();
        // put proc.loadavg.1m 1524995898 1.13 host=evoup-Inspiron-3443
        // put metric value tagkey=tagvalue
        // proc.uptime.total 1525004232 27925.52
        // procstats proc.uptime.total 1525005860 29552.60 host=montioragent2
        // sysload cpu.idle 1525009928 69.80 cpu=0 host=monitoragent2
        if (!StringUtils.isEmpty(m)) {
            String[] split = m.split(" ");
            String metricKey = split[1];
            int timeStamp;
            try {
                timeStamp = Integer.parseInt(split[2]);
            } catch (NumberFormatException | ArrayIndexOutOfBoundsException e) {
                return;
            }
            double value;
            try {
                value = Double.parseDouble(split[3]);
            } catch (NumberFormatException | ArrayIndexOutOfBoundsException e) {
                return;
            }
            Map<String, String> map = new HashMap<>();
            // 第五位开始是tag=tag_value
            for (int i = 4; i < split.length; i++) {
                String tagKv = split[i];
                String[] tagKV = tagKv.split("=");
                try {
                    map.put(tagKV[0], tagKV[1]);
                } catch (ArrayIndexOutOfBoundsException e) {
                    return;
                }
            }

            MetricBuilder builder = MetricBuilder.getInstance();
            builder.addMetric(metricKey).setDataPoint(timeStamp, value).addTags(map);
            SimpleHttpResponse response = httpClient.doPost(opentsdbServerUrl + "/api/put/?details", builder.build());
            String host = map.get("host");
            String ip = map.get("ip");
            // 写入服务器到数据库，主要为了显示到服务器列表
            if (StringUtils.isNotEmpty(host)) {
                LOG.info("host name not null");
                if (sender.loadingCache.getIfPresent(host) == null) {
                    if (sender.serverService.findServer(host) == null) {
                        LOG.info("find new server!");
                        DataCollector dataCollector = sender.dataCollectorService.findDataCollector(dataCollectorServerName);
                        // 需要找到数据收集器的IP，要求部署的IP
                        if (dataCollector != null) {
                            // 新服务器，设置状态为没有监控
                            Server server = new Server();
                            server.setHostname(host);
                            server.setName(host);
                            server.setDataCollectorId(dataCollector.getId());
                            server.setStatus(ServerStatusEnum.UNMONTORING.ordinal());
                            server.setCreateAt(new Date());
                            server.setIp(map.get("ip"));
                            sender.serverMapper.insert(server);
                        }
                    } else {
                        // 老朋友了，pass
                    }
                    sender.loadingCache.getUnchecked(host);
                }
            }
            System.out.println(response.getStatusCode());
            System.out.println(response.getContent());
        }
    }
}
