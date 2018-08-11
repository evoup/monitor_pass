package com.evoupsight.monitorpass.datacollector.cfg;

import com.evoupsight.monitorpass.datacollector.services.JmxPollerService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.scheduling.annotation.EnableScheduling;
import org.springframework.scheduling.annotation.SchedulingConfigurer;
import org.springframework.scheduling.concurrent.ThreadPoolTaskScheduler;
import org.springframework.scheduling.config.ScheduledTaskRegistrar;

/**
 * @author evoup
 */
@Configuration
@EnableScheduling
public class JmxScheduleConfigurer implements SchedulingConfigurer
{
    @Autowired
    JmxPollerService jmxPollerService;

    @Bean()
    public ThreadPoolTaskScheduler taskSchedulerJmx() {
        ThreadPoolTaskScheduler threadPoolTaskScheduler = new ThreadPoolTaskScheduler();
        threadPoolTaskScheduler.setPoolSize(10);
        threadPoolTaskScheduler.initialize();
        return threadPoolTaskScheduler;
    }

    @Override
    public void configureTasks(ScheduledTaskRegistrar taskRegistrar)
    {
        taskRegistrar.setTaskScheduler(taskSchedulerJmx());
        taskRegistrar.addFixedDelayTask(() -> jmxPollerService.poll(), 10000);
    }
}