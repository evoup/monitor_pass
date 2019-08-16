package com.evoupsight.monitorpass.datacollector.manager.impl;

import com.evoupsight.monitorpass.datacollector.dao.mapper.DataCollectorMapper;
import com.evoupsight.monitorpass.datacollector.dao.model.DataCollector;
import com.evoupsight.monitorpass.datacollector.dao.model.DataCollectorExample;
import com.evoupsight.monitorpass.datacollector.manager.DataCollectorCache;
import org.apache.commons.collections.CollectionUtils;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Component;

import java.util.List;

/**
 * @author evoup
 */
@SuppressWarnings("SpringJavaAutowiredMembersInspection")
@Component
public class DataCollectorCacheImpl implements DataCollectorCache {
    @Autowired
    private DataCollectorMapper dataCollectorMapper;

    @Override
    public DataCollector findDataCollector(String dataCollectorServerName) {
        DataCollectorExample example = new DataCollectorExample();
        example.createCriteria().andNameEqualTo(dataCollectorServerName);
        List<DataCollector> dataCollectors = dataCollectorMapper.selectByExample(example);
        if (CollectionUtils.isNotEmpty(dataCollectors)) {
            return dataCollectors.get(0);
        }
        return null;
    }
}
