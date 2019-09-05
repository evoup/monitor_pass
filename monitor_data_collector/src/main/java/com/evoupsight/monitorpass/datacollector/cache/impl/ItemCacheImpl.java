package com.evoupsight.monitorpass.datacollector.cache.impl;

import com.evoupsight.monitorpass.datacollector.cache.ItemCache;
import com.evoupsight.monitorpass.datacollector.dao.mapper.MonitorItemMapper;
import com.evoupsight.monitorpass.datacollector.dao.mapper.RelationServerServerGroupMapper;
import com.evoupsight.monitorpass.datacollector.dao.mapper.RelationTemplateServerGroupMapper;
import com.evoupsight.monitorpass.datacollector.dao.mapper.ServerGroupMapper;
import com.evoupsight.monitorpass.datacollector.dao.model.*;
import org.apache.commons.collections.CollectionUtils;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Component;

import java.util.ArrayList;
import java.util.List;
import java.util.Objects;
import java.util.stream.Collectors;

/**
 * @author evoup
 */
@Component
public class ItemCacheImpl implements ItemCache {
    @Autowired
    RelationServerServerGroupMapper relationServerServerGroupMapper;
    @Autowired
    ServerGroupMapper serverGroupMapper;
    @Autowired
    RelationTemplateServerGroupMapper relationTemplateServerGroupMapper;
    @Autowired
    MonitorItemMapper monitorItemMapper;

    @Override
    public List<MonitorItem> findMonitorItems(Server server) {
        // server -> server group -> template -> item
        List<MonitorItem> ret = new ArrayList<>();
        RelationServerServerGroupExample relationServerServerGroupExample = new RelationServerServerGroupExample();
        relationServerServerGroupExample.createCriteria().andServerIdEqualTo(server.getId());
        List<RelationServerServerGroup> relationServerServerGroups = relationServerServerGroupMapper.selectByExample(relationServerServerGroupExample);
        if (CollectionUtils.isNotEmpty(relationServerServerGroups)) {
            relationServerServerGroups.stream().filter(Objects::nonNull).forEach(r0 -> {
                Integer servergroupId = r0.getServergroupId();
                if (servergroupId != null) {
                    ServerGroup serverGroup = serverGroupMapper.selectByPrimaryKey(servergroupId);
                    if (serverGroup != null) {
                        RelationTemplateServerGroupExample relationTemplateServerGroupExample = new RelationTemplateServerGroupExample();
                        relationTemplateServerGroupExample.createCriteria().andServergroupIdEqualTo(servergroupId);
                        List<RelationTemplateServerGroup> relationTemplateServerGroups = relationTemplateServerGroupMapper.selectByExample(relationTemplateServerGroupExample);
                        List<Integer> templateIds = relationTemplateServerGroups.stream().filter(Objects::nonNull).map(r1 -> r1.getTemplateId().intValue()).collect(Collectors.toList());
                        if (CollectionUtils.isNotEmpty(templateIds)) {
                            MonitorItemExample monitorItemExample = new MonitorItemExample();
                            monitorItemExample.createCriteria().andTemplateIdIn(templateIds);
                            ret.addAll(monitorItemMapper.selectByExample(monitorItemExample));
                        }
                    }
                }
            });
        }
        return ret;
    }
}
