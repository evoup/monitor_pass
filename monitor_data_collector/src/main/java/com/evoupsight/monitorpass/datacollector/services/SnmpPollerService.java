package com.evoupsight.monitorpass.datacollector.services;

import com.evoupsight.monitorpass.datacollector.snmp.SnmpManager;
import org.apache.log4j.Logger;
import org.snmp4j.smi.OID;
import org.springframework.stereotype.Service;

import java.io.IOException;

/**
 * @author evoup
 */
@Service
public class SnmpPollerService {
    private static final Logger LOG = Logger.getLogger(SnmpPollerService.class);

    public void poll() throws IOException {
        /**
         * Port 161 is used for Read and Other operations
         * Port 162 is used for the trap generation
         */
//        SnmpManager client = new SnmpManager("udp:127.0.0.1/161");
        SnmpManager client = new SnmpManager("udp:192.168.2.4/161");
        client.start();
        /**
         * OID - .1.3.6.1.2.1.1.1.0 => SysDec
         * OID - .1.3.6.1.2.1.1.5.0 => SysName
         * => MIB explorer will be usefull here, as discussed in previous article
         */
        String sysDescr = client.getAsString(new OID(".1.3.6.1.2.1.1.5.0"), "public");
        System.out.println(sysDescr);
        LOG.info("snmp metric " + sysDescr);
        client.close();
    }
}
