package com.evoupsight.monitorpass.datacollector.manager;

import com.evoupsight.monitorpass.datacollector.dao.model.Server;

/**
 * @author evoup
 */
public interface ServerCache {
    /**
     * 找到指定Server对象
     *
     * @param serverName 服务器名
     * @return Server
     */
    Server findServer(String serverName);
}
