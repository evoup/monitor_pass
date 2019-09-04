import com.evoupsight.monitorpass.datacollector.dao.model.Server;
import com.evoupsight.monitorpass.datacollector.services.impl.MonitorItemConfigPollerServiceImpl;
import org.junit.Test;

/**
 * https://www.cnblogs.com/geomantic/p/4751859.html
 */
public class JsonRpcClientTest {
    @Test
    public void aTest() {
        try {
            Server server = new Server();
            server.setName("testServer");
            server.setHostname("testServer");
            server.setAgentAddress("192.168.2.194:8338");
            new MonitorItemConfigPollerServiceImpl().configUpdatePoll(server);
        } catch (Throwable throwable) {
            throwable.printStackTrace();
        }
    }

}
