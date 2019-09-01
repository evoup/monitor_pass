package com.evoupsight.monitorpass.server.cache;

import com.evoupsight.monitorpass.server.dao.model.Trigger;

import java.util.List;

/**
 * @author evoup
 */
public interface TriggerCache {
    /**
     * 获取全部触发器
     * @return List<Trigger>
     */
    List<Trigger> fetchAll();

    /**
     * 根据templateId返回triggers
     * @param templateId
     * @return
     */
    List<Trigger> getByTemplate(Long templateId);
}
