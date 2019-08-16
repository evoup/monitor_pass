package com.evoupsight.monitorpass.datacollector.services;

import com.evoupsight.monitorpass.datacollector.dao.model.Server;

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
}
