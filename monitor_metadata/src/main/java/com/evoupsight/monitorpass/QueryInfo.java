package com.evoupsight.monitorpass;

import org.apache.commons.lang.StringUtils;
import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.fs.Path;
import org.apache.hadoop.hbase.*;
import org.apache.hadoop.hbase.client.*;
import org.apache.hadoop.hbase.filter.PageFilter;
import org.apache.hadoop.hbase.util.Bytes;

import java.io.IOException;
import java.net.URL;
import java.util.ArrayList;
import java.util.List;

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
//            byte[] row1 = Bytes.toBytes("23219");
//            TableName table1 = TableName.valueOf("monitor_items");
//            String family1 = "info";
//            Get g = new Get(row1);
//            System.out.println(table1.getName());

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

            for (int k=0; k<tDescriptor.length;k++ ) {

                // Print all the table names.
                System.out.println(tDescriptor[k].getNameAsString());
            }

    }


    public void getScanData() throws IOException {
        Configuration config = getHbaseConf();
        HBaseAdmin ad = null;
        try {
            ad = new HBaseAdmin(config);
        } catch (IOException e) {
            e.printStackTrace();
        }
        scanData(ad);
    }

    /**
     * 获取全部items
     */
    public void scanData(HBaseAdmin ad) throws IOException {
        Connection connection = ConnectionFactory.createConnection(ad.getConfiguration());
        //List<RowValue> result = new ArrayList<>();
        //try (Connection connection = hBaseConnectionFactory.connect()) {
        Table table = connection.getTable(TableName.valueOf("monitor_items"));
        Scan scan = new Scan();
        //scan.setFilter(new PageFilter(10));
        try (ResultScanner rs = table.getScanner(scan)) {
            for (Result r = rs.next(); r != null; r = rs.next()) {
                //conversionsService.constructRowValue is a helper method (defined in the app)
                //result.add(conversionsService.constructRowValue(r));
                //family
                //qualifier
                //System.out.println(r.getColumnCells("info".getBytes(), "desc".getBytes()));
                //System.out.println(r.getColumnCells("info".getBytes(), "description".getBytes()));
                //System.out.println(r.getColumnCells("info".getBytes(), "data_type".getBytes()));
                //System.out.println(r.getColumnCells("info".getBytes(), "delay".getBytes()));
                //List<Cell> columnCells = r.getColumnCells("info".getBytes(), "desc".getBytes());

                byte[] value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("type"));
                if (value != null) {
                    System.out.println("[info:type]:" + new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("data_type"));
                if (value != null) {
                    System.out.println("[info:data_type]:" + new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("snmp_community"));
                if (value != null) {
                    System.out.println("[info:snmp_community]:" + new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("snmp_oid"));
                if (value != null) {
                    System.out.println("[info:snmp_oid]:" + new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("host_id"));
                if (value != null) {
                    System.out.println("[info:host_id]:" + new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("name"));
                if (value != null) {
                    System.out.println("[info:name]:" + new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("key_"));
                if (value != null) {
                    System.out.println("[info:key_]:" + new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("desc"));
                if (value != null) {
                    System.out.println("[info:desc]:" + new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("delay"));
                if (value != null) {
                    System.out.println("[info:delay]:" + new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("history"));
                if (value != null) {
                    System.out.println("[info:history]:" + new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("status"));
                if (value != null) {
                    System.out.println("[info:status]:" + new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("value_type"));
                if (value != null) {
                    System.out.println("[info:value_type]:" + new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("units"));
                if (value != null) {
                    System.out.println("[info:units]:" + new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("multiplier"));
                if (value != null) {
                    System.out.println("[info:multiplier]:" + new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("delta"));
                if (value != null) {
                    System.out.println("[info:delta]:" + new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("delta"));
                if (value != null) {
                    System.out.println("[info:delta]:" + new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("lastlogsize"));
                if (value != null) {
                    System.out.println("[info:lastlogsize]:" + new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("data_type"));
                if (value != null) {
                    System.out.println("[info:data_type]:" + new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("description"));
                if (value != null) {
                    System.out.println("[info:description]:" + new String(value));
                }
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("description"));
                if (value != null) {
                    System.out.println("[info:description]:" + new String(value));
                }
            }
        }
    }

    /**
     * 根据RowKey获取数据
     *
     * @param tableName 表名称
     * @param rowKey RowKey名称
     * @param colFamily 列族名称
     * @param col 列名称
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

