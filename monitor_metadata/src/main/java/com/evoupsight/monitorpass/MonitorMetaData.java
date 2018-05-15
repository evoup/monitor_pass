package com.evoupsight.monitorpass;

import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.fs.Path;
import org.apache.hadoop.hbase.HBaseConfiguration;
import org.apache.hadoop.hbase.TableName;
import org.apache.hadoop.hbase.client.Get;
import org.apache.hadoop.hbase.util.Bytes;

import java.net.URL;

/**
 * Hello world!
 *
 */
public class MonitorMetaData
{
    public static void main( String[] args )
    {
        // System.out.println( "Hello World!" );
        QueryInfo queryInfo = new QueryInfo();
        queryInfo.getRow();
    }


}
