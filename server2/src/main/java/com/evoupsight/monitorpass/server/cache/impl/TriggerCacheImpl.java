package com.evoupsight.monitorpass.server.cache.impl;

import com.evoupsight.monitorpass.server.cache.TriggerCache;
import com.evoupsight.monitorpass.server.dao.mapper.TriggerMapper;
import com.evoupsight.monitorpass.server.dao.model.Trigger;
import com.evoupsight.monitorpass.server.dao.model.TriggerExample;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.cache.annotation.Cacheable;
import org.springframework.stereotype.Repository;

import java.util.List;

import static com.evoupsight.monitorpass.server.constants.CacheConstants.CACHE_MANAGER_GUAVA;

/**
 * @author evoup
 */
@Repository
public class TriggerCacheImpl implements TriggerCache {
    private static final String CACHE_NAME = "com.evoupsight.monitorpass.server.cache.impl.TriggerCacheImpl";
    @Autowired
    TriggerMapper triggerMapper;

    @Override
    @Cacheable(value = CACHE_NAME, key = "#root.targetClass", unless = "#result == null", cacheManager = CACHE_MANAGER_GUAVA)
    public List<Trigger> fetchAll() {
        TriggerExample example = new TriggerExample();
        example.createCriteria().andIdGreaterThan(0L);
        return triggerMapper.selectByExample(example);
    }
}
