package com.evoupsight.monitorpass;

import com.evoupsight.monitorpass.domain.Item;
import com.google.gson.Gson;
import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.fs.Path;
import org.apache.hadoop.hbase.*;
import org.apache.hadoop.hbase.client.*;
import org.apache.hadoop.hbase.util.Bytes;

import java.io.IOException;
import java.net.URL;
import java.util.HashMap;

/**
 * @author evoup
 */
public class QueryInfo {
    public Configuration getHbaseConf() {
        Configuration config = HBaseConfiguration.create();
        ClassLoader classLoader = this.getClass().getClassLoader();
        URL resource = classLoader.getResource("hbase-site.xml");
        if (resource != null) {
            String path = resource.getPath();
            config.addResource(new Path(path));
            return config;
        }
        return null;
    }

    public void getRow() throws IOException {
        Configuration config = getHbaseConf();
        HBaseAdmin ad = null;
        try {
            ad = new HBaseAdmin(config);
        } catch (IOException e) {
            e.printStackTrace();
        }
        getData("monitor_items", "23219", null, null, ad);
    }

    public void getTables() {
        Configuration config = getHbaseConf();
        // Instantiate HBaseAdmin class
        HBaseAdmin ad = null;
        try {
            ad = new HBaseAdmin(config);
        } catch (IOException e) {
            e.printStackTrace();
        }

        //  Give list of tables HBaseAdmin Object
        HTableDescriptor[] tDescriptor = new HTableDescriptor[0];
        try {
            tDescriptor = ad.listTables();
        } catch (IOException e) {
            e.printStackTrace();
        }

        for (int k = 0; k < tDescriptor.length; k++) {
            // Print all the table names.
            System.out.println(tDescriptor[k].getNameAsString());
        }

    }


    public String getScanData() throws IOException {
        Configuration config = getHbaseConf();
        HBaseAdmin ad = null;
        try {
            ad = new HBaseAdmin(config);
        } catch (IOException e) {
            e.printStackTrace();
        }
        return scanData(ad);
    }

    /**
     * 获取全部items
     */
    public String scanData(HBaseAdmin ad) throws IOException {
        Connection connection = ConnectionFactory.createConnection(ad.getConfiguration());
        Table table = connection.getTable(TableName.valueOf("monitor_items"));
        Scan scan = new Scan();
        //scan.setFilter(new PageFilter(10));
        HashMap<String, Item> confMap = new HashMap();
        try (ResultScanner rs = table.getScanner(scan)) {
            for (Result r = rs.next(); r != null; r = rs.next()) {
                byte[] row = r.getRow();
                Item item = new Item();
                byte[] value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("type"));
                if (value != null) {
                    item.setType(new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("data_type"));
                if (value != null) {
                    item.setDataType(new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("snmp_community"));
                if (value != null) {
                    item.setSnmpCommunity(new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("snmp_oid"));
                if (value != null) {
                    item.setSnmpOid(new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("host_id"));
                if (value != null) {
                    item.setHostId(new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("name"));
                if (value != null) {
                    item.setName(new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("key_"));
                if (value != null) {
                    item.setKey(new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("delay"));
                if (value != null) {
                    item.setDelay(new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("history"));
                if (value != null) {
                    item.setHistory(new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("status"));
                if (value != null) {
                    item.setStatus(new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("value_type"));
                if (value != null) {
                    item.setValueType(new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("units"));
                if (value != null) {
                    item.setUnits(new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("multiplier"));
                if (value != null) {
                    item.setMultiplier(new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("delta"));
                if (value != null) {
                    item.setDelta(new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("lastlogsize"));
                if (value != null) {
                    item.setLastlogsize(new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("data_type"));
                if (value != null) {
                    item.setDataType(new String(value));
                }
                if (row != null) {
                    confMap.put(new String(row), item);
                }
            }
        }
        String s = new Gson().toJson(confMap);
        System.out.println(s);
        return s;
    }

    /**
     * 根据RowKey获取数据
     *
     * @param tableName 表名称
     * @param rowKey    RowKey名称
     * @param colFamily 列族名称
     * @param col       列名称
     * @throws IOException
     */
    public void getData(String tableName, String rowKey, String colFamily, String col, HBaseAdmin ad) throws IOException {
        Connection connection = ConnectionFactory.createConnection(ad.getConfiguration());
        Table table = connection.getTable(TableName.valueOf(tableName));
        Get get = new Get(Bytes.toBytes(rowKey));
        if (colFamily != null) {
            get.addFamily(Bytes.toBytes(colFamily));
        }
        if (colFamily != null && col != null) {
            get.addColumn(Bytes.toBytes(colFamily), Bytes.toBytes(col));
        }
        Result result = table.get(get);
        showCell(result);
        table.close();
        ad.close();
        connection.close();
    }

    /**
     * 根据RowKey获取信息
     *
     * @param tableName
     * @param rowKey
     * @throws IOException
     */
    public void getData(String tableName, String rowKey, HBaseAdmin ad) throws IOException {
        getData(tableName, rowKey, null, null, ad);
    }

    /**
     * 格式化输出
     *
     * @param result
     */
    public static void showCell(Result result) {
        Cell[] cells = result.rawCells();
        for (Cell cell : cells) {
            System.out.println("RowName: " + new String(CellUtil.cloneRow(cell)) + " ");
            System.out.println("Timetamp: " + cell.getTimestamp() + " ");
            System.out.println("column Family: " + new String(CellUtil.cloneFamily(cell)) + " ");
            System.out.println("row Name: " + new String(CellUtil.cloneQualifier(cell)) + " ");
            System.out.println("value: " + new String(CellUtil.cloneValue(cell)) + " ");
        }
    }
}

