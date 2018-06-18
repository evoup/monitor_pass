package com.evoupsight.monitorpass.server.cfg;


import org.apache.hadoop.fs.Path;
import org.apache.hadoop.hbase.HBaseConfiguration;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.context.annotation.PropertySource;
import org.springframework.context.annotation.PropertySources;

import java.net.URL;

/**
 * @author evoup
 */
@Configuration
@PropertySources({
        @PropertySource("server.properties")
})
public class Config {

    @Bean
    public org.apache.hadoop.conf.Configuration hbaseConf() {
        org.apache.hadoop.conf.Configuration config = HBaseConfiguration.create();
        ClassLoader classLoader = this.getClass().getClassLoader();
        URL resource = classLoader.getResource("hbase-site.xml");
        if (resource != null) {
            String path = resource.getPath();
            config.addResource(new Path(path));
            return config;
        }
        return null;
    }
}
