package com.evoupsight.monitorpass.server.dto.memcache;

import lombok.Getter;
import lombok.Setter;

/**
 * @author evoup
 */
@Getter
@Setter
public class TriggerDto {
    private String triggerId;
    private String expression;
    private String description;
    private String url;
    private String status;
    private String value;
    private String priority;
    private String lastChange;
    private String comments;
    private String error;
    private String templateId;
    private String type;
    private String state;
    private String flags;
    private String hostId;
}
