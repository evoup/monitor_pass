package com.evoupsight.monitorpass;

import org.springframework.boot.Banner;
import org.springframework.boot.CommandLineRunner;
import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;



/**
 * @author evoup
 */
@SpringBootApplication(scanBasePackages="com.evoupsight.monitorpass")
//@EnableAutoConfiguration(exclude = {ErrorMvcAutoConfiguration.class, ErrorWebFluxAutoConfiguration.class,
//        EmbeddedWebServerFactoryCustomizerAutoConfiguration.class, ReactiveSecurityAutoConfiguration.class})
public class MonitorMetaData implements CommandLineRunner {
    public static void main(String[] args) {
        SpringApplication app = new SpringApplication(MonitorMetaData.class);
        app.setBannerMode(Banner.Mode.OFF);
        app.run(args);
    }

    @Override
    public void run(String... args) {
        // noop
    }
}
