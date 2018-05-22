package com.evoupsight.monitorpass;

import redis.clients.jedis.Jedis;
import redis.clients.jedis.JedisPool;
import redis.clients.jedis.JedisPoolConfig;

import java.io.IOException;

import static com.evoupsight.monitorpass.utils.Utils.buildPoolConfig;

/**
 * Hello world!
 */
public class MonitorMetaData {
    public static void main(String[] args) throws IOException {
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
