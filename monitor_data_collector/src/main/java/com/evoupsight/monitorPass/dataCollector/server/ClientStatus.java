package com.evoupsight.monitorPass.dataCollector.server;

import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;

public class ClientStatus {
    private static Map<String, ClientStatusType> map = new ConcurrentHashMap<>();

    public static void add(String clientId, ClientStatusType clientStatusType) {
        map.put(clientId, clientStatusType);
    }
    public static ClientStatusType get(String clientId) {
        return map.get(clientId);
    }
    public static void remove(String clientId) {
        map.remove(clientId);
    }
}