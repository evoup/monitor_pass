package com.evoupsight.monitorpass.datacollector.services.impl;

import com.evoupsight.monitorpass.datacollector.constants.RpcConstant;
import com.evoupsight.monitorpass.datacollector.dao.model.Server;
import com.evoupsight.monitorpass.datacollector.services.ServerService;
import com.evoupsight.monitorpass.datacollector.services.SnmpPollerService;
import com.googlecode.jsonrpc4j.JsonRpcClient;
import org.apache.log4j.Logger;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.beans.factory.annotation.Qualifier;
import org.springframework.stereotype.Service;

import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.net.Socket;
import java.util.ArrayList;
import java.util.List;
import java.util.concurrent.Callable;
import java.util.concurrent.ExecutorService;

/**
 * @author evoup
 */
@Service
public class MonitorItemConfigPollerServiceImpl {
    @Autowired
    @Qualifier("monitorItemConfigExecutorServiceThreadPool")
    protected ExecutorService es;
    @Autowired
    private ServerService serverService;

    private static final Logger LOG = Logger.getLogger(SnmpPollerService.class);

    public void poll() {
        // 每个被监控主机作为一个任务
        LOG.info(serverService);
        List<Server> servers = serverService.fetchAllMonitoringServer();
        int totalTaskNum = servers.size();
        LOG.info("there`re " + totalTaskNum + " servers to be dispatch config");

        List<ConfigUpdateTask> tasks = new ArrayList<>();
        for (int i = 0; i < totalTaskNum; i++) {
            tasks.add(new ConfigUpdateTask(servers.get(i)));
        }

        try {
            es.invokeAll(tasks);
            LOG.info("config update poll done");
            System.out.println("config update poll done");
        } catch (InterruptedException e) {
            e.printStackTrace();
            LOG.error(e.getMessage(), e);
        }
    }

    public void configUpdatePoll(Server server) {
        LOG.info(server.getName() + "will be dispatch config");
        LOG.info("monitor item rpc client updater");
        Socket socket = null;
        try {
            socket = new Socket("127.0.0.1", 8338);
            JsonRpcClient client = new JsonRpcClient();

            InputStream ips = socket.getInputStream();
            OutputStream ops = socket.getOutputStream();
            int reply = client.invokeAndReadResponse("MonitorItemConfig.Update", new Object[]{"memeda"}, int.class, ops, ips);
            System.out.println("reply: " + reply);
            if (RpcConstant.SERVER_OK.code.equals(reply)) {
                System.out.println("调用服务成功");
            } else {
                System.out.println("调用服务失败");
            }
        } catch (Throwable throwable) {
            LOG.error(throwable.getMessage(), throwable);
            throwable.printStackTrace();
        } finally {
            try {
                if (socket != null) {
                    socket.close();
                }
            } catch (IOException e) {
                LOG.error(e.getMessage(), e);
            }
        }

    }

    public class ConfigUpdateTask implements Callable<Server>{
        Server server;
        public ConfigUpdateTask(Server server) {
            configUpdatePoll(server);
            this.server = server;
        }
        @Override
        public Server call() {
            return server;
        }
    }
}
