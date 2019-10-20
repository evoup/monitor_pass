package jmeter.javarequest;

import org.apache.jmeter.config.Arguments;
import org.apache.jmeter.protocol.java.sampler.AbstractJavaSamplerClient;
import org.apache.jmeter.protocol.java.sampler.JavaSamplerContext;
import org.apache.jmeter.samplers.SampleResult;

import java.io.IOException;
import java.net.InetSocketAddress;
import java.net.Socket;
import java.net.SocketAddress;

/**
 * @author evoup
 * 测试数据收集器的并发连接数
 */
@SuppressWarnings("unused")
public class JavaRequest extends AbstractJavaSamplerClient {


    @Override
    public Arguments getDefaultParameters() {
        return super.getDefaultParameters();
    }

    @Override
    public void setupTest(JavaSamplerContext context) {
        super.setupTest(context);
    }

    @Override
    public SampleResult runTest(JavaSamplerContext javaSamplerContext) {
        return getSampleResult();
    }

    private SampleResult getSampleResult() {
        SampleResult result = new SampleResult();

        boolean success = true;
        result.sampleStart();
        // Write your test code here.
        Socket socket = new Socket();
        SocketAddress address = new InetSocketAddress("data-collector", 8091);
        try {
            socket.connect(address);
        } catch (IOException e) {
            e.printStackTrace();
            success = false;
        } finally {
            result.sampleEnd();
            result.setSuccessful(success);
            try {
                socket.close();
            } catch (IOException e) {
                e.printStackTrace();
            }
        }
        return result;
    }

    @Override
    public void teardownTest(JavaSamplerContext context) {
        super.teardownTest(context);
    }
}
