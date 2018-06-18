package com.evoupsight.monitorpass.utils;

import org.apache.hadoop.hbase.client.Result;
import org.apache.hadoop.hbase.util.Bytes;
import redis.clients.jedis.JedisPoolConfig;

import java.time.Duration;
import java.util.NavigableMap;

/**
 * @author evoup
 */
public class Utils {

    public static JedisPoolConfig buildPoolConfig() {
        JedisPoolConfig poolConfig = new JedisPoolConfig();
        poolConfig.setMaxTotal(128);
        poolConfig.setMaxIdle(128);
        poolConfig.setMinIdle(16);
        poolConfig.setTestOnBorrow(true);
        poolConfig.setTestOnReturn(true);
        poolConfig.setTestWhileIdle(true);
        poolConfig.setMinEvictableIdleTimeMillis(Duration.ofSeconds(60).toMillis());
        poolConfig.setTimeBetweenEvictionRunsMillis(Duration.ofSeconds(30).toMillis());
        poolConfig.setNumTestsPerEvictionRun(3);
        poolConfig.setBlockWhenExhausted(true);
        return poolConfig;
    }


    public static String[] getColumnsInColumnFamily(Result r, String columnfamily)
    {

        NavigableMap<byte[], byte[]> familyMap = r.getFamilyMap(Bytes.toBytes(columnfamily));
        String[] quantifers = new String[familyMap.size()];

        int counter = 0;
        for(byte[] bQunitifer : familyMap.keySet())
        {
            quantifers[counter++] = Bytes.toString(bQunitifer);

        }

        return quantifers;
    }
}
