package com.evoupsight.monitorpass.utils;

import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.hbase.TableName;
import org.apache.hadoop.hbase.client.*;
import org.apache.hadoop.hbase.util.Bytes;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.io.IOException;

import static com.evoupsight.monitorpass.constants.Constants.KEY_SCAN_DURATION;
import static com.evoupsight.monitorpass.constants.Constants.MDB_COL_SCAN_DURATION;
import static com.evoupsight.monitorpass.constants.Constants.MDB_TAB_ENGINE;

public class Scan {
        private static final Logger LOG = LoggerFactory.getLogger(Scan.class);

    public void saveLastScanTime(Configuration config) throws IOException {
        HBaseAdmin ad = null;
        Table table = null;
        Connection connection = null;
        String colFamily = MDB_COL_SCAN_DURATION;
        try {
            ad = new HBaseAdmin(config);
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
