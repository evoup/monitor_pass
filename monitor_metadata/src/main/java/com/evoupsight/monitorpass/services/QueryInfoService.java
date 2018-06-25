package com.evoupsight.monitorpass.services;

import com.evoupsight.monitorpass.domain.Item;
import com.google.gson.Gson;
import org.apache.commons.lang.StringUtils;
import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.hbase.Cell;
import org.apache.hadoop.hbase.CellUtil;
import org.apache.hadoop.hbase.HTableDescriptor;
import org.apache.hadoop.hbase.TableName;
import org.apache.hadoop.hbase.client.*;
import org.apache.hadoop.hbase.util.Bytes;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.stereotype.Service;
import redis.clients.jedis.Jedis;
import redis.clients.jedis.JedisPool;
import redis.clients.jedis.JedisPoolConfig;

import java.io.IOException;
import java.util.HashMap;
import java.util.HashSet;

import static com.evoupsight.monitorpass.utils.Utils.buildPoolConfig;
import static com.evoupsight.monitorpass.utils.Utils.getColumnsInColumnFamily;

/**
 * @author evoup
 */
@Service
public class QueryInfoService {
    private static final Logger LOG = LoggerFactory.getLogger(QueryInfoService.class);
    private final Configuration hbaseConf;

    @Value("${redis.host}")
    String redisHost;

    @Autowired
    public QueryInfoService(Configuration hbaseConf) {
        this.hbaseConf = hbaseConf;
    }

    public void getRow() throws IOException {
        HBaseAdmin ad = null;
        try {
            ad = new HBaseAdmin(hbaseConf);
        } catch (IOException e) {
            e.printStackTrace();
        }
        getData("monitor_items", "23219", null, null, ad);
    }

    public void getTables() {
        // Instantiate HBaseAdmin class
        HBaseAdmin ad = null;
        try {
            ad = new HBaseAdmin(hbaseConf);
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


    public void getScanData() throws IOException {
        HBaseAdmin ad = null;
        try {
            ad = new HBaseAdmin(hbaseConf);
        } catch (IOException e) {
            e.printStackTrace();
        }
        scanData(ad);
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
                LOG.info("value:" + value);
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
    private HashMap<String, HashSet<String>> scanTemplateSets(HBaseAdmin ad) throws IOException {
        Connection connection = ConnectionFactory.createConnection(ad.getConfiguration());
        Table table = connection.getTable(TableName.valueOf("monitor_sets"));
        HashMap<String, HashSet<String>> templateSetsMap = new HashMap<>();
        Scan scan = new Scan();
        templateSetsMap = makeMap(table, templateSetsMap, scan);
        connection.close();
        return templateSetsMap;
    }



    /**
     * 返回key为itemId，value为若干setid
     * @param ad
     */
    private  HashMap<String, HashSet<String>> scanItemSets(HBaseAdmin ad) throws IOException {
        Connection connection = ConnectionFactory.createConnection(ad.getConfiguration());
        Table table = connection.getTable(TableName.valueOf("monitor_items"));
        HashMap<String, HashSet<String>> setItemsMap = new HashMap<>();
        Scan scan = new Scan();
        setItemsMap = makeMap(table, setItemsMap, scan);
        connection.close();
        return setItemsMap;
    }

    /**
     * 返回itemId,value为对应Item的map
     */
    private HashMap<String, Item> scanItems(HBaseAdmin ad) throws IOException {
        Connection connection = ConnectionFactory.createConnection(ad.getConfiguration());
        Table table = connection.getTable(TableName.valueOf("monitor_items"));
        HashMap<String, Item> itemMap = new HashMap<>();
        Scan scan = new Scan();
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
                    itemMap.put(new String(row), item);
                }
            }
        }
        connection.close();
        return itemMap;
    }


    /**
     * 获取全部items，通过template查set，通过item查询属于set的，最后归入host
     */
    private void scanData(HBaseAdmin ad) throws IOException {
        // 获取主机和模板
        HashMap<String, String[]> hostTemplateMap = scanHosts(ad);
        System.out.println("========hostTemplateMap===========");
        String json1 = new Gson().toJson(hostTemplateMap);
        LOG.info("json1:" + json1);
        System.out.println("==================================");

        HashMap<String, HashSet<String>> templateSetsMap = scanTemplateSets(ad);
        System.out.println("=========templateSetsMap==========");
        String json2 = new Gson().toJson(templateSetsMap);
        //LOG.info("json2:" + json2);
        System.out.println("==================================");

        HashMap<String, HashSet<String>> itemSetsMap = scanItemSets(ad);
        System.out.println("===========itemSetsMap============");
        String json3 = new Gson().toJson(itemSetsMap);
        //LOG.info("json3:" + json3);
        System.out.println("==================================");

        HashMap<String, Item> itemsMap = scanItems(ad);
        System.out.println("=============itemsMap=============");
        String json4 = new Gson().toJson(itemsMap);
        //LOG.info("json4:" + json4);
        System.out.println("==================================");

        JedisPoolConfig poolConfig = buildPoolConfig();

        // 缓存成4个key就足够了
        try (JedisPool jedisPool = new JedisPool(poolConfig, redisHost); Jedis jedis = jedisPool.getResource()) {
            // do simple operation to verify that the Jedis resource is working
            jedis.set("key1", json1);
            jedis.set("key2", json2);
            jedis.set("key3", json3);
            jedis.set("key4", json4);
            // flush Redis
            //jedis.flushAll();
        }

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

    private HashMap<String, HashSet<String>> makeMap(Table table, HashMap<String, HashSet<String>> map, Scan scan) throws IOException {
        try (ResultScanner rs = table.getScanner(scan)) {
            for (Result r = rs.next(); r != null; r = rs.next()) {
                byte[] row = r.getRow();
                String templateId = new String(row);
                String[] cols = getColumnsInColumnFamily(r, "info");
                if (cols != null) {
                    for (String col : cols) {
                        // 查出列，找是否有类似info:setid286的列，这就是setid
                        if (col.contains("setid")) {
                            String setid = col.substring(5);
                            if (map.containsKey(templateId)) {
                                HashSet<String> sets = map.get(templateId);
                                sets.add(setid);
                                map.put(templateId, sets);
                            } else {
                                HashSet<String> sets = new HashSet<>();
                                sets.add(setid);
                                map.put(templateId, sets);
                            }
                        }
                    }
                }
            }
        }
        return map;
    }
}

