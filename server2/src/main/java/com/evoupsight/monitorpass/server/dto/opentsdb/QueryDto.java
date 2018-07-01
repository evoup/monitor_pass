package com.evoupsight.monitorpass.server.dto.opentsdb;

import java.util.HashMap;

public class QueryDto {
    private String metric;
    private HashMap<String, Object> tags;
    private HashMap<String, Object> dps;

    public String getMetric() {
        return metric;
    }

    public void setMetric(String metric) {
        this.metric = metric;
    }

    public HashMap<String, Object> getTags() {
        return tags;
    }

    public void setTags(HashMap<String, Object> tags) {
        this.tags = tags;
    }

    public HashMap<String, Object> getDps() {
        return dps;
    }

    public void setDps(HashMap<String, Object> dps) {
        this.dps = dps;
    }
}
