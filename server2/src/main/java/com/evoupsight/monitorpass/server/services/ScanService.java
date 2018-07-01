package com.evoupsight.monitorpass.server.services;

import com.evoupsight.monitorpass.server.dto.HostTemplateDto;
import com.evoupsight.monitorpass.server.dto.opentsdb.QueryDto;
import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;
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
import java.util.ArrayList;
import java.util.List;

import static com.evoupsight.monitorpass.server.constants.Constants.KEY_SCAN_DURATION;
import static com.evoupsight.monitorpass.server.constants.Constants.MDB_TAB_ENGINE;


/**
 * @author evoup
 */
@Service
public class ScanService {

    @Value("${opentsdb.serverurl}")
    private String opentsdbUrl;

    private static final Logger LOG = LoggerFactory.getLogger(ScanService.class);

    private final Configuration hbaseConf;

    private final JedisPool jedisPool;

    private final PoolingHttpClient httpClient;

    @Autowired
    private ScanService(JedisPool jedisPool, Configuration hbaseConf, PoolingHttpClient httpClient) {
        this.jedisPool = jedisPool;
        this.hbaseConf = hbaseConf;
        this.httpClient = httpClient;
    }

    /**
     * 执行所有工作
     */
    void doAllJobs() throws IOException {
        saveLastScanTime();
        scanHostDown();
    }

    /**
     * 保存上次扫描时间
     * @throws IOException 异常
     */
    private void saveLastScanTime() throws IOException {
        LOG.debug("save scan time");
        try (Connection connection = ConnectionFactory.createConnection(hbaseConf);
             Table table = connection.getTable(TableName.valueOf(MDB_TAB_ENGINE))) {
            Put p = new Put(Bytes.toBytes(KEY_SCAN_DURATION));
            p.addColumn(Bytes.toBytes("scan"), Bytes.toBytes("duration"), Bytes.toBytes(System.currentTimeMillis()));
            table.put(p);
        }
    }



    /**
     * 检查是否宕机
     */
    private void scanHostDown() {
        HttpResponse httpResponse = null;
        Gson gson = new Gson();
        try (Jedis resource = jedisPool.getResource()) {
            String value1 = resource.get("key1");
            List<HostTemplateDto> hostTemplateDtos = new Gson().fromJson(value1,
                    new TypeToken<ArrayList<HostTemplateDto>>(){}.getType());
            LOG.info(new Gson().toJson(hostTemplateDtos));
            if (hostTemplateDtos != null) {
                for (HostTemplateDto hostTemplateDto : hostTemplateDtos) {
                    String host = hostTemplateDto.getHost();
                    if (StringUtils.isNotEmpty(host)) {
                        String myhost = StringUtils.remove(host, "-");
                        HttpGet httpGet = new HttpGet(opentsdbUrl +
                                "/api/query?start=5m-ago&m=sum:rate:apps.backend." + myhost +
                                ".proc.loadavg.5min%7Bhost=" + host + "%7D");
                        httpResponse = httpClient.execute(httpGet);
                        if (httpResponse != null && httpResponse.getStatusLine().getStatusCode()==200) {
                            HttpEntity entity = httpResponse.getEntity();
                            //将entity当中的数据转换为字符串
                            String response = EntityUtils.toString(entity, "utf-8");
                            LOG.info("response:" + response);
                            QueryDto queryDto = gson.fromJson(response, QueryDto.class);
                            LOG.info("queryDto:" + gson.toJson(queryDto));
                        }
                    }
                }
            }
        } catch (IOException e) {
            LOG.error(e.getMessage(), e);
        } finally {
            releaseResponse(httpResponse);
        }
    }

    /**
     * 清理
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
