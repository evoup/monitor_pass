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
        RelationServerServerGroupExample relationServerServerGroupExample = new RelationServerServerGroupExample();
        relationServerServerGroupExample.createCriteria().andServerIdEqualTo(server.getId());
        List<RelationServerServerGroup> relationServerServerGroups = relationServerServerGroupMapper.selectByExample(relationServerServerGroupExample);
        if (CollectionUtils.isNotEmpty(relationServerServerGroups)) {
            RelationServerServerGroup relationServerServerGroup = relationServerServerGroups.get(0);
            Integer servergroupId = relationServerServerGroup.getServergroupId();
            if (servergroupId != null) {
                ServerGroupExample serverGroupExample = new ServerGroupExample();
                serverGroupExample.createCriteria().andIdEqualTo(servergroupId);
                List<ServerGroup> serverGroups = serverGroupMapper.selectByExample(serverGroupExample);
                if (CollectionUtils.isNotEmpty(serverGroups)) {
                    RelationTemplateServerGroupExample relationTemplateServerGroupExample = new RelationTemplateServerGroupExample();
                    relationTemplateServerGroupExample.createCriteria().andServergroupIdEqualTo(serverGroups.get(0).getId());
                    List<RelationTemplateServerGroup> relationTemplateServerGroups = relationTemplateServerGroupMapper.selectByExample(relationTemplateServerGroupExample);
                    List<Integer> templateIds = relationTemplateServerGroups.stream().filter(Objects::nonNull).map(r -> r.getTemplateId().intValue()).collect(Collectors.toList());
                    if (CollectionUtils.isNotEmpty(templateIds)) {
                        MonitorItemExample monitorItemExample = new MonitorItemExample();
                        monitorItemExample.createCriteria().andTemplateIdIn(templateIds);
                        return monitorItemMapper.selectByExample(monitorItemExample);
                    }
                }
            }
        }
        return null;
    }
}
