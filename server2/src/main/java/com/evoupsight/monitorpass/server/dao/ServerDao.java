package com.evoupsight.monitorpass.server.dao;

import com.evoupsight.monitorpass.server.dao.model.Server;

import java.util.List;

/**
 * @author evoup
 */
public interface ServerDao {
    /**
     * 获取所有Server
     *
     * @return List<Server>
     */
    List<Server> fetchAll();
}
