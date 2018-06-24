package com.evoupsight.monitorpass.services;

import com.evoupsight.monitorpass.QueryInfo;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Component;
import org.springframework.stereotype.Service;

import java.io.IOException;

@Component
public class CacheTask {
    private static final Logger LOG = LoggerFactory.getLogger(CacheTask.class);

    @Scheduled(fixedDelay = 5000)
    public void cache() throws IOException {
        LOG.info("test");
//        QueryInfo queryInfo = new QueryInfo();
//        //queryInfo.getRow();
//        queryInfo.getScanData();
    }
}
