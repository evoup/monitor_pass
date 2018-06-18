package com.evoupsight.monitorpass.datacollector.utils;

import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.hbase.TableName;
import org.apache.hadoop.hbase.client.*;
import org.apache.hadoop.hbase.util.Bytes;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Component;

import java.io.IOException;

import static com.evoupsight.monitorpass.constants.Constants.*;

/**
 * @author evoup
 */
@Component
public class HbaseUtils {
    private static final Logger LOG = LoggerFactory.getLogger(HbaseUtils.class);
    @Autowired
    Configuration config;

    private static HbaseUtils instance;

    public static synchronized HbaseUtils getInstance() {
        if (instance == null) {
            instance = new HbaseUtils();
        }
        return instance;
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
            p.addColumn(Bytes.toBytes("info"), Bytes.toBytes("lastin"), Bytes.toBytes(System.currentTimeMillis()));
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
