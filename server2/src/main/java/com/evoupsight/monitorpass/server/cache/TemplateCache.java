package com.evoupsight.monitorpass.server.cache;

import com.evoupsight.monitorpass.server.dao.model.Template;

import java.util.List;

/**
 * @author evoup
 */
public interface TemplateCache {
    /**
     * 获取所有模板
     *
     * @return List<Template>
     */
    List<Template> fetchAll();
}
