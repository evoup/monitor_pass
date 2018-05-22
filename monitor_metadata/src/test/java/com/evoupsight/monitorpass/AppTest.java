package com.evoupsight.monitorpass;

import org.junit.Assert;
import org.junit.Test;
import redis.clients.jedis.Jedis;
import redis.clients.jedis.JedisPool;
import redis.clients.jedis.JedisPoolConfig;

import java.io.IOException;

import static com.evoupsight.monitorpass.utils.Utils.buildPoolConfig;

/**
 * Unit test for simple MonitorMetaData.
 */
public class AppTest {
    /**
     * Rigorous Test :-)
     */
    @Test
    public void shouldAnswerWithTrue() throws IOException {
        //assertTrue( true);
        new QueryInfo().getRow();
    }

    @Test
    public void redisOk() {
        JedisPoolConfig poolConfig = buildPoolConfig();

        try (JedisPool jedisPool = new JedisPool(poolConfig, "datacollector"); Jedis jedis = jedisPool.getResource()) {

            // do simple operation to verify that the Jedis resource is working
            // properly
            String key = "key";
            String value = "value";

            jedis.set(key, value);
            String value2 = jedis.get(key);
            System.out.println(value2);

            Assert.assertEquals(value, value2);

            // flush Redis
            jedis.flushAll();
        }
    }


}
