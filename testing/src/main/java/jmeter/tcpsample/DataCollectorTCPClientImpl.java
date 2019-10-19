package jmeter.tcpsample;

import org.apache.commons.lang3.ArrayUtils;
import org.apache.commons.lang3.StringUtils;
import org.apache.jmeter.protocol.tcp.sampler.AbstractTCPClient;
import org.apache.jmeter.protocol.tcp.sampler.ReadException;
import org.apache.jmeter.util.JMeterUtils;
import org.apache.jorphan.util.JOrphanUtils;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.nio.charset.Charset;
import java.util.Arrays;

/**
 * @author evoup
 */
public class DataCollectorTCPClientImpl extends AbstractTCPClient {
    private static final Logger log = LoggerFactory.getLogger(DataCollectorTCPClientImpl.class);
    //\r\n
    private static final String CRLF = "0d0a";
    private static final String CHARSET = JMeterUtils.getPropDefault("tcp.charset", Charset.defaultCharset().name());

    public DataCollectorTCPClientImpl() {
        super();
        setCharset(CHARSET);

        String configuredCharset = JMeterUtils.getProperty("tcp.charset");

        if (StringUtils.isEmpty(configuredCharset)) {
            log.info("Using platform default charset:" + CHARSET);
        } else {
            log.info("Using charset:" + configuredCharset);
        }
    }

    @Override
    public String read(InputStream is) throws ReadException {
        ByteArrayOutputStream w = new ByteArrayOutputStream();
        try {
            byte[] buffer = new byte[4096];
            int x = 0;
            while ((x = is.read(buffer)) > -1) {
                w.write(buffer, 0, x);
                int tail = CRLF.length() / 2;

                byte[] eolBuffer = w.toByteArray();
                if (JOrphanUtils.baToHexString(Arrays.copyOfRange(eolBuffer, eolBuffer.length - tail, eolBuffer.length))
                        .equals(JOrphanUtils.baToHexString(hexStringToByteArray(CRLF)))) {
                    break;
                }
            }

            return w.toString(CHARSET);
        } catch (IOException e) {
            throw new ReadException("Error reading from server, bytes read: " + w.size(), e, w.toString());
        }
    }

    @Override
    public void write(OutputStream os, InputStream is) throws IOException {
        byte[] buff = new byte[512];
        while (is.read(buff) > 0) {
            os.write(buff);
            os.flush();
        }
    }

    @Override
    public void write(OutputStream os, String s) throws IOException {

        byte[] buffer = s.getBytes(CHARSET);
        byte[] eolBuffer = ArrayUtils.addAll(buffer, hexStringToByteArray(CRLF));
        os.write(eolBuffer);
        os.flush();
    }

    private static byte[] hexStringToByteArray(String hexEncodedBinary) {
        if (hexEncodedBinary.length() % 2 == 0) {
            char[] sc = hexEncodedBinary.toCharArray();
            byte[] ba = new byte[sc.length / 2];

            for (int i = 0; i < ba.length; i++) {
                int nibble0 = Character.digit(sc[i * 2], 16);
                int nibble1 = Character.digit(sc[i * 2 + 1], 16);
                if (nibble0 == -1 || nibble1 == -1) {
                    throw new IllegalArgumentException(
                            "Hex-encoded binary string contains an invalid hex digit in '" + sc[i * 2] + sc[i * 2 + 1] + "'");
                }
                ba[i] = (byte) ((nibble0 << 4) | (nibble1));
            }

            return ba;
        } else {
            throw new IllegalArgumentException(
                    "Hex-encoded binary string contains an uneven no. of digits");
        }
    }

}
