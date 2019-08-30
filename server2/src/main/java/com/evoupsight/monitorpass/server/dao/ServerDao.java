package com.evoupsight.monitorpass.server.dao;

import com.evoupsight.monitorpass.server.dao.model.Server;

import java.util.List;

public interface ServerDao {
    List<Server> fetchAll();
}
