package com.evoupsight.monitorpass.server.cfg;

import org.apache.curator.RetryPolicy;
import org.apache.curator.framework.CuratorFramework;
import org.apache.curator.framework.CuratorFrameworkFactory;
import org.apache.curator.retry.ExponentialBackoffRetry;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.context.annotation.PropertySource;
import org.springframework.context.annotation.PropertySources;


/**
 * @author evoup
 */
@Configuration
@PropertySources({
        @PropertySource("server.properties")
})
public class Config {
    @Value("${zk.servers}")
    String zkServers;

    @Bean
    public CuratorFramework becameCurator() {
        int baseSleepTimeMills = 1000;
        int maxRetries = 3;
        RetryPolicy retryPolicy = new ExponentialBackoffRetry(baseSleepTimeMills, maxRetries);
        return CuratorFrameworkFactory.newClient(zkServers, retryPolicy);
    }
}
