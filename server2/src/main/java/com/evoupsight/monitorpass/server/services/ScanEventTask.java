package com.evoupsight.monitorpass.server.services;



import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.concurrent.TimeUnit;

import org.apache.curator.RetryPolicy;
import org.apache.curator.framework.CuratorFramework;
import org.apache.curator.framework.CuratorFrameworkFactory;
import org.apache.curator.framework.recipes.locks.InterProcessLock;
import org.apache.curator.framework.recipes.locks.InterProcessMutex;
import org.apache.curator.retry.ExponentialBackoffRetry;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Component;

@Component
public class ScanEventTask {
    private static final Logger LOG = LoggerFactory.getLogger(ScanEventTask.class);
    private static final long DEFAULT_WAIT_TIME_SECONDS = Long.MAX_VALUE;

    private static final SimpleDateFormat dateFormat = new SimpleDateFormat("HH:mm:ss");

    @Value("${zk.servers}")
    private String zkServers;

    @Scheduled(fixedRate = 5000)
    public void reportCurrentTime() throws Exception {
        LOG.info(zkServers);
        String hosts = "zk1:2181,zk2:2181,zk3:2181";
        String lockPath = "/hehe";
        String myName = "test";
        long waitTimeSeconds = DEFAULT_WAIT_TIME_SECONDS;
        int baseSleepTimeMills = 1000;
        int maxRetries = 3;
        RetryPolicy retryPolicy = new ExponentialBackoffRetry(baseSleepTimeMills, maxRetries);
        CuratorFramework client = CuratorFrameworkFactory.newClient(hosts, retryPolicy);
        client.start();
        InterProcessLock lock = new InterProcessMutex(client, lockPath);
        if (lock.acquire(waitTimeSeconds, TimeUnit.SECONDS)) {
            try {
                //doSomeWork(myName);
                LOG.info("doSomeWork");
                Thread.sleep(15000);
            } finally {
                lock.release();
            }
        } else {
            LOG.error("{} timed out after {} seconds waiting to acquire lock on {}",
                    myName, waitTimeSeconds, lockPath);
        }

        client.close();

        LOG.info("The time is now {}", dateFormat.format(new Date()));
    }
}
