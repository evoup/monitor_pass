package com.evoupsight.monitorpass.server.dto.memcache;

import lombok.Getter;
import lombok.Setter;

import java.util.List;

/**
 * @author evoup
 */
@Getter
@Setter
public class HostTemplateDto {

    /**
     * 主机
     */
    private String host;

    /**
     * 模板ids
     */
    private List<String> templateIds;

}
