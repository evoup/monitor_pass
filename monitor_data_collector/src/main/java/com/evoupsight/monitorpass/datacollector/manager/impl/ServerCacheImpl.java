package com.evoupsight.monitorpass.datacollector.manager.impl;

import com.evoupsight.monitorpass.datacollector.dao.mapper.ServerMapper;
import com.evoupsight.monitorpass.datacollector.dao.model.Server;
import com.evoupsight.monitorpass.datacollector.dao.model.ServerExample;
import com.evoupsight.monitorpass.datacollector.manager.ServerCache;
import org.apache.commons.collections.CollectionUtils;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Component;

import java.util.List;

/**
 * @author evoup
 */
@SuppressWarnings("SpringJavaAutowiredMembersInspection")
@Component
public class ServerCacheImpl implements ServerCache {
    @Autowired
    private ServerMapper serverMapper;

    @Override
    public Server findServer(String serverName) {
        ServerExample example = new ServerExample();
        example.createCriteria().andHostnameEqualTo(serverName);
        List<Server> servers = serverMapper.selectByExample(example);
        if (CollectionUtils.isNotEmpty(servers)) {
            return servers.get(0);
        }
        return null;
    }
}
