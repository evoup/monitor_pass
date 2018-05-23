import org.junit.Test;

import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class MessageFormatTest {
    private static final Pattern
            CLIENT_GET_CONF_MESSAGE = Pattern.compile("getconf\\|(.*)");

    @Test
    public void testSubString() {
        String msg = "12121212,monitormsg";
        System.out.println( msg.substring(0, msg.indexOf(",")));
    }

    @Test
    public void testClientGetConf() {
        String msg = "getconf|host1\r\n";
        //String msg = "getconf|hostName";
        Matcher m = CLIENT_GET_CONF_MESSAGE.matcher(msg);
        if (!m.matches()) {
            System.out.println("err");
        }
        Matcher matcher = CLIENT_GET_CONF_MESSAGE.matcher(msg);
        while (matcher.find()) {
            System.out.println("group 0: " + matcher.group(0));
            System.out.println("group 1: " + matcher.group(1));
        }
    }
}
