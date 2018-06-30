package com.evoupsight.monitorpass.services;

import com.evoupsight.monitorpass.domain.Function;
import com.evoupsight.monitorpass.domain.Item;
import com.evoupsight.monitorpass.domain.Trigger;
import com.evoupsight.monitorpass.dto.HostTemplateDto;
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
import org.springframework.stereotype.Service;
import redis.clients.jedis.Jedis;
import redis.clients.jedis.JedisPool;

import java.io.IOException;
import java.util.Arrays;
import java.util.HashMap;
import java.util.HashSet;

import static com.evoupsight.monitorpass.utils.Utils.getColumnsInColumnFamily;

/**
 * @author evoup
 */
@Service
public class QueryInfoService {
    private static final Logger LOG = LoggerFactory.getLogger(QueryInfoService.class);
    private final Configuration hbaseConf;
    private final JedisPool jedisPool;

    @Autowired
    public QueryInfoService(Configuration hbaseConf, JedisPool jedisPool) {
        this.hbaseConf = hbaseConf;
        this.jedisPool = jedisPool;
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


    /**
     * 扫描需要缓存的表数据
     */
    void getScanData() {
        try (HBaseAdmin ad = new HBaseAdmin(hbaseConf)) {
            scanData(ad);
        } catch (IOException e) {
            LOG.error(e.getMessage(), e);
        }
    }

    /**
     * 返回key为hostName，value为template id数组的map
     *
     * @param ad
     */
    private HostTemplateDto scanHosts(HBaseAdmin ad) {
        HostTemplateDto hostTemplateDto = new HostTemplateDto();
        try (Connection connection = ConnectionFactory.createConnection(ad.getConfiguration())) {
            Table table = connection.getTable(TableName.valueOf("monitor_hosts"));
            Scan scan = new Scan();
            try (ResultScanner rs = table.getScanner(scan)) {
                for (Result r = rs.next(); r != null; r = rs.next()) {
                    byte[] row = r.getRow();
                    String hostName = new String(row);
                    // 数据收集器不为空，说明是实际的服务器
                    if (r.getValue(Bytes.toBytes("info"), Bytes.toBytes("data_collector")) != null) {
                        byte[] templateBytes = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("template"));
                        if (templateBytes != null) {
                            String templateStr = new String(templateBytes);
                            if (StringUtils.isNotEmpty(templateStr)) {
                                String[] templateArr = templateStr.split("\\|");
                                hostTemplateDto.setHost(hostName);
                                hostTemplateDto.setTemplateIds(Arrays.asList(templateArr));
                            }
                        }
                    }

                }
            }
        } catch (Exception e) {
            LOG.error(e.getMessage(), e);
        }
        return hostTemplateDto;
    }

    /**
     * 返回key为templateId，value为若干setid
     *
     * @param ad
     */
    private HashMap<String, HashSet<String>> scanTemplateSets(HBaseAdmin ad) {
        HashMap<String, HashSet<String>> templateSetsMap = new HashMap<>();
        try (Connection connection = ConnectionFactory.createConnection(ad.getConfiguration())) {
            Table table = connection.getTable(TableName.valueOf("monitor_sets"));
            Scan scan = new Scan();
            templateSetsMap = makeMap(table, templateSetsMap, scan);
        } catch (Exception e) {
            LOG.error(e.getMessage(), e);
        }
        return templateSetsMap;
    }

    /**
     * 返回key为templateId,value为map，map的key是setId，value是setName
     */
    private HashMap<String, HashMap<String, String>> scanTemplateSetsDetails(HBaseAdmin ad) {
        HashMap<String, HashMap<String, String>> map = new HashMap<>();
        try (Connection connection = ConnectionFactory.createConnection(ad.getConfiguration())) {
            Table table = connection.getTable(TableName.valueOf("monitor_sets"));
            Scan scan = new Scan();
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
                                    HashMap<String, String> sets = map.get(templateId);
                                    byte[] value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes(col));
                                    if (value != null) {
                                        sets.put(setid, new String(value));
                                    }
                                    map.put(templateId, sets);
                                } else {
                                    HashMap<String, String> sets = new HashMap<>();
                                    byte[] value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes(col));
                                    if (value != null) {
                                        sets.put(setid, new String(value));
                                    }
                                    map.put(templateId, sets);
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception e) {
            LOG.error(e.getMessage(), e);
        }
        return map;
    }


    /**
     * 返回key为itemId，value为若干setid
     *
     * @param ad
     */
    private HashMap<String, HashSet<String>> scanItemSets(HBaseAdmin ad) {
        HashMap<String, HashSet<String>> setItemsMap = new HashMap<>();
        try (Connection connection = ConnectionFactory.createConnection(ad.getConfiguration())) {
            Table table = connection.getTable(TableName.valueOf("monitor_items"));
            Scan scan = new Scan();
            setItemsMap = makeMap(table, setItemsMap, scan);
        } catch (Exception e) {
            LOG.error(e.getMessage(), e);
        }
        return setItemsMap;
    }

    /**
     * 返回itemId,value为对应Item的map
     */
    private HashMap<String, Item> scanItems(HBaseAdmin ad) {
        HashMap<String, Item> itemMap = new HashMap<>();
        try (Connection connection = ConnectionFactory.createConnection(ad.getConfiguration())) {
            Table table = connection.getTable(TableName.valueOf("monitor_items"));
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
        } catch (Exception e) {
           LOG.error(e.getMessage(), e);
        }
        return itemMap;
    }

    private HashMap<String, Trigger> scanTriggers(HBaseAdmin ad) {
        HashMap<String, Trigger> triggerMap = new HashMap<>();
        try (Connection connection = ConnectionFactory.createConnection(ad.getConfiguration())) {
            Table table = connection.getTable(TableName.valueOf("monitor_triggers"));
            Scan scan = new Scan();
            try (ResultScanner rs = table.getScanner(scan)) {
                for (Result r = rs.next(); r != null; r = rs.next()) {
                    byte[] row = r.getRow();
                    Trigger trigger = new Trigger();
                    byte[] value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("triggerid"));
                    if (value != null) {
                        trigger.setTriggerid(new String(value));
                    }
                    value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("expression"));
                    if (value != null) {
                        trigger.setExpression(new String(value));
                    }
                    value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("description"));
                    if (value != null) {
                        trigger.setDescription(new String(value));
                    }
                    value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("url"));
                    if (value != null) {
                        trigger.setUrl(new String(value));
                    }
                    value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("status"));
                    if (value != null) {
                        trigger.setStatus(new String(value));
                    }
                    value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("value"));
                    if (value != null) {
                        trigger.setValue(new String(value));
                    }
                    value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("priority"));
                    if (value != null) {
                        trigger.setPriority(new String(value));
                    }
                    value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("lastchange"));
                    if (value != null) {
                        trigger.setLastchange(new String(value));
                    }
                    value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("comments"));
                    if (value != null) {
                        trigger.setComments(new String(value));
                    }
                    value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("error"));
                    if (value != null) {
                        trigger.setError(new String(value));
                    }
                    value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("templateid"));
                    if (value != null) {
                        trigger.setTemplateid(new String(value));
                    }
                    value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("type"));
                    if (value != null) {
                        trigger.setType(new String(value));
                    }
                    value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("state"));
                    if (value != null) {
                        trigger.setState(new String(value));
                    }
                    value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("flags"));
                    if (value != null) {
                        trigger.setFlags(new String(value));
                    }
                    value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("hostid"));
                    if (value != null) {
                        trigger.setHostid(new String(value));
                    }
                    if (row != null) {
                        triggerMap.put(new String(row), trigger);
                    }
                }
            }
        } catch (Exception e) {
            LOG.error(e.getMessage(), e);
        }
        return triggerMap;
    }

    private HashMap<String, Function> scanFunctions(HBaseAdmin ad) {
        HashMap<String, Function> functionMap = new HashMap<>();
        try (Connection connection = ConnectionFactory.createConnection(ad.getConfiguration())) {
            Table table = connection.getTable(TableName.valueOf("monitor_functions"));
            Scan scan = new Scan();
            try (ResultScanner rs = table.getScanner(scan)) {
                for (Result r = rs.next(); r != null; r = rs.next()) {
                    byte[] row = r.getRow();
                    Function function = new Function();
                    byte[] value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("functionid"));
                    if (value != null) {
                        function.setFunctionid(new String(value));
                    }
                    value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("function"));
                    if (value != null) {
                        function.setFunction(new String(value));
                    }
                    value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("itemid"));
                    if (value != null) {
                        function.setItemid(new String(value));
                    }
                    value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("key"));
                    if (value != null) {
                        function.setKey(new String(value));
                    }
                    value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("parameter"));
                    if (value != null) {
                        function.setParameter(new String(value));
                    }
                    value = r.getValue(Bytes.toBytes("info"), Bytes.toBytes("triggerid"));
                    if (value != null) {
                        function.setTriggerid(new String(value));
                    }
                    if (row != null) {
                        functionMap.put(new String(row), function);
                    }
                }
            }
        } catch (Exception e) {
            LOG.error(e.getMessage(), e);
        }
        return functionMap;
    }


    /**
     * 获取全部items，通过template查set，通过item查询属于set的，最后归入host
     */
    private void scanData(HBaseAdmin ad) {
        // 获取主机和模板
        HostTemplateDto hostTemplateDto = scanHosts(ad);
        String json1 = new Gson().toJson(hostTemplateDto);

        HashMap<String, HashSet<String>> templateSetsMap = scanTemplateSets(ad);
        String json2 = new Gson().toJson(templateSetsMap);

        HashMap<String, HashSet<String>> itemSetsMap = scanItemSets(ad);
        String json3 = new Gson().toJson(itemSetsMap);

        HashMap<String, Item> itemsMap = scanItems(ad);
        String json4 = new Gson().toJson(itemsMap);

        HashMap<String, HashMap<String, String>> setDetailsMap = scanTemplateSetsDetails(ad);
        String json5 = new Gson().toJson(setDetailsMap);

        HashMap<String, Trigger> triggerMap = scanTriggers(ad);
        String json6 = new Gson().toJson(triggerMap);

        HashMap<String, Function> functionMap = scanFunctions(ad);
        String json7 = new Gson().toJson(functionMap);




        // 缓存成key
        try (Jedis jedis = jedisPool.getResource()) {
            // do simple operation to verify that the Jedis resource is working
            jedis.set("key1", json1);
            jedis.set("key2", json2);
            jedis.set("key3", json3);
            jedis.set("key4", json4);
            jedis.set("key5", json5);
            jedis.set("key6", json6);
            jedis.set("key7", json7);
        }

    }

    /**
     * 根据RowKey获取数据
     *
     * @param tableName 表名称
     * @param rowKey    RowKey名称
     * @param colFamily 列族名称
     * @param col       列名称
     * @throws IOException 异常
     */
    private void getData(String tableName, String rowKey, String colFamily, String col, HBaseAdmin ad) throws IOException {
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
    private static void showCell(Result result) {
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

