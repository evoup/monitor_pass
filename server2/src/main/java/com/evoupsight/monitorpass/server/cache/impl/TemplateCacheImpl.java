package com.evoupsight.monitorpass.server.cache.impl;

import com.evoupsight.monitorpass.server.cache.TemplateCache;
import com.evoupsight.monitorpass.server.dao.mapper.TemplateMapper;
import com.evoupsight.monitorpass.server.dao.model.Template;
import com.evoupsight.monitorpass.server.dao.model.TemplateExample;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.cache.annotation.Cacheable;

import java.util.List;

import static com.evoupsight.monitorpass.server.constants.CacheConstants.CACHE_MANAGER_GUAVA;

/**
 * @author evoup
 */
public class TemplateCacheImpl implements TemplateCache {
    private static final String CACHE_NAME = "com.evoupsight.monitorpass.server.cache.impl.TemplateCacheImpl";
    @Autowired
    private TemplateMapper templateMapper;

    @Override
    @Cacheable(value = CACHE_NAME, key = "#root.targetClass", unless = "#result == null", cacheManager = CACHE_MANAGER_GUAVA)
    public List<Template> fetchAll() {
        TemplateExample templateExample = new TemplateExample();
        templateExample.createCriteria().andIdGreaterThan(0L);
        return templateMapper.selectByExample(templateExample);
    }
}
