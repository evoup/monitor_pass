package com.evoupsight.monitorpass.datacollector.utils;

import org.apache.commons.dbcp2.BasicDataSource;

import java.util.ArrayList;

/**
 * dbcp配置
 *
 * @author evoup
 */
public class DbUtils {
    public BasicDataSource getBasicDataSource(String driver, String url, String userName, String userPass) {
        BasicDataSource ds = new BasicDataSource();
        ds.setDriverClassName(driver);
        ds.setUrl(url);
        ds.setUsername(userName);
        ds.setPassword(userPass);
        ds.setInitialSize(5);
        ds.setMaxTotal(80);
        ds.setMinIdle(10);
        ds.setMaxIdle(60);
        ds.setMaxOpenPreparedStatements(100);
        ds.setMaxWaitMillis(5000);
        ds.setValidationQuery("SELECT 1");
        ds.setTestWhileIdle(true);
        ds.setTestOnBorrow(false);
        ds.setTimeBetweenEvictionRunsMillis(30000);
        ds.setMinEvictableIdleTimeMillis(1800000);
        ds.setRemoveAbandonedTimeout(180);
        ds.setNumTestsPerEvictionRun(3);
        ds.setRemoveAbandonedOnBorrow(true);
        ds.setRemoveAbandonedOnMaintenance(true);
        ds.setConnectionInitSqls(new ArrayList<String>() {{
            add("set names utf8mb4;");
        }});
        return ds;
    }
}
