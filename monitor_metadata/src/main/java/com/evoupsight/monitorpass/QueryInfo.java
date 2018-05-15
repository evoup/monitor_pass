package com.evoupsight.monitorpass;

import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.fs.Path;
import org.apache.hadoop.hbase.HBaseConfiguration;
import org.apache.hadoop.hbase.TableName;
import org.apache.hadoop.hbase.client.Get;
import org.apache.hadoop.hbase.util.Bytes;

import java.net.URL;

public class QueryInfo {

    public void getRow() {
        Configuration config = HBaseConfiguration.create();
        ClassLoader classLoader = this.getClass().getClassLoader();
        // URL resource = classLoader.getResource("hbase-site.xml");
        URL resource = classLoader.getResource("/etc/hbase/conf/hbase-site.xml");
        if (resource != null) {
            String path = resource.getPath();
            config.addResource(new Path(path));
            byte[] row1 = Bytes.toBytes("23219");
            TableName table1 = TableName.valueOf("monitor_items");
            String family1 = "info";
            Get g = new Get(row1);
            System.out.println(table1.getName());
        }
    }
}
