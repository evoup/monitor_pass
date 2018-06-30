package com.evoupsight.monitorpass.server.constants;

/**
 * @author evoup
 */
public class Constants {

    public static final long DEFAULT_WAIT_TIME_SECONDS = Long.MAX_VALUE;

    public static final String LOCK_PATH = "/monitorserver2";

    public static final String MY_NAME = "myname";

    public static final String MDB_TAB_ENGINE = "monitor_engine";
    public static final String KEY_SCAN_DURATION = "durationtime";
    public static final String MDB_COL_SCAN_DURATION = "scan:duration";


    /**
     * 事件状态
     */
    public enum EventState {
        /**
         * 正常
         */
        OK,
        /**
         * 出现问题
         */
        PROBLEM
    }


}
