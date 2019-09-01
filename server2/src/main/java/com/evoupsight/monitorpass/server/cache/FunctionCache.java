package com.evoupsight.monitorpass.server.cache;

import com.evoupsight.monitorpass.server.dao.model.Function;


/**
 * @author evoup
 */
public interface FunctionCache {
    /**
     * @param id
     * @return
     */
    Function get(Long id);
}
