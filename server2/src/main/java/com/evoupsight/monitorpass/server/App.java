package com.evoupsight.monitorpass.server;

import org.mybatis.spring.annotation.MapperScan;
import org.springframework.boot.Banner;
import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.scheduling.annotation.EnableScheduling;

/**
 * @author evoup
 */
@EnableScheduling
@SpringBootApplication(scanBasePackages="com.evoupsight.monitorpass.server")
@MapperScan("com.evoupsight.monitorpass.server.dao.mapper")
public class App {

    public static void main(String[] args) {
        SpringApplication app = new SpringApplication(App.class);
        app.setBannerMode(Banner.Mode.OFF);
        app.run(args);
    }
}