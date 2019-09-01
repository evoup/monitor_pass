package com.evoupsight.monitorpass.server.cache;

import com.evoupsight.monitorpass.server.dao.model.Item;

/**
 * @author evoup
 */
public interface ItemCache {
    Item get(Integer id);
}
