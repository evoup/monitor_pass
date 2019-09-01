package com.evoupsight.monitorpass.server.cache.impl;

import com.evoupsight.monitorpass.server.cache.FunctionCache;
import com.evoupsight.monitorpass.server.dao.mapper.FunctionMapper;
import com.evoupsight.monitorpass.server.dao.model.Function;
import com.evoupsight.monitorpass.server.dao.model.FunctionExample;
import org.apache.commons.collections.CollectionUtils;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.cache.annotation.Cacheable;
import org.springframework.stereotype.Repository;

import java.util.List;

import static com.evoupsight.monitorpass.server.constants.CacheConstants.CACHE_MANAGER_GUAVA;

/**
 * @author evoup
 */
@SuppressWarnings("SpringJavaInjectionPointsAutowiringInspection")
@Repository
public class FunctionCacheImpl implements FunctionCache {
    private static final String CACHE_NAME = "com.evoupsight.monitorpass.server.cache.impl.FunctionCacheImpl";
    @Autowired
    FunctionMapper functionMapper;

    @Override
    @Cacheable(value = CACHE_NAME, key = "#root.targetClass+'-'+ #id", unless = "#result == null",
        condition = "#id != null", cacheManager = CACHE_MANAGER_GUAVA)
    public Function get(Long id) {
        FunctionExample functionExample = new FunctionExample();
        functionExample.createCriteria().andIdEqualTo(id);
        List<Function> functions = functionMapper.selectByExample(functionExample);
        if (CollectionUtils.isNotEmpty(functions)) {
            return functionMapper.selectByExample(functionExample).get(0);
        }
        return null;
    }
}
