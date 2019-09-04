package com.evoupsight.monitorpass.datacollector.cfg;

import com.evoupsight.monitorpass.datacollector.services.impl.MonitorItemConfigPollerServiceImpl;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.context.annotation.Bean;
import org.springframework.scheduling.annotation.SchedulingConfigurer;
import org.springframework.scheduling.concurrent.ThreadPoolTaskScheduler;
import org.springframework.scheduling.config.ScheduledTaskRegistrar;

/**
 * @author evoup
 */
public class MonitorItemConfigScheduleConfigurer implements SchedulingConfigurer {
    @Autowired
    private MonitorItemConfigPollerServiceImpl monitorItemConfigPollerServiceImpl;
    @Bean()
    public ThreadPoolTaskScheduler taskSchedulerSnmp() {
        ThreadPoolTaskScheduler threadPoolTaskScheduler = new ThreadPoolTaskScheduler();
        threadPoolTaskScheduler.setPoolSize(1);
        threadPoolTaskScheduler.initialize();
        return threadPoolTaskScheduler;
    }

    @Override
    public void configureTasks(ScheduledTaskRegistrar taskRegistrar) {
        taskRegistrar.setTaskScheduler(taskSchedulerSnmp());
        taskRegistrar.addFixedDelayTask(() -> monitorItemConfigPollerServiceImpl.poll(), 10000);
    }
}
