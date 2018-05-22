package com.evoupsight.monitorpass;

import com.evoupsight.monitorpass.domain.Item;
import com.evoupsight.monitorpass.utils.Utils;
import com.google.gson.Gson;
import org.apache.commons.lang.StringUtils;
import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.fs.Path;
import org.apache.hadoop.hbase.*;
import org.apache.hadoop.hbase.client.*;
import org.apache.hadoop.hbase.util.Bytes;

import java.io.IOException;
import java.net.URL;
import java.util.*;

import static com.evoupsight.monitorpass.utils.Utils.getColumnsInColumnFamily;

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
        scanTemplateSets(ad);
        //return scanData(ad);
        return null;
    }

    /**
     * 返回key为hostName，value为template id数组的map
     * @param ad
     */
    private HashMap<String, String[]> scanHosts(HBaseAdmin ad) throws IOException {
        Connection connection = ConnectionFactory.createConnection(ad.getConfiguration());
        Table table = connection.getTable(TableName.valueOf("monitor_hosts"));
        Scan scan = new Scan();
        HashMap<String, String[]> hostTemplateMap = new HashMap<>();
        try (ResultScanner rs = table.getScanner(scan)) {
            for (Result r = rs.next(); r != null; r = rs.next()) {
                byte[] row = r.getRow();
                String hostName = new String(row);
                byte[] value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("type"));
                // 数据收集器不为空，说明是实际的服务器
                if (r.getValue(Bytes.toBytes("info"), Bytes.toBytes("data_collector")) != null) {
                    byte[] templateBytes = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("template"));
                    if (templateBytes != null) {
                        String templateStr = new String(templateBytes);
                        if (StringUtils.isNotEmpty(templateStr)) {
                            String[] templateArr = templateStr.split("\\|");
                            hostTemplateMap.put(hostName, templateArr);
                        }
                    }
                }

            }
        }
        connection.close();
        return hostTemplateMap;
    }

    /**
     * 返回key为templateId，value为若干setid
     * @param ad
     */
    private void scanTemplateSets(HBaseAdmin ad) throws IOException {
        Connection connection = ConnectionFactory.createConnection(ad.getConfiguration());
        Table table = connection.getTable(TableName.valueOf("monitor_sets"));
        HashMap<String, HashSet<String>> templateSetsMap = new HashMap<>();
        Scan scan = new Scan();
        try (ResultScanner rs = table.getScanner(scan)) {
            for (Result r = rs.next(); r != null; r = rs.next()) {
                byte[] row = r.getRow();
                String templateId = new String(row);
                String[] cols = getColumnsInColumnFamily(r, "info");
                // System.out.println("cols:" + new Gson().toJson(cols));
                if (cols != null) {
                    for (String col : cols) {
                        // 查出列，找是否有类似info:setid286的列，这就是setid
                        if (col.contains("setid")) {
                            String setid = col.substring(5);
                            // System.out.println("setid:" + setid);
                            if (templateSetsMap.containsKey(templateId)) {
                                HashSet<String> sets = templateSetsMap.get(templateId);
                                sets.add(setid);
                                templateSetsMap.put(templateId, sets);
                            } else {
                                HashSet<String> sets = new HashSet<>();
                                sets.add(setid);
                                templateSetsMap.put(templateId, sets);
                            }
                        }
                    }
                }
            }
        }
        connection.close();
        System.out.println("templateSetsMap:" + new Gson().toJson(templateSetsMap));
    }

    /**
     * 获取全部items，通过template查set，通过item查询属于set的，最后归入host
     */
    public String scanData(HBaseAdmin ad) throws IOException {
        // 获取主机和模板
        HashMap<String, String[]> hostTemplateMap = scanHosts(ad);
        System.out.println("========hostTemplateMap===========");
        System.out.println(new Gson().toJson(hostTemplateMap));
        System.out.println("==================================");

        Connection connection = ConnectionFactory.createConnection(ad.getConfiguration());
        Table table = connection.getTable(TableName.valueOf("monitor_items"));
        Scan scan = new Scan();

        // 中间数组，key为templateid，value为若干itemid
        HashMap<String, HashSet<String>> templateItemMap = new HashMap<>();

        HashMap<String, Item> itemMap = new HashMap<>();
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
                value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("templateid"));
                if (value != null) {
                    System.out.println("find templateid");
                    String templateId = new String(value);
                    // 已经有模板id的，追加监控项，否则直接加入
                    if (templateItemMap.containsKey(templateId)) {
                        HashSet<String> items = templateItemMap.get(templateId);
                        items.add(new String(row));
                        templateItemMap.put(templateId, items);
                    } else {
                        HashSet<String> items = new HashSet<>();
                        items.add(new String(row));
                        templateItemMap.put(templateId, items);
                    }
                } else {
                    System.out.println("could not find templateid");
                }
                if (row != null) {
                    itemMap.put(new String(row), item);
                }
            }
        }
        // 最终数组，key为主机，value为若干items
        HashMap<String, ArrayList<Item>> lastMap = new HashMap<>();
        Set<Map.Entry<String, String[]>> entries = hostTemplateMap.entrySet();
        for (Map.Entry<String, String[]> hostTemplateEntry : entries) {
            String keyHostName = hostTemplateEntry.getKey();
            System.out.println("keyHostName:" + keyHostName);
            String[] templateIds = hostTemplateEntry.getValue();
            System.out.println("templateIds:" + new Gson().toJson(templateIds));
            Set<Map.Entry<String, HashSet<String>>> entries1 = templateItemMap.entrySet();
            for (Map.Entry<String, HashSet<String>> templateItemsEntry : entries1) {
                String templateId = templateItemsEntry.getKey();
                System.out.println("templateId:" + templateId);
                if (Arrays.asList(templateIds).contains(templateId)) {
                    System.out.println("templateId hit:" + templateId);
                    // 属于该host的template下的item
                    HashSet<String> itemIds = templateItemsEntry.getValue();
                    System.out.println("itemIds:" + new Gson().toJson(itemIds));
                    ArrayList<Item> myItems = new ArrayList<>();
                    if (itemIds != null) {
                        for (String itemId : itemIds) {
                            myItems.add(itemMap.get(itemId));
                        }
                    }
                    // 已有主机的，追加实际item，否则直接加入
                    if (lastMap.containsKey(keyHostName)) {
                        ArrayList<Item> items1 = lastMap.get(keyHostName);
                        items1.addAll(myItems);
                        lastMap.put(keyHostName, items1);
                    } else {
                        lastMap.put(keyHostName, myItems);
                    }
                }
            }
        }

//        System.out.println("----------itemMap----------");
//        System.out.println(new Gson().toJson(itemMap));
//        System.out.println("---------------------------");
        System.out.println("----------templateItemMap----------");
        System.out.println(new Gson().toJson(templateItemMap));
        System.out.println("-----------------------------------");

        //String s = new Gson().toJson(confMap);
        String s = new Gson().toJson(lastMap);
        System.out.println(s);
        connection.close();
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

