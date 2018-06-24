package com.evoupsight.monitorpass.services;

import com.evoupsight.monitorpass.QueryInfo;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Component;
import org.springframework.stereotype.Service;

import java.io.IOException;

@Component
public class CacheTask {

    @Scheduled(fixedDelay = 5000)
    public void cache() throws IOException {
        System.out.println("test");
//        QueryInfo queryInfo = new QueryInfo();
//        //queryInfo.getRow();
//        queryInfo.getScanData();
    }
}
