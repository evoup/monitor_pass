import org.junit.Test;

public class ByteToStringTest {


    @Test
    public void testSubString() {
        System.out.println(printHexString("data".getBytes()));
    }

    public   String printHexString( byte[] b) {
        String a = "";
        for (int i = 0; i < b.length; i++) {
            String hex = Integer.toHexString(b[i] & 0xFF);
            if (hex.length() == 1) {
                hex = '0' + hex;
            }

            a = a+hex;
        }

        return a;
    }
}
