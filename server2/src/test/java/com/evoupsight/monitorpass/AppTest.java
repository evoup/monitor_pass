package com.evoupsight.monitorpass;

import com.evoupsight.monitorpass.server.dto.HostTemplateDto;
import com.google.gson.Gson;
import junit.framework.Test;
import junit.framework.TestCase;
import junit.framework.TestSuite;

import java.util.ArrayList;

/**
 * Unit test for simple App.
 */
public class AppTest 
    extends TestCase
{
    /**
     * Create the test case
     *
     * @param testName name of the test case
     */
    public AppTest( String testName )
    {
        super( testName );
    }

    /**
     * @return the suite of tests being tested
     */
    public static Test suite()
    {
        return new TestSuite( AppTest.class );
    }

    /**
     * Rigourous Test :-)
     */
    public void testApp()
    {
        assertTrue( true );
    }

    public void testDto() {
        HostTemplateDto hostTemplateDto = new HostTemplateDto();
        hostTemplateDto.setHost("server1");
        ArrayList<String> templateIds = new ArrayList<>();
        templateIds.add("10001");
        templateIds.add("10021");
        hostTemplateDto.setTemplateIds(templateIds);
        System.out.println(new Gson().toJson(hostTemplateDto));
    }
}
