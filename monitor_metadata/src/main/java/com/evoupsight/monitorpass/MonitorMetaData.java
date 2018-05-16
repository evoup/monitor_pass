package com.evoupsight.monitorpass;

import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.fs.Path;
import org.apache.hadoop.hbase.HBaseConfiguration;
import org.apache.hadoop.hbase.TableName;
import org.apache.hadoop.hbase.client.Get;
import org.apache.hadoop.hbase.util.Bytes;
import redis.clients.jedis.Jedis;
import redis.clients.jedis.JedisPool;
import redis.clients.jedis.JedisPoolConfig;

import java.io.IOException;
import java.net.URL;

import static com.evoupsight.monitorpass.utils.Utils.buildPoolConfig;

/**
 * Hello world!
 *
 */
public class MonitorMetaData
{
    public static void main( String[] args ) throws IOException {
        QueryInfo queryInfo = new QueryInfo();
        //queryInfo.getRow();
        String scanData = queryInfo.getScanData();
        JedisPoolConfig poolConfig = buildPoolConfig();

        try (JedisPool jedisPool = new JedisPool(poolConfig, "datacollector"); Jedis jedis = jedisPool.getResource()) {

            // do simple operation to verify that the Jedis resource is working
            // properly
            String key = "key";
//            String value = "value";

            jedis.set(key, scanData);
            String value2 = jedis.get(key);
            System.out.println(value2);
            // flush Redis
            //jedis.flushAll();
        }
    }


}
