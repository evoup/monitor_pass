package com.evoupsight.monitorpass.server.services;


import org.apache.curator.framework.CuratorFramework;
import org.apache.curator.framework.recipes.locks.InterProcessLock;
import org.apache.curator.framework.recipes.locks.InterProcessMutex;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.beans.factory.annotation.Qualifier;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Component;

import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.concurrent.TimeUnit;

import static com.evoupsight.monitorpass.constants.Constants.*;

/**
 * @author evoup
 */
@Component
public class ScanEventTask {
    private static final Logger LOG = LoggerFactory.getLogger(ScanEventTask.class);


    private static final SimpleDateFormat DATE_FORMAT = new SimpleDateFormat("HH:mm:ss");



    private final
    CuratorFramework cfClient;

    @Autowired
    public ScanEventTask(@Qualifier("cfClient") CuratorFramework cfClient) {
        this.cfClient = cfClient;
        this.cfClient.start();
    }

    @Scheduled(fixedRate = 5000)
    public void reportCurrentTime() throws Exception {
        InterProcessLock lock = new InterProcessMutex(cfClient, LOCK_PATH);
        if (lock.acquire(DEFAULT_WAIT_TIME_SECONDS, TimeUnit.SECONDS)) {
            try {
                //doSomeWork(myName);
                LOG.info("doSomeWork");
                Thread.sleep(15000);
            } finally {
                lock.release();
            }
        } else {
            LOG.error("{} timed out after {} seconds waiting to acquire lock on {}",
                    MY_NAME, DEFAULT_WAIT_TIME_SECONDS, LOCK_PATH);
        }

//        cfClient.close();

        LOG.info("The time is now {}", DATE_FORMAT.format(new Date()));
    }
}
