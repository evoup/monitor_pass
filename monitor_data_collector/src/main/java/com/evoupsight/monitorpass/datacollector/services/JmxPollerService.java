package com.evoupsight.monitorpass.datacollector.services;

import com.evoupsight.monitorpass.datacollector.domain.ObjNameAttributes;
import org.apache.log4j.Logger;
import org.springframework.stereotype.Service;

import javax.management.*;
import javax.management.remote.JMXConnector;
import javax.management.remote.JMXConnectorFactory;
import javax.management.remote.JMXServiceURL;
import java.io.IOException;
import java.util.Map;
import java.util.Set;
import java.util.concurrent.*;

/**
 * @author evoup
 */
@Service
public class JmxPollerService {
    private static final Logger LOG = Logger.getLogger(JmxPollerService.class);

    /**
     * 根据redis中属于jmx的类型的，并且监控的items，进行poll，poll完后就送到opentsdb了
     * 任务1秒执行一次
     */

    public void poll() {
        try {
            //tomcat jmx url
            String jmxURL = "service:jmx:rmi:///jndi/rmi://192.168.2.4:9090/jmxrmi";
            JMXServiceURL serviceURL = new JMXServiceURL(jmxURL);
            JMXConnector connector = connectWithTimeout(serviceURL, 4);
            MBeanServerConnection mbsc = connector.getMBeanServerConnection();

            //端口最好是动态取得
            //ObjectName threadObjName = new ObjectName("Catalina:type=ThreadPool,name=http-8089");
            String objectNameStr = "Catalina:type=ProtocolHandler,port=8080";
            String attrName = "compression";
            ObjNameAttributes objNameAttributes = new ObjNameAttributes();
            objNameAttributes.setMonitoredObject(objectNameStr, attrName);
            Map<String, Set<String>> map = objNameAttributes.getObjNameAttributes();
            map.forEach((k,v) -> {
                ObjectName objectName = null;
                try {
                    objectName = new ObjectName(k);
                } catch (MalformedObjectNameException e) {
                    e.printStackTrace();
                    LOG.error(e.getMessage(), e);
                }
                try {
                    MBeanInfo mbInfo = mbsc.getMBeanInfo(objectName);
                    //tomcat是否用gzip压缩
                    MBeanAttributeInfo[] mbAttributes = mbInfo.getAttributes();
                    if (mbAttributes != null) {
                        for (MBeanAttributeInfo mbAttribute : mbAttributes) {
                            if (attrName.equals(mbAttribute.getName())) {
                                LOG.info("jmx metric " + attrName + ":" + mbsc.getAttribute(objectName, attrName));
                            }
                        }
                    }
                } catch (Exception e) {
                    e.printStackTrace();
                    LOG.error(e.getMessage(), e);
                }
            });
        } catch (Exception e) {
            LOG.error(e.getMessage(), e);
        }
    }

    private JMXConnector connectWithTimeout(final JMXServiceURL url, long timeout) throws InterruptedException, ExecutionException, TimeoutException {
        ExecutorService executor = Executors.newSingleThreadExecutor();
        Future<JMXConnector> future = executor.submit(new Callable<JMXConnector>() {
            @Override
            public JMXConnector call() throws IOException {
                return JMXConnectorFactory.connect(url);
            }
        });
        return future.get(timeout, TimeUnit.SECONDS);
    }
}
