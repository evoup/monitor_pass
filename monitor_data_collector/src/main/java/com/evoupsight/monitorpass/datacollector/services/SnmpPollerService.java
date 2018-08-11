package com.evoupsight.monitorpass.datacollector.services;

import com.evoupsight.monitorpass.datacollector.snmp.SnmpManager;
import org.apache.log4j.Logger;
import org.snmp4j.smi.OID;
import org.springframework.stereotype.Service;

import java.io.IOException;
import java.util.ArrayList;
import java.util.List;
import java.util.concurrent.*;

/**
 * @author evoup
 */
@Service
public class SnmpPollerService {
    private static final Logger LOG = Logger.getLogger(SnmpPollerService.class);

    public void poll() {
        int totaltaskNum = 50;

        ExecutorService es = Executors.newFixedThreadPool(5);
//        ThreadFactory namedThreadFactory = new ThreadFactoryBuilder().setNameFormat("snmp-pool-%d").build();
//        ExecutorService es = new ThreadPoolExecutor(5, 200, 0L,
//                TimeUnit.MILLISECONDS, new LinkedBlockingDeque<Runnable>(1024), namedThreadFactory, new ThreadPoolExecutor.AbortPolicy());
        List<Callable<Boolean>> callableTasks = new ArrayList<>();
        for (int i = 0; i < totaltaskNum; i++) {
            callableTasks.add(callableTask);
        }

        try {
            List<Future<Boolean>> answers = es.invokeAll(callableTasks);
            LOG.info("snmp poll done");
            System.out.println("snmp poll done");
        } catch (InterruptedException e) {
            e.printStackTrace();
            LOG.error(e.getMessage(), e);
        } finally {
            es.shutdownNow();
        }
    }


    private void snmpPoll() throws IOException {
        /**
         * Port 161 is used for Read and Other operations
         * Port 162 is used for the trap generation
         */
//        SnmpManager client = new SnmpManager("udp:127.0.0.1/161");
        SnmpManager client = new SnmpManager("udp:192.168.2.4/161");
        try {
            client.start();
            /**
             * OID - .1.3.6.1.2.1.1.1.0 => SysDec
             * OID - .1.3.6.1.2.1.1.5.0 => SysName
             * => MIB explorer will be usefull here, as discussed in previous article
             */
            String sysDescr = client.getAsString(new OID(".1.3.6.1.2.1.1.5.0"), "public");
            System.out.println("snmp metric " + sysDescr);
            LOG.info("snmp metric " + sysDescr);
        } finally {
            client.close();
        }
    }


    private Callable<Boolean> callableTask = () -> {
        try {
            snmpPoll();
            return Boolean.TRUE;
        } catch (IOException e) {
            e.printStackTrace();
            return Boolean.FALSE;
        }
    };
}
