import com.evoupsight.monitorpass.datacollector.constants.RpcConstant;
import com.googlecode.jsonrpc4j.JsonRpcClient;
import org.junit.Test;

import java.io.InputStream;
import java.io.OutputStream;
import java.net.Socket;

/**
 * https://www.cnblogs.com/geomantic/p/4751859.html
 */
public class JsonRpcClientTest {
    @Test
    public void aTest() {
        try {
            Socket socket = new Socket("127.0.0.1", 8338);
            JsonRpcClient client = new JsonRpcClient();

            InputStream ips = socket.getInputStream();
            OutputStream ops = socket.getOutputStream();

            int reply = client.invokeAndReadResponse("MonitorItemConfig.Update", new Object[]{"memeda"}, int.class, ops, ips);

            System.out.println("reply: " + reply);
            if (RpcConstant.SERVER_OK.code.equals(reply)) {
                System.out.println("调用服务成功");
            } else {
                System.out.println("调用服务失败");
            }
        } catch (Throwable throwable) {
            throwable.printStackTrace();
        }
    }

}
