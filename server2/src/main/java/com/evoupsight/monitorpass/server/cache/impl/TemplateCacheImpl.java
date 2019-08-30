package com.evoupsight.monitorpass.server.cache.impl;

import com.evoupsight.monitorpass.server.cache.TemplateCache;
import com.evoupsight.monitorpass.server.dao.mapper.TemplateMapper;
import com.evoupsight.monitorpass.server.dao.model.Template;
import com.evoupsight.monitorpass.server.dao.model.TemplateExample;
import org.springframework.beans.factory.annotation.Autowired;

import java.util.List;

/**
 * @author evoup
 */
public class TemplateCacheImpl implements TemplateCache {
    @Autowired
    private TemplateMapper templateMapper;

    @Override
    public List<Template> fetchAll() {
        TemplateExample templateExample = new TemplateExample();
        templateExample.createCriteria().andIdGreaterThan(0L);
        return templateMapper.selectByExample(templateExample);
    }
}