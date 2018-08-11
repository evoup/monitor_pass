package com.evoupsight.monitorpass.datacollector.cfg;

import com.evoupsight.monitorpass.datacollector.services.SnmpPollerService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.scheduling.annotation.EnableScheduling;
import org.springframework.scheduling.annotation.SchedulingConfigurer;
import org.springframework.scheduling.concurrent.ThreadPoolTaskScheduler;
import org.springframework.scheduling.config.ScheduledTaskRegistrar;

import java.io.IOException;

/**
 * @author evoup
 */
@Configuration
@EnableScheduling
public class SnmpScheduleConfigurer implements SchedulingConfigurer {
    @Autowired
    private SnmpPollerService snmpPollerService;

    @Bean()
    public ThreadPoolTaskScheduler taskSchedulerSnmp() {
        ThreadPoolTaskScheduler threadPoolTaskScheduler = new ThreadPoolTaskScheduler();
        threadPoolTaskScheduler.setPoolSize(10);
        threadPoolTaskScheduler.initialize();
        return threadPoolTaskScheduler;
    }

    @Override
    public void configureTasks(ScheduledTaskRegistrar taskRegistrar) {
        taskRegistrar.setTaskScheduler(taskSchedulerSnmp());
        taskRegistrar.addFixedDelayTask(() -> {
            try {
                snmpPollerService.poll();
            } catch (IOException e) {
                e.printStackTrace();
            }
        }, 10000);
    }

}
