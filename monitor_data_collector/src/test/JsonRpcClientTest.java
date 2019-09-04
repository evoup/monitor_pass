import com.evoupsight.monitorpass.datacollector.services.impl.MonitorItemConfigPollerServiceImpl;
import org.junit.Test;

/**
 * https://www.cnblogs.com/geomantic/p/4751859.html
 */
public class JsonRpcClientTest {
    @Test
    public void aTest() {
        try {
            new MonitorItemConfigPollerServiceImpl().configUpdatePoll();
        } catch (Throwable throwable) {
            throwable.printStackTrace();
        }
    }

}
