package com.evoupsight.monitorpass.server.cfg;

import org.springframework.beans.factory.annotation.Qualifier;
import org.springframework.cache.annotation.EnableCaching;
import org.springframework.cache.concurrent.ConcurrentMapCacheManager;
import org.springframework.cache.guava.GuavaCacheManager;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.context.annotation.Primary;

import static com.evoupsight.monitorpass.server.constants.CacheConstants.*;


/**
 * spring缓存配置（guava简单版，仅仅为了缓存自动过期而以）
 *
 * @author evoup
 */
@EnableCaching()
@Configuration
public class CacheConfig {

    @Bean
    @Qualifier(CACHE_MANAGER_CONCURRENTMAP)
    @Primary
    ConcurrentMapCacheManager concurrentMapCacheManager() {
        return new ConcurrentMapCacheManager();
    }

    @Bean
    @Qualifier(CACHE_MANAGER_GUAVA)
    GuavaCacheManager guavaCacheManager() {
        GuavaCacheManager guavaCacheManager = new GuavaCacheManager();
        guavaCacheManager.setCacheSpecification("maximumSize=50000,expireAfterWrite=2m");
        return guavaCacheManager;
    }

    @Bean
    @Qualifier(CACHE_MANAGER_GUAVA_EVENT)
    GuavaCacheManager guavaCacheManagerEvent() {
        GuavaCacheManager guavaCacheManager = new GuavaCacheManager();
        guavaCacheManager.setCacheSpecification("maximumSize=50000, expireAfterWrite=1m");
        return guavaCacheManager;
    }
}
