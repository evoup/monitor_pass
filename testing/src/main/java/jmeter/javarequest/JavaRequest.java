package jmeter.javarequest;

import exception.InvalidProtocolException;
import org.apache.commons.lang.RandomStringUtils;
import org.apache.jmeter.config.Arguments;
import org.apache.jmeter.protocol.java.sampler.AbstractJavaSamplerClient;
import org.apache.jmeter.protocol.java.sampler.JavaSamplerContext;
import org.apache.jmeter.samplers.SampleResult;
import utils.Base64;
import utils.PasswordHash;
import utils.ScramUtils;

import java.io.*;
import java.net.InetSocketAddress;
import java.net.Socket;
import java.net.SocketAddress;
import java.net.SocketException;
import java.security.InvalidKeyException;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.security.spec.InvalidKeySpecException;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

/**
 * @author evoup
 * 测试数据收集器的并发连接数
 */
@SuppressWarnings("unused")
public class JavaRequest extends AbstractJavaSamplerClient {
    private static final Pattern SERVER_FIRST_MESSAGE = Pattern.compile("r=([^,]*),s=([^,]*),i=(.*)$");

    private static final String CLIENT_HEADER = "biws";
    private static final String CLIENT_PASS = "pencil";
    @SuppressWarnings("SpellCheckingInspection")
    private static final int PBKDF2_LEN = 20;

    private static final String AUTH_FAIL_MESSAGE = "invalid protocol";

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
        SampleResult result = new SampleResult();
        boolean success = false;
        result.sampleStart();
        try {
            scramSha1SendData();
            success = true;
        } catch (SocketException | InvalidKeySpecException | InvalidKeyException | NoSuchAlgorithmException | InvalidProtocolException e) {
            e.printStackTrace();
        } finally {
            result.sampleEnd();
            result.setSuccessful(success);
        }
        return result;
    }

    private void scramSha1SendData() throws SocketException, InvalidKeySpecException, NoSuchAlgorithmException, InvalidKeyException, InvalidProtocolException {
        String cName = "host1";
        String cNonce = randStringBytesRmndr();
        String clientFirstMessage = clientFirstMessage(cName, cNonce);
        Socket socket = new Socket();
        socket.setSoTimeout(3000000);
        SocketAddress address = new InetSocketAddress("data-collector", 8091);
        try {
            socket.connect(address);
            // 写数据,客户端第一次消息
            sendMessage(clientFirstMessage, socket);

            // 读数据,服务端第一次消息
            String serverFirstMessageData = receiveMessage(socket);
            System.out.println(serverFirstMessageData);

            // 反解服务端第一次消息
            Matcher m = SERVER_FIRST_MESSAGE.matcher(serverFirstMessageData);
            if (m.matches()) {
                String nonce = m.group(1);
                String salt = m.group(2);
                String iterator = m.group(3);
                int clientNonceLength = cNonce.length();
                String sNonce = nonce.substring(clientNonceLength);
                String authMessage = authMessage(cName, cNonce, sNonce, CLIENT_HEADER, serverFirstMessageData);
                byte[] decodeSalt = Base64.decode(salt);
                byte[] saltedPassword = PasswordHash.pbkdf2(CLIENT_PASS.toCharArray(), decodeSalt, Integer.valueOf(iterator), PBKDF2_LEN);
                byte[] clientKey = ScramUtils.computeHmac(saltedPassword, "HmacSHA1", "Client Key");
                byte[] storedKey = MessageDigest.getInstance("SHA-1").digest(clientKey);
                byte[] clientSignature = ScramUtils.computeHmac(storedKey, "HmacSHA1", authMessage);
                byte[] clientProof = new byte[20];
                for (int i = 0; i < clientKey.length; i++) {
                    int x = clientKey[i] ^ clientSignature[i];
                    clientProof[i] = (byte) x;
                }
                String out = clientFinalMessageWithoutProof(CLIENT_HEADER, cNonce, sNonce);
                out = String.format("%s,p=%s", out, Base64.encodeBytes(clientProof));
                // 发送客户端最后一次认证数据
                sendMessage(out, socket);

                // 读取服务端最后一次认证数据
                String serverFinalMessage = receiveMessage(socket);
                // v=3nL1m8VUvU6P1PQuimVgk02i5Ck=
                System.out.println(serverFinalMessage);
                if (serverFinalMessage.contains(AUTH_FAIL_MESSAGE)) {
                    throw new InvalidProtocolException();
                }
                // 认证基本完成，客户端想再认证一次就认证一次，这里是测试可略过，接下里继续发数据就行了
                // 每隔15秒发送
            }
        } catch (IOException e) {
            e.printStackTrace();
        } finally {
            try {
                socket.close();
            } catch (IOException e) {
                e.printStackTrace();
            }
        }
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

    private String randStringBytesRmndr() {
        return RandomStringUtils.random(10, true, false);
    }


    private String clientFirstMessageBare(String cName, String cNonce) {
        return String.format("n=%s,r=%s", cName, cNonce);
    }


    private String clientFirstMessage(String cName, String cNonce) {
        return String.format("n,,%s", clientFirstMessageBare(cName, cNonce));
    }

    private String clientFinalMessageWithoutProof(String cHeader, String cNonce, String sNonce) {
        String nonce = String.format("%s%s", cNonce, sNonce);
        return String.format("c=%s,r=%s", cHeader, nonce);
    }

    private String receiveMessage(Socket socket) throws IOException {
        InputStream in = socket.getInputStream();
        int c;
        String raw = "";
        do {
            c = in.read();
            raw += (char) c;
        } while (in.available() > 0);
        return raw;
    }

    private void sendMessage(String clientFirstMessage, Socket socket) throws IOException {
        OutputStream out = socket.getOutputStream();
        Writer writer = new OutputStreamWriter(out, "UTF-8");
        writer.write(clientFirstMessage);
        writer.flush();
    }

    private String authMessage(String cName, String cNonce, String sNonce, String cHeader, String serverFirstMessage) {
        String out = clientFirstMessageBare(cName, cNonce);
        String clientFinalMessageWithoutProof = clientFinalMessageWithoutProof(cHeader, cNonce, sNonce);
        out = String.format("%s,%s,%s", out, serverFirstMessage, clientFinalMessageWithoutProof);
        return out;
    }
}
