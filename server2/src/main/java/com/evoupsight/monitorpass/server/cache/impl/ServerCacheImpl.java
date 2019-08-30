package com.evoupsight.monitorpass.server.cache.impl;

import com.evoupsight.monitorpass.server.cache.ServerCache;
import com.evoupsight.monitorpass.server.dao.mapper.ServerMapper;
import com.evoupsight.monitorpass.server.dao.model.Server;
import com.evoupsight.monitorpass.server.dao.model.ServerExample;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.cache.annotation.Cacheable;
import org.springframework.stereotype.Repository;

import java.util.List;

import static com.evoupsight.monitorpass.server.constants.CacheConstants.CACHE_MANAGER_GUAVA;

/**
 * @author evoup
 */
@Repository
public class ServerCacheImpl implements ServerCache {
    private static final String CACHE_NAME = "com.evoupsight.monitorpass.server.cache.impl.ServerCacheImpl";
    @Autowired
    ServerMapper serverMapper;

    @Override
    @Cacheable(value = CACHE_NAME, key = "#root.targetClass", unless = "#result == null", cacheManager = CACHE_MANAGER_GUAVA)
    public List<Server> fetchAll() {
        ServerExample serverExample = new ServerExample();
        serverExample.createCriteria().andIdGreaterThan(0);
        return serverMapper.selectByExample(serverExample);
    }
}
