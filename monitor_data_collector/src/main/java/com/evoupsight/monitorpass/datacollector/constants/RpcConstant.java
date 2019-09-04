package com.evoupsight.monitorpass.datacollector.constants;

/**
 * @author evoup
 */
public enum RpcConstant {
    /**
     * 调用rpc方法成功
     */
    SERVER_OK(20000),

    /**
     * 调用rpc方法失败
     */
    SERVER_FAIL(40000);

    public final Integer code;

    RpcConstant(Integer code) {
        this.code = code;
    }
}
