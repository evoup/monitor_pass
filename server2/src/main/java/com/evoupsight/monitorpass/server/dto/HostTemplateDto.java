package com.evoupsight.monitorpass.server.dto;

import java.util.ArrayList;

/**
 * @author evoup
 */
public class HostTemplateDto {

    /**
     * 主机
     */
    private String host;

    /**
     * 模板ids
     */
    private ArrayList<String> templateIds;

    public String getHost() {
        return host;
    }

    public void setHost(String host) {
        this.host = host;
    }

    public ArrayList<String> getTemplateIds() {
        return templateIds;
    }

    public void setTemplateIds(ArrayList<String> templateIds) {
        this.templateIds = templateIds;
    }
}
