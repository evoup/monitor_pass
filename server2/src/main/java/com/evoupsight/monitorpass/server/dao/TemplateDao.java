package com.evoupsight.monitorpass.server.dao;

import com.evoupsight.monitorpass.server.dao.model.Template;

import java.util.List;

/**
 * @author evoup
 */
public interface TemplateDao {
    /**
     * 获取所有模板
     *
     * @return List<Template>
     */
    List<Template> fetchAll();
}
