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
