package com.evoupsight.monitorpass.utils;

import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.hbase.TableName;
import org.apache.hadoop.hbase.client.*;
import org.apache.hadoop.hbase.util.Bytes;

import java.io.IOException;

import static com.evoupsight.monitorpass.constants.Constants.__KEY_SCAN_DURATION;
import static com.evoupsight.monitorpass.constants.Constants.__MDB_COL_SCAN_DURATION;
import static com.evoupsight.monitorpass.constants.Constants.__MDB_TAB_ENGINE;

public class Scan {

    public void saveLastScanTime(Configuration config) {
        HBaseAdmin ad = null;
        String colFamily = __MDB_COL_SCAN_DURATION;
        try {
            ad = new HBaseAdmin(config);
            Connection connection = ConnectionFactory.createConnection(ad.getConfiguration());
            Table table = connection.getTable(TableName.valueOf(__MDB_TAB_ENGINE));
            Put p = new Put(Bytes.toBytes(__KEY_SCAN_DURATION));
            p.addColumn(Bytes.toBytes("scan"), Bytes.toBytes("duration"), Bytes.toBytes(System.currentTimeMillis()));
            table.put(p);
            table.close();
            ad.close();
            connection.close();
        } catch (IOException e) {
            e.printStackTrace();
        }
    }
}
