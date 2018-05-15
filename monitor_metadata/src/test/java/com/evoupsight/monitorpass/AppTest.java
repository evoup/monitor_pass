package com.evoupsight.monitorpass;

import static org.junit.Assert.assertTrue;

import org.junit.Test;

import java.io.IOException;

/**
 * Unit test for simple MonitorMetaData.
 */
public class AppTest 
{
    /**
     * Rigorous Test :-)
     */
    @Test
    public void shouldAnswerWithTrue() throws IOException {
        //assertTrue( true);
        new QueryInfo().getRow();
    }
}
