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
//@ComponentScan("com.evoupsight")
@PropertySources({
        @PropertySource("redis.properties")
})
@EnableScheduling
public class Config {
    /**
     * Necessary to make the Value annotations work.
     * 这个自动配置掉了
     * @return
     */
//    @Bean
//    public static PropertySourcesPlaceholderConfigurer propertyPlaceholderConfigurer() {
//        return new PropertySourcesPlaceholderConfigurer();
//    }

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
