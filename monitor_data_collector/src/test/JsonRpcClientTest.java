import com.evoupsight.monitorpass.datacollector.dao.model.Server;
import com.evoupsight.monitorpass.datacollector.services.impl.MonitorItemConfigPollerServiceImpl;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.springframework.test.context.ContextConfiguration;
import org.springframework.test.context.junit4.SpringJUnit4ClassRunner;

/**
 * https://www.cnblogs.com/geomantic/p/4751859.html
 */
//@RunWith(SpringJUnit4ClassRunner.class)
//@ContextConfiguration(locations = {"classpath:applicationContext.xml"})
public class JsonRpcClientTest {
    @Test
    public void aTest() {
        try {
            Server server = new Server();
            server.setName("evoup-Inspiron-3443");
            server.setHostname("evoup-Inspiron-3443");
            server.setAgentAddress("192.168.2.194:8338");
            new MonitorItemConfigPollerServiceImpl().configUpdatePoll(server);
        } catch (Throwable throwable) {
            throwable.printStackTrace();
        }
    }

}
