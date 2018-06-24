package com.evoupsight.monitorpass.cfg;

import org.apache.hadoop.fs.Path;
import org.apache.hadoop.hbase.HBaseConfiguration;
import org.springframework.context.annotation.*;
import org.springframework.scheduling.annotation.EnableScheduling;

import java.net.URL;


/**
 * @author evoup
 */
@Configuration
@PropertySources({
        @PropertySource("redis.properties")
})
@EnableScheduling
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
