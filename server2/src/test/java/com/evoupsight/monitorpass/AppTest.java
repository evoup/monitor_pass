package com.evoupsight.monitorpass;

import com.evoupsight.monitorpass.server.dto.memcache.HostTemplateDto;
import com.evoupsight.monitorpass.server.dto.memcache.TriggerDto;
import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;
import junit.framework.Test;
import junit.framework.TestCase;
import junit.framework.TestSuite;
import redis.clients.jedis.Jedis;
import redis.clients.jedis.JedisPool;
import redis.clients.jedis.JedisPoolConfig;

import java.time.Duration;
import java.util.ArrayList;
import java.util.HashMap;

/**
 * Unit test for simple App.
 */
public class AppTest 
    extends TestCase
{
    /**
     * Create the test case
     *
     * @param testName name of the test case
     */
    public AppTest( String testName )
    {
        super( testName );
    }

    /**
     * @return the suite of tests being tested
     */
    public static Test suite()
    {
        return new TestSuite( AppTest.class );
    }

    /**
     * Rigourous Test :-)
     */
    public void testApp()
    {
        assertTrue( true );
    }

    public void testDto() {
        HostTemplateDto hostTemplateDto = new HostTemplateDto();
        hostTemplateDto.setHost("server1");
        ArrayList<String> templateIds = new ArrayList<>();
        templateIds.add("10001");
        templateIds.add("10021");
        hostTemplateDto.setTemplateIds(templateIds);
        System.out.println(new Gson().toJson(hostTemplateDto));
    }

    /**
     * 检查redis中的值
     */
    public void testRedisKey() {
        JedisPool jedisPool = buildJedisPool();
        Jedis resource = jedisPool.getResource();
        String val = resource.get("key6");
        System.out.println(val);
        HashMap<String, TriggerDto> TriggerDtos = new Gson().fromJson(val,
                new TypeToken<HashMap<String, TriggerDto>>() {
                }.getType());
        System.out.println("TriggerDtos:" + new Gson().toJson(TriggerDtos));
        resource.close();
        jedisPool.close();
    }


    private JedisPool buildJedisPool() {
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
        return new JedisPool(poolConfig, "192.168.2.198");
    }
}
