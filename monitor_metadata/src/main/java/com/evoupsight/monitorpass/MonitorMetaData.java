package com.evoupsight.monitorpass;

import org.springframework.boot.Banner;
import org.springframework.boot.CommandLineRunner;
import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.EnableAutoConfiguration;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.boot.autoconfigure.security.reactive.ReactiveSecurityAutoConfiguration;
import org.springframework.boot.autoconfigure.web.embedded.EmbeddedWebServerFactoryCustomizerAutoConfiguration;
import org.springframework.boot.autoconfigure.web.reactive.error.ErrorWebFluxAutoConfiguration;
import org.springframework.boot.autoconfigure.web.servlet.error.ErrorMvcAutoConfiguration;


/**
 * @author evoup
 */
@SpringBootApplication(scanBasePackages="com.evoupsight.monitorpass")
@EnableAutoConfiguration(exclude = {ErrorMvcAutoConfiguration.class, ErrorWebFluxAutoConfiguration.class,
        EmbeddedWebServerFactoryCustomizerAutoConfiguration.class, ReactiveSecurityAutoConfiguration.class})
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
