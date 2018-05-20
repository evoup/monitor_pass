package com.evoupsight.monitorpass.datacollector.services;

import org.apache.log4j.Logger;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Service;

import javax.management.MBeanAttributeInfo;
import javax.management.MBeanInfo;
import javax.management.MBeanServerConnection;
import javax.management.ObjectName;
import javax.management.remote.JMXConnector;
import javax.management.remote.JMXConnectorFactory;
import javax.management.remote.JMXServiceURL;
import java.util.HashMap;
import java.util.Map;

/**
 * @author evoup
 */
@Service
public class JMXPollerService {
    private static final Logger LOG = Logger.getLogger(JMXPollerService.class);

    /**
     * 根据redis中属于jmx的类型的，并且监控的items，进行poll，poll完后就送到opentsdb了
     * 任务1秒执行一次
     */
    //@Scheduled(fixedDelay = 1000)
    public void poll() {
        try {
            //tomcat jmx url
            String jmxURL = "service:jmx:rmi:///jndi/rmi://192.168.2.196:9090/jmxrmi";
            JMXServiceURL serviceURL = new JMXServiceURL(jmxURL);
            Map map = new HashMap();
            JMXConnector connector = JMXConnectorFactory.connect(serviceURL, map);
            MBeanServerConnection mbsc = connector.getMBeanServerConnection();

            //端口最好是动态取得
            //ObjectName threadObjName = new ObjectName("Catalina:type=ThreadPool,name=http-8089");
            ObjectName objectName = new ObjectName("Catalina:type=ProtocolHandler,port=8080");
            MBeanInfo mbInfo = mbsc.getMBeanInfo(objectName);

            //tomcat是否用gzip压缩
            String attrName = "compression";
            MBeanAttributeInfo[] mbAttributes = mbInfo.getAttributes();
            System.out.println("compression:" + mbsc.getAttribute(objectName, attrName));
        } catch (Exception e) {

        }
    }
}
