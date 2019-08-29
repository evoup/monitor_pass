package com.evoupsight.monitorpass.server.dto.opentsdb;

import lombok.Getter;
import lombok.Setter;

import java.util.HashMap;

/**
 * @author evoup
 */
@Getter
@Setter
public class QueryDto {
    private String metric;
    private HashMap<String, Object> tags;
    private HashMap<String, Object> dps;

}
