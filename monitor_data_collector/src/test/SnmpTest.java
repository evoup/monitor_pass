import com.evoupsight.monitorPass.dataCollector.snmp.SNMPManager;
import org.junit.Test;
import org.snmp4j.smi.OID;

import java.io.IOException;


public class SnmpTest {

    @Test
    public void MySnmpGet() throws IOException {

/**
 * Port 161 is used for Read and Other operations
 * Port 162 is used for the trap generation
 */
        SNMPManager client = new SNMPManager("udp:127.0.0.1/161");
        client.start();
/**
 * OID - .1.3.6.1.2.1.1.1.0 => SysDec
 * OID - .1.3.6.1.2.1.1.5.0 => SysName
 * => MIB explorer will be usefull here, as discussed in previous article
 */
        String sysDescr = client.getAsString(new OID(".1.3.6.1.2.1.1.1.0"));
//        String sysDescr = client.getAsString(new OID(".1.3.6.1.4.1.2021.10.1.3.1"));
        System.out.println(sysDescr);
    }


}
