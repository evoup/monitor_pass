package com.evoupsight.monitorpass.dto;

import java.util.List;

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
    private List<String> templateIds;

    public String getHost() {
        return host;
    }

    public void setHost(String host) {
        this.host = host;
    }

    public List<String> getTemplateIds() {
        return templateIds;
    }

    public void setTemplateIds(List<String> templateIds) {
        this.templateIds = templateIds;
    }
}
