package com.evoupsight.monitorpass.server.dao.impl;

import com.evoupsight.monitorpass.server.dao.ServerDao;
import com.evoupsight.monitorpass.server.dao.mapper.ServerMapper;
import com.evoupsight.monitorpass.server.dao.model.Server;
import com.evoupsight.monitorpass.server.dao.model.ServerExample;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Repository;

import java.util.List;

@Repository
public class ServerDaoImpl implements ServerDao {
    @Autowired
    ServerMapper serverMapper;

    @Override
    public List<Server> fetchAll() {
        ServerExample serverExample = new ServerExample();
        serverExample.createCriteria().andIdGreaterThan(0);
        return serverMapper.selectByExample(serverExample);
    }
}
