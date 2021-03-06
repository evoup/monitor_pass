package com.evoupsight.monitorpass.server.cache.impl;

import com.evoupsight.monitorpass.server.cache.ServerCache;
import com.evoupsight.monitorpass.server.constants.Constants;
import com.evoupsight.monitorpass.server.dao.mapper.RelationServerServerGroupMapper;
import com.evoupsight.monitorpass.server.dao.mapper.RelationTemplateServerGroupMapper;
import com.evoupsight.monitorpass.server.dao.mapper.ServerMapper;
import com.evoupsight.monitorpass.server.dao.mapper.TemplateMapper;
import com.evoupsight.monitorpass.server.dao.model.*;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.cache.annotation.Cacheable;
import org.springframework.stereotype.Repository;

import java.util.*;

import static com.evoupsight.monitorpass.server.constants.CacheConstants.CACHE_MANAGER_GUAVA;
import static com.evoupsight.monitorpass.server.constants.CacheConstants.CACHE_MANAGER_GUAVA_EVENT;

/**
 * @author evoup
 */
@SuppressWarnings("SpringJavaInjectionPointsAutowiringInspection")
@Repository
public class ServerCacheImpl implements ServerCache {
    private static final String CACHE_NAME = "com.evoupsight.monitorpass.server.cache.impl.ServerCacheImpl";
    private static final Logger LOG = LoggerFactory.getLogger(ServerCacheImpl.class);
    @Autowired
    ServerMapper serverMapper;
    @Autowired
    TemplateMapper templateMapper;
    @Autowired
    RelationTemplateServerGroupMapper relationTemplateServerGroupMapper;
    @Autowired
    RelationServerServerGroupMapper relationServerServerGroupMapper;

    @Override
    @Cacheable(value = CACHE_NAME, key = "#root.targetClass", unless = "#result == null", cacheManager = CACHE_MANAGER_GUAVA)
    public List<Server> fetchAll() {
        ServerExample serverExample = new ServerExample();
        serverExample.createCriteria().andIdGreaterThan(0);
        return serverMapper.selectByExample(serverExample);
    }

    @Override
    @Cacheable(value = CACHE_NAME, key = "#root.targetClass+'-'+ #id", unless = "#result == null",
            condition = "#id != null", cacheManager = CACHE_MANAGER_GUAVA)
    public List<Server> getByTemplate(Long id) {
        TemplateExample templateExample = new TemplateExample();
        templateExample.createCriteria().andIdEqualTo(id);
        List<Template> templates = templateMapper.selectByExample(templateExample);
        List<RelationTemplateServerGroup> relationTemplateServerGroups = new ArrayList<>();
        templates.stream().filter(Objects::nonNull).forEach(t -> {
            // 根据templates找对应的服务器组
            RelationTemplateServerGroupExample relationTemplateServerGroupExample = new RelationTemplateServerGroupExample();
            relationTemplateServerGroupExample.createCriteria().andIdEqualTo(t.getId().intValue());
            relationTemplateServerGroups.addAll(relationTemplateServerGroupMapper.selectByExample(relationTemplateServerGroupExample));
        });
        Set<Server> serverSets = new HashSet<>();
        // 根据服务器组找服务器
        relationTemplateServerGroups.stream().filter(Objects::nonNull).forEach(g -> {
            RelationServerServerGroupExample relationServerServerGroupExample = new RelationServerServerGroupExample();
            relationServerServerGroupExample.createCriteria().andServergroupIdEqualTo(g.getId());
            List<RelationServerServerGroup> relationServerServerGroups = relationServerServerGroupMapper.selectByExample(relationServerServerGroupExample);
            relationServerServerGroups.stream().filter(Objects::nonNull).forEach(r -> {
                Server server = serverMapper.selectByPrimaryKey(r.getServerId());
                Optional.ofNullable(server).ifPresent(s -> {
                    if (!new Integer(Constants.ServerStatus.NOT_MONITORING.ordinal()).equals(s.getId())) {
                        serverSets.add(s);
                    }
                });
            });
        });
        return new ArrayList<>(serverSets);
    }

    /**
     * 设置在线
     */
    @Override
    @Cacheable(value = CACHE_NAME, key = "#root.targetClass+'-host_on-'+ #hostId", unless = "#result == null",
            condition = "#hostId != null", cacheManager = CACHE_MANAGER_GUAVA_EVENT)
    public String makeUp(Integer hostId) {
        Server server = new Server();
        server.setId(hostId);
        server.setStatus(Constants.ServerStatus.ON.ordinal());
        serverMapper.updateByPrimaryKeySelective(server);
        return "ok";
    }

    /**
     * 设置宕机
     */
    @Override
    @Cacheable(value = CACHE_NAME, key = "#root.targetClass+'-host_down-'+ #hostId", unless = "#result == null",
            condition = "#hostId != null", cacheManager = CACHE_MANAGER_GUAVA_EVENT)
    public String makeDown(Integer hostId) {
        Server record = serverMapper.selectByPrimaryKey(hostId);
        if (record != null) {
            if (System.currentTimeMillis() - record.getLastOnline().getTime() > 300000) {
                Server server = new Server();
                server.setId(hostId);
                server.setStatus(Constants.ServerStatus.DOWN.ordinal());
                serverMapper.updateByPrimaryKeySelective(server);
            }
        }
        return "ok";
    }
}
