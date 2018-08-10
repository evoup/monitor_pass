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
public class ScheduleConfigurer implements SchedulingConfigurer
{
    @Autowired
    JmxPollerService jmxPollerService;

    @Bean()
    public ThreadPoolTaskScheduler taskScheduler() {
        return new ThreadPoolTaskScheduler();
    }

    @Override
    public void configureTasks(ScheduledTaskRegistrar taskRegistrar)
    {
        taskRegistrar.setTaskScheduler(taskScheduler());
        taskRegistrar.addFixedDelayTask(new Runnable()
        {
            @Override
            public void run()
            {
                jmxPollerService.poll();
            }
        }, 10000);
    }
}