package com.evoupsight.monitorpass.server.dto;

import lombok.Builder;
import lombok.Getter;
import lombok.Setter;

/**
 * 操作消息的DTO
 * @author evoup
 */
@Getter
@Setter
@Builder
public class OperationMessageDto {
    String serverName;
    String itemName;
}
