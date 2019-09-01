package com.evoupsight.monitorpass.server.cache.impl;

import com.evoupsight.monitorpass.server.cache.ItemCache;
import com.evoupsight.monitorpass.server.dao.mapper.ItemMapper;
import com.evoupsight.monitorpass.server.dao.model.Item;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.cache.annotation.Cacheable;

import static com.evoupsight.monitorpass.server.constants.CacheConstants.CACHE_MANAGER_GUAVA;

/**
 * @author evoup
 */
@SuppressWarnings("SpringJavaAutowiredMembersInspection")
public class ItemCacheImpl implements ItemCache {
    private static final String CACHE_NAME = "com.evoupsight.monitorpass.server.cache.impl.ItemCacheImpl";
    @Autowired
    ItemMapper itemMapper;

    @Override
    @Cacheable(value = CACHE_NAME, key = "#root.targetClass+'-'+ #id", unless = "#result == null",
        condition = "#id != null", cacheManager = CACHE_MANAGER_GUAVA)
    public Item get(Integer id) {
        return itemMapper.selectByPrimaryKey(id);
    }
}
