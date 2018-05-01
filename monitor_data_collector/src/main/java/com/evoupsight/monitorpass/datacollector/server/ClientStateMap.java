package com.evoupsight.monitorpass.datacollector.server;

import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;

public class ClientStateMap {
    private static Map<String, ServerState> map = new ConcurrentHashMap<>();

    public static void set(String clientId, ServerState serverState) {
        map.put(clientId, serverState);
    }
    public static ServerState get(String clientId) {
        return map.get(clientId);
    }
    public static void remove(String clientId) {
        map.remove(clientId);
    }
}