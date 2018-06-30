package com.evoupsight.monitorpass.server.services;


import org.apache.curator.RetryPolicy;
import org.apache.curator.framework.CuratorFramework;
import org.apache.curator.framework.CuratorFrameworkFactory;
import org.apache.curator.framework.recipes.locks.InterProcessLock;
import org.apache.curator.framework.recipes.locks.InterProcessMutex;
import org.apache.curator.retry.ExponentialBackoffRetry;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Component;

import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.concurrent.TimeUnit;

import static com.evoupsight.monitorpass.server.constants.Constants.*;


/**
 * @author evoup
 */
@Component
public class ScanEventTask {
    private static final Logger LOG = LoggerFactory.getLogger(ScanEventTask.class);

    private static final SimpleDateFormat DATE_FORMAT = new SimpleDateFormat("HH:mm:ss");

    @Value("${zk.servers}")
    String zkServers;

    private final ScanService scanService;

    @Autowired
    public ScanEventTask(ScanService scanService) {
        this.scanService = scanService;
    }

    @Scheduled(fixedRate = 5000)
    public void doAllJobs() {
        int baseSleepTimeMills = 1000;
        int maxRetries = 3;
        RetryPolicy retryPolicy = new ExponentialBackoffRetry(baseSleepTimeMills, maxRetries);
        try (CuratorFramework cfClient = CuratorFrameworkFactory.newClient(zkServers, retryPolicy)) {
            cfClient.start();
            InterProcessLock lock = new InterProcessMutex(cfClient, LOCK_PATH);
            if (lock.acquire(DEFAULT_WAIT_TIME_SECONDS, TimeUnit.SECONDS)) {
                try {
                    LOG.info("server loop");
                    scanService.doAllJobs();
                    Thread.sleep(15000);
                } finally {
                    lock.release();
                }
            } else {
                LOG.error("{} timed out after {} seconds waiting to acquire lock on {}",
                        MY_NAME, DEFAULT_WAIT_TIME_SECONDS, LOCK_PATH);
            }
        } catch (Exception e) {
            LOG.error(e.getMessage(), e);
        }

        LOG.info("The time is now {}", DATE_FORMAT.format(new Date()));
    }
}
