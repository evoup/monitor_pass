package com.evoupsight.monitorpass.server.services;

import com.evoupsight.monitorpass.server.dto.HostTemplateDto;
import com.google.gson.Gson;
import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.hbase.TableName;
import org.apache.hadoop.hbase.client.*;
import org.apache.hadoop.hbase.util.Bytes;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import redis.clients.jedis.Jedis;
import redis.clients.jedis.JedisPool;

import java.io.IOException;

import static com.evoupsight.monitorpass.server.constants.Constants.KEY_SCAN_DURATION;
import static com.evoupsight.monitorpass.server.constants.Constants.MDB_TAB_ENGINE;


/**
 * @author evoup
 */
@Service
public class ScanService {

    private static final Logger LOG = LoggerFactory.getLogger(ScanService.class);

    private final Configuration hbaseConf;

    private final JedisPool jedisPool;

    @Autowired
    private ScanService(JedisPool jedisPool, Configuration hbaseConf) {
        this.jedisPool = jedisPool;
        this.hbaseConf = hbaseConf;
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
        try (Jedis resource = jedisPool.getResource()) {
            String value1 = resource.get("key1");
            LOG.info("value1:" + value1);
            HostTemplateDto hostTemplateDto = new Gson().fromJson(value1, HostTemplateDto.class);
            LOG.info(new Gson().toJson(hostTemplateDto));
        }
    }
}
