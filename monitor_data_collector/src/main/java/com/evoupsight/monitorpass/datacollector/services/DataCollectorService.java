package com.evoupsight.monitorpass.datacollector.services;

import com.evoupsight.monitorpass.datacollector.dao.model.DataCollector;

/**
 * @author evoup
 */
public interface DataCollectorService {
    /**
     * 根据收集收集器的IP，返回DataCollector
     *
     * @param dataCollectorServerName 数据收集器的ID，如DC1
     * @return
     */
    DataCollector findDataCollector(String dataCollectorServerName);
}
