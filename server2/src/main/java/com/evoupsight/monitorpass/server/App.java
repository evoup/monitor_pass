package com.evoupsight.monitorpass.server;

import org.mybatis.spring.annotation.MapperScan;
import org.springframework.boot.Banner;
import org.springframework.boot.WebApplicationType;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.boot.builder.SpringApplicationBuilder;
import org.springframework.scheduling.annotation.EnableScheduling;

/**
 * @author evoup
 */
@EnableScheduling
@SpringBootApplication(scanBasePackages = "com.evoupsight.monitorpass.server")
@MapperScan("com.evoupsight.monitorpass.server.dao.mapper")
public class App {

    public static void main(String[] args) {
        new SpringApplicationBuilder(App.class).bannerMode(Banner.Mode.OFF)
                .web(WebApplicationType.NONE)
                .run(args);
    }
}