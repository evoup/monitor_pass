package com.evoupsight.monitorpass.server.cfg;


import org.apache.hadoop.fs.Path;
import org.apache.hadoop.hbase.HBaseConfiguration;
import org.opentsdb.client.PoolingHttpClient;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.context.annotation.PropertySource;
import org.springframework.context.annotation.PropertySources;
import redis.clients.jedis.JedisPool;
import redis.clients.jedis.JedisPoolConfig;

import java.net.URL;
import java.time.Duration;

/**
 * @author evoup
 */
@Configuration
@PropertySources({
        @PropertySource({"server.properties", "redis.properties"})
})
public class Config {

    @Value("${redis.host}")
    String redisHost;

    @Bean
    public org.apache.hadoop.conf.Configuration hbaseConf() {
        org.apache.hadoop.conf.Configuration config = HBaseConfiguration.create();
        ClassLoader classLoader = this.getClass().getClassLoader();
        URL resource = classLoader.getResource("hbase-site.xml");
        if (resource != null) {
            String path = resource.getPath();
            config.addResource(new Path(path));
            return config;
        }
        return null;
    }

    @Bean
    public JedisPool buildJedisPool() {
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
        return new JedisPool(poolConfig, redisHost);
    }

    @Bean(name = "http_client_pool")
    public PoolingHttpClient poolingHttpClient() {
        return new PoolingHttpClient();
    }
}
