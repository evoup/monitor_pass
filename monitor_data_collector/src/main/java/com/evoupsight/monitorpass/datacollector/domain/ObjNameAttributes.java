package com.evoupsight.monitorpass.datacollector.domain;

import java.util.*;

/**
 * @author evoup
 */
public class ObjNameAttributes {
    private Map<String, Set<String>> objNameAttributes = new HashMap<>();

    public void setMonitoredObject(String objectName, String attribute) {
        Set<String> attributes = this.objNameAttributes.get(objectName);
        if (attributes == null) {
            attributes = new HashSet<>();
        }
        attributes.add(attribute);
        this.objNameAttributes.put(objectName, attributes);
    }

    public Map<String, Set<String>> getObjNameAttributes() {
        return objNameAttributes;
    }
}
