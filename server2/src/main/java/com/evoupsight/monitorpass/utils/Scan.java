package com.evoupsight.monitorpass.utils;

import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.hbase.TableName;
import org.apache.hadoop.hbase.client.*;
import org.apache.hadoop.hbase.util.Bytes;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.context.annotation.Scope;
import org.springframework.stereotype.Component;

import java.io.IOException;

import static com.evoupsight.monitorpass.constants.Constants.KEY_SCAN_DURATION;
import static com.evoupsight.monitorpass.constants.Constants.MDB_TAB_ENGINE;

@Component
@Scope(value = "singleton")
public class Scan {
    @Autowired
    private Configuration hbaseConf;

    private static final Logger LOG = LoggerFactory.getLogger(Scan.class);

    private static Scan instance;
    private HBaseAdmin hBaseAdmin;

    private Scan() {
    }

    public synchronized static Scan getInstance() throws IOException {
        if (instance == null) {
            instance = new Scan();
            instance.hBaseAdmin = new HBaseAdmin(instance.hbaseConf);
        }
        return instance;
    }

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
