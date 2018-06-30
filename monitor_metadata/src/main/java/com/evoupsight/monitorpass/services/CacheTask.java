package com.evoupsight.monitorpass.services;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Component;

/**
 * @author evoup
 */
@Component
public class CacheTask {
    private final QueryInfoService queryInfoService;

    @Autowired
    public CacheTask(QueryInfoService queryInfoService) {
        this.queryInfoService = queryInfoService;
    }

    @Scheduled(fixedDelay = 5000)
    public void cache() {
        queryInfoService.getScanData();
    }
}
