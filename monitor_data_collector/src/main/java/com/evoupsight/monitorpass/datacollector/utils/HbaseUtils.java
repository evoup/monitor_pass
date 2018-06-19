package com.evoupsight.monitorpass.datacollector.utils;

import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.fs.Path;
import org.apache.hadoop.hbase.HBaseConfiguration;
import org.apache.hadoop.hbase.TableName;
import org.apache.hadoop.hbase.client.*;
import org.apache.hadoop.hbase.util.Bytes;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.io.IOException;
import java.net.URL;

import static com.evoupsight.monitorpass.constants.Constants.*;

/**
 * @author evoup
 */
public class HbaseUtils {
    private static final Logger LOG = LoggerFactory.getLogger(HbaseUtils.class);

    private static HbaseUtils instance;
    private static Configuration config;

    public static synchronized HbaseUtils getInstance() {
        if (instance == null) {
            instance = new HbaseUtils();
            config = instance.hbaseConf(instance);
        }
        return instance;
    }

    private Configuration hbaseConf(HbaseUtils hbaseUtils) {
        org.apache.hadoop.conf.Configuration config = HBaseConfiguration.create();
        ClassLoader classLoader = hbaseUtils.getClass().getClassLoader();
        URL resource = classLoader.getResource("hbase-site.xml");
        if (resource != null) {
            String path = resource.getPath();
            config.addResource(new Path(path));
            return config;
        }
        throw new RuntimeException("can not load hbase config");
    }

    public void saveLastScanTime(String host) throws IOException {
        HBaseAdmin ad = null;
        Table table = null;
        Connection connection = null;
        try {
            ad = new HBaseAdmin(config);
            connection = ConnectionFactory.createConnection(ad.getConfiguration());
            table = connection.getTable(TableName.valueOf(MDB_TAB_HOSTS));
            Put p = new Put(Bytes.toBytes(host));
            p.addColumn(Bytes.toBytes("info"), Bytes.toBytes("last_upload"), Bytes.toBytes(String.valueOf(System.currentTimeMillis())));
            table.put(p);
            table = connection.getTable(TableName.valueOf(MDB_TAB_HOST));
            p = new Put(Bytes.toBytes(host));
            p.addColumn(Bytes.toBytes("info"), Bytes.toBytes("last_upload"), Bytes.toBytes(String.valueOf(System.currentTimeMillis())));
            table.put(p);
            LOG.info("host:" + host + "added to hbase");
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
