package com.evoupsight.monitorpass.datacollector.services.impl;

import com.evoupsight.monitorpass.datacollector.services.SnmpPollerService;
import org.apache.log4j.Logger;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.beans.factory.annotation.Qualifier;
import org.springframework.stereotype.Service;

import java.util.ArrayList;
import java.util.List;
import java.util.concurrent.Callable;
import java.util.concurrent.ExecutorService;

/**
 * @author evoup
 */
@Service
public class MonitorItemConfigPollerServiceImpl {
    @Autowired
    @Qualifier("monitorItemConfigExecutorServiceThreadPool")
    protected ExecutorService es;
    private static final Logger LOG = Logger.getLogger(SnmpPollerService.class);

    public void poll() {
        int totalTaskNum = 10;

        List<Callable<Boolean>> callableTasks = new ArrayList<>();
        for (int i = 0; i < totalTaskNum; i++) {
            callableTasks.add(callableTask);
        }

        try {
            es.invokeAll(callableTasks);
            LOG.info("config update poll done");
            System.out.println("config update poll done");
        } catch (InterruptedException e) {
            e.printStackTrace();
            LOG.error(e.getMessage(), e);
        }
    }

    private void configUpdatePoll() {
        LOG.info("monitor item rpc client updater");
    }

    private Callable<Boolean> callableTask = () -> {
        configUpdatePoll();
        return Boolean.TRUE;
    };
}
