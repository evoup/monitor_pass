package com.evoupsight.monitorpass.server.dao.impl;

import com.evoupsight.monitorpass.server.dao.TriggerDao;
import com.evoupsight.monitorpass.server.dao.mapper.TriggerMapper;
import com.evoupsight.monitorpass.server.dao.model.Trigger;
import com.evoupsight.monitorpass.server.dao.model.TriggerExample;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Repository;

import java.util.List;

/**
 * @author evoup
 */
@Repository
public class TriggerDaoImpl implements TriggerDao {
    @Autowired
    TriggerMapper triggerMapper;
    @Override
    public List<Trigger> fetchAll() {
        TriggerExample example = new TriggerExample();
        example.createCriteria().andIdGreaterThan(0L);
        return triggerMapper.selectByExample(example);
    }
}
