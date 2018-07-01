package com.evoupsight.monitorpass.services;

import org.apache.hadoop.conf.Configuration;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Component;
import redis.clients.jedis.JedisPool;

/**
 * @author evoup
 */
@Component
public class CacheTask {
    private final JedisPool jedisPool;

    private final Configuration hBaseConf;

    @Autowired
    public CacheTask(JedisPool jedisPool, Configuration hBaseConf) {
        this.jedisPool = jedisPool;
        this.hBaseConf = hBaseConf;
    }

    @Scheduled(fixedDelay = 5000)
    public void cache() {
        QueryInfoService.getInstance(hBaseConf, jedisPool).cacheData();
    }
}
