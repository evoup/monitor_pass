package com.evoupsight.monitorpass;

import org.junit.Test;

import java.util.regex.Matcher;
import java.util.regex.Pattern;

/**
 * Unit test for simple App.
 */
public class ExpressionTest {
    @Test
    public void testExpression() {
        String expression = "{2}>5 and {3}>12";

        String regex = "\\{([^}]*)\\}";
        String x= expression.replaceAll(regex, myFun("$1"));
        System.out.println(x);
        // 按指定模式在字符串查找
//        Pattern pattern = Pattern.compile(regex);
//        Matcher matcher = pattern.matcher(expression);
//        while (matcher.find()) {
//            System.out.println(matcher.group());
//        }
    }

    public String myFun(String x) {
        return "x" + x;
    }


    @Test
    public void testExpression2() {
        Pattern p = Pattern.compile("\\{([^}]*)\\}");
        Matcher m = p.matcher("{2}>5 and {3}>12");
        StringBuffer sb = new StringBuffer();
        while (m.find()) {
            m.appendReplacement(sb, myFun(m.group(1)));
        }
        m.appendTail(sb);
        System.out.println(sb.toString());
    }
}