import org.junit.Test;

public class MessageFormatTest {

    @Test
    public void testSubString() {
        String msg = "12121212,monitormsg";
        System.out.println( msg.substring(0, msg.indexOf(",")));
    }
}
