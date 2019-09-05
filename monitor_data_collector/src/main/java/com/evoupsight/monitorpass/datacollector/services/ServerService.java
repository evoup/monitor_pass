package com.evoupsight.monitorpass.datacollector.services;

import com.evoupsight.monitorpass.datacollector.dao.model.Server;

import java.util.List;

/**
 * @author evoup
 */
public interface ServerService {
    /**
     * 找到指定Server对象
     *
     * @param serverName 服务器名
     * @return Server
     */
    Server findServer(String serverName);

    /**
     * 返回所有被监控的主机
     * @return List<Server>
     */
    List<Server> fetchAllMonitoringServer();

    /**
     * 通知被监控主机的配置需要被更新
     * @param name
     */
    void notifyServerNeedConfig(String name);
}
