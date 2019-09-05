package com.evoupsight.monitorpass.datacollector.services.impl;

import com.evoupsight.monitorpass.datacollector.constants.ServerStatusEnum;
import com.evoupsight.monitorpass.datacollector.dao.mapper.ServerMapper;
import com.evoupsight.monitorpass.datacollector.dao.model.Server;
import com.evoupsight.monitorpass.datacollector.dao.model.ServerExample;
import com.evoupsight.monitorpass.datacollector.services.ServerService;
import org.apache.commons.collections.CollectionUtils;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import java.util.List;

/**
 * @author evoup
 */
@SuppressWarnings("SpringJavaAutowiredMembersInspection")
@Service
public class ServerServiceImpl implements ServerService {
    private static final Logger LOG = LoggerFactory.getLogger(ServerServiceImpl.class);
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

    @Override
    public List<Server> fetchAllMonitoringServer() {
        ServerExample example = new ServerExample();
        example.createCriteria().andStatusNotEqualTo(ServerStatusEnum.UNMONTORING.ordinal());
        return serverMapper.selectByExample(example);
    }

    @Override
    public void notifyServerNeedConfig(String name, boolean configUpdated) {
        try {
            ServerExample example = new ServerExample();
            example.createCriteria().andNameEqualTo(name);
            Server server = new Server();
            server.setConfigUpdated(configUpdated);
            serverMapper.updateByExampleSelective(server, example);
        } catch (Exception e) {
            LOG.error(e.getMessage(), e);
        }
    }
}
