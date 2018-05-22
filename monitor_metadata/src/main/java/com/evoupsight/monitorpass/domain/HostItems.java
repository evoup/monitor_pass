package com.evoupsight.monitorpass.domain;

import java.util.List;

/**
 * @author evoup
 */
public class HostItems {
    private String host;
    private List<Item> items;

    public String getHost() {
        return host;
    }

    public void setHost(String host) {
        this.host = host;
    }

    public List<Item> getItems() {
        return items;
    }

    public void setItems(List<Item> items) {
        this.items = items;
    }
}
