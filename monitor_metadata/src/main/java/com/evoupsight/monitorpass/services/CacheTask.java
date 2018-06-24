package com.evoupsight.monitorpass.services;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Component;

import java.io.IOException;

/**
 * @author evoup
 */
@Component
public class CacheTask {
    private static final Logger LOG = LoggerFactory.getLogger(CacheTask.class);
    private final QueryInfoService queryInfoService;

    @Autowired
    public CacheTask(QueryInfoService queryInfoService) {
        this.queryInfoService = queryInfoService;
    }

    @Scheduled(fixedDelay = 5000)
    public void cache() throws IOException {
        LOG.info("test");
        queryInfoService.getRow();
        queryInfoService.getScanData();
    }
}
