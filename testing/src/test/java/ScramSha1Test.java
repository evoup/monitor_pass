import org.apache.commons.lang.RandomStringUtils;
import org.junit.Test;
import utils.Base64;

import java.io.*;
import java.net.InetSocketAddress;
import java.net.Socket;
import java.net.SocketAddress;
import java.net.SocketException;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class ScramSha1Test {

    private static final Pattern SERVER_FIRST_MESSAGE = Pattern.compile("r=([^,]*),s=([^,]*),i=(.*)$");

    private static final String ClientHeader = "biws";

    @SuppressWarnings("Duplicates")
    @Test
    public void scramSha1Client() throws SocketException {
        String cName = "host1";
        String cNonce = randStringBytesRmndr();
        System.out.println(cNonce);
        String clientFirstMessage = clientFirstMessage(cName, cNonce);
        System.out.println(clientFirstMessage);
        Socket socket = new Socket();
        socket.setSoTimeout(30000);
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
                System.out.println(m);
                String nonce = m.group(1);
                String salt = m.group(2);
                String iterator = m.group(3);
                System.out.println(nonce);
                System.out.println(salt);
                System.out.println(iterator);
                int clientNonceLength = cNonce.length();
                String sNonce = nonce.substring(clientNonceLength);
                String authMessage = authMessage(cName, cNonce, sNonce, ClientHeader, serverFirstMessageData);
                byte[] decodeSalt = Base64.decode(salt);

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


    private String randStringBytesRmndr() {
        return RandomStringUtils.random(10, true, false);
    }


    private String clientFirstMessageBare(String cName, String cNonce) {
        return String.format("n=%s,r=%s", cName, cNonce);
    }


    private String clientFirstMessage(String cName, String cNonce) {
        return String.format("n,,%s", clientFirstMessageBare(cName, cNonce));
    }

//    func authMessage(cName string, cNonce []byte, sNonce []byte, cHeader string, serverFirstMessage string) (out []byte) {
//        out = clientFirstMessageBare([]byte(cName), cNonce)
//        out = append(out, ","...)
//        out = append(out, serverFirstMessage...)
//        out = append(out, ","...)
//        out = append(out, clientFinalMessageWithoutProof([]byte(cHeader), cNonce, sNonce)...)
//        return
//    }
    private String authMessage(String cName, String cNonce, String sNonce, String cHeader, String serverFirstMessage) {
        String out = clientFirstMessageBare(cName, cNonce);
        String clientFinalMessageWithoutProof = clientFinalMessageWithoutProof(cHeader, cNonce, sNonce);
        out = String.format("%s,%s,%s", out, serverFirstMessage, clientFinalMessageWithoutProof);
        return out;
    }

//    func clientFinalMessageWithoutProof(cHeader, cNonce, sNonce []byte) (out []byte) {
//        nonce := append(cNonce, sNonce...)
//
//        out = []byte("c=")
//        out = append(out, cHeader...)
//        out = append(out, ",r="...)
//        out = append(out, nonce...)
//        return
//    }
    private String clientFinalMessageWithoutProof(String cHeader, String cNonce, String sNonce) {
        String nonce = String.format("%s%s", cNonce, sNonce);
        return String.format("c=%s,r=%s", cHeader, nonce);
    }
}
