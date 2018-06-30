package com.evoupsight.monitorpass.server.utils;

import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.hbase.TableName;
import org.apache.hadoop.hbase.client.*;
import org.apache.hadoop.hbase.util.Bytes;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.io.IOException;

import static com.evoupsight.monitorpass.constants.Constants.KEY_SCAN_DURATION;
import static com.evoupsight.monitorpass.constants.Constants.MDB_TAB_ENGINE;

/**
 * @author evoup
 */
public class Scan {

    private static final Logger LOG = LoggerFactory.getLogger(Scan.class);

    private static Scan instance;
    private HBaseAdmin hBaseAdmin;
    private Configuration hbaseConf;

    private Scan() {
    }

    /**
     * 单例
     * @param hbaseConf hbase配置
     * @return Scan
     * @throws IOException 异常
     */
    public synchronized static Scan getInstance(Configuration hbaseConf) throws IOException {
        if (instance == null) {
            instance = new Scan();
            instance.hbaseConf = hbaseConf;
            instance.hBaseAdmin = new HBaseAdmin(instance.hbaseConf);
        }
        return instance;
    }

    /**
     * 保存上次扫描时间
     * @throws IOException 异常
     */
    public void saveLastScanTime() throws IOException {
        Table table = null;
        Connection connection = null;
        HBaseAdmin ad = null;
        try {
            ad = hBaseAdmin;
            connection = ConnectionFactory.createConnection(ad.getConfiguration());
            table = connection.getTable(TableName.valueOf(MDB_TAB_ENGINE));
            Put p = new Put(Bytes.toBytes(KEY_SCAN_DURATION));
            p.addColumn(Bytes.toBytes("scan"), Bytes.toBytes("duration"), Bytes.toBytes(System.currentTimeMillis()));
            table.put(p);
        } catch (IOException e) {
            LOG.error(e.getMessage(), e);
        } finally {
            if (table != null) {
                table.close();
            }
            if (ad != null) {
                ad.close();
            }
            if (connection != null) {
                connection.close();
            }
        }
    }
}
