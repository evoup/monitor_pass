package com.evoupsight.monitorpass.server.cache;

import com.evoupsight.monitorpass.server.dao.model.Server;

import java.util.List;

/**
 * @author evoup
 */
public interface ServerCache {
    /**
     * 获取所有Server
     *
     * @return List<Server>
     */
    List<Server> fetchAll();
}
