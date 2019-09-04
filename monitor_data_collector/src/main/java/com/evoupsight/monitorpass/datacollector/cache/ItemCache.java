package com.evoupsight.monitorpass.datacollector.cache;

import com.evoupsight.monitorpass.datacollector.dao.model.MonitorItem;
import com.evoupsight.monitorpass.datacollector.dao.model.Server;

import java.util.List;

/**
 * @author evoup
 */
public interface ItemCache {
    /**
     * 找到服务器所有的监控项
     * @param server
     * @return
     */
    List<MonitorItem> findMonitorItems(Server server);
}
