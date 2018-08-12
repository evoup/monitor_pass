package com.evoupsight.monitorpass.datacollector.server;

/**
 * @author evoup
 */

public enum ServerState {
    /**
     * 初始化
     */
    INITIAL,
    /**
     * 客户端首个消息已经处理
     */
    FIRST_CLIENT_MESSAGE_HANDLED,
    @Deprecated
    PREPARED_FIRST,
    /**
     * 认证结束
     */
    ENDED
}