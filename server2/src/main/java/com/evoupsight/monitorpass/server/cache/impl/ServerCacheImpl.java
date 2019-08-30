package com.evoupsight.monitorpass.server.cache.impl;

import com.evoupsight.monitorpass.server.cache.ServerCache;
import com.evoupsight.monitorpass.server.dao.mapper.ServerMapper;
import com.evoupsight.monitorpass.server.dao.model.Server;
import com.evoupsight.monitorpass.server.dao.model.ServerExample;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Repository;

import java.util.List;

/**
 * @author evoup
 */
@Repository
public class ServerCacheImpl implements ServerCache {
    @Autowired
    ServerMapper serverMapper;

    @Override
    public List<Server> fetchAll() {
        ServerExample serverExample = new ServerExample();
        serverExample.createCriteria().andIdGreaterThan(0);
        return serverMapper.selectByExample(serverExample);
    }
}
