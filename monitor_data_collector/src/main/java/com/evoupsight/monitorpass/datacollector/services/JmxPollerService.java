package com.evoupsight.monitorpass.datacollector.services;

import com.evoupsight.monitorpass.datacollector.domain.ObjNameAttributes;
import org.apache.log4j.Logger;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.beans.factory.annotation.Qualifier;
import org.springframework.stereotype.Service;

import javax.management.*;
import javax.management.remote.JMXConnector;
import javax.management.remote.JMXConnectorFactory;
import javax.management.remote.JMXServiceURL;
import java.io.IOException;
import java.util.ArrayList;
import java.util.List;
import java.util.Map;
import java.util.Set;
import java.util.concurrent.*;

/**
 * @author evoup
 */
@Service
public class JmxPollerService {
    private static final Logger LOG = Logger.getLogger(JmxPollerService.class);
    @Autowired
    @Qualifier("jmxExecutorServiceThreadPool")
    protected ExecutorService es;


    public void poll() {
        int totaltaskNum = 50;

        List<Callable<Boolean>> callableTasks = new ArrayList<>();
        for (int i = 0; i < totaltaskNum; i++) {
            callableTasks.add(callableTask);
        }

        try {
            List<Future<Boolean>> answers = es.invokeAll(callableTasks);
            LOG.info("jmx poll done");
            System.out.println("jmx poll done");
        } catch (InterruptedException e) {
            e.printStackTrace();
            LOG.error(e.getMessage(), e);
//        } finally {
//            es.shutdownNow();
        }
    }


    /**
     * 根据redis中属于jmx的类型的，并且监控的items，进行poll，poll完后就送到opentsdb了
     * 任务1秒执行一次
     */

    private void jmxPoll() throws IOException, InterruptedException, ExecutionException, TimeoutException {
        String jmxURL = "service:jmx:rmi:///jndi/rmi://192.168.2.4:9090/jmxrmi";
        JMXServiceURL serviceURL = new JMXServiceURL(jmxURL);
        try (JMXConnector connector = connectWithTimeout(serviceURL, 4)) {
            //tomcat jmx url
            MBeanServerConnection mbsc = connector.getMBeanServerConnection();
            //端口最好是动态取得
            //ObjectName threadObjName = new ObjectName("Catalina:type=ThreadPool,name=http-8089");
            String objectNameStr = "Catalina:type=ProtocolHandler,port=8080";
            String attrName = "compression";
            ObjNameAttributes objNameAttributes = new ObjNameAttributes();
            objNameAttributes.setMonitoredObject(objectNameStr, attrName);
            Map<String, Set<String>> map = objNameAttributes.getObjNameAttributes();
            map.forEach((k, v) -> {
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
                                System.out.println("jmx metric " + attrName + ":" + mbsc.getAttribute(objectName, attrName));
                                LOG.info("jmx metric " + attrName + ":" + mbsc.getAttribute(objectName, attrName));
                            }
                        }
                    }
                } catch (Exception e) {
                    e.printStackTrace();
                    LOG.error(e.getMessage(), e);
                }
            });
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


    private Callable<Boolean> callableTask = () -> {
        try {
            jmxPoll();
            return Boolean.TRUE;
        } catch (Exception e) {
            e.printStackTrace();
            return Boolean.FALSE;
        }
    };
}
