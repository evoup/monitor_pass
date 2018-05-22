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
        queryInfo.getScanData();
    }


}
