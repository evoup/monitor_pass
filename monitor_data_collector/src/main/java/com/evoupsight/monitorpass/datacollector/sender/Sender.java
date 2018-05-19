package com.evoupsight.monitorpass.datacollector.sender;


import org.opentsdb.client.ExpectResponse;
import org.opentsdb.client.HttpClient;
import org.opentsdb.client.HttpClientImpl;
import org.opentsdb.client.PoolingHttpClient;
import org.opentsdb.client.builder.MetricBuilder;
import org.opentsdb.client.response.Response;
import org.opentsdb.client.response.SimpleHttpResponse;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.beans.factory.annotation.Qualifier;
import org.springframework.stereotype.Component;
import org.springframework.stereotype.Service;
import org.springframework.util.StringUtils;

import java.io.IOException;
import java.io.InputStream;
import java.util.HashMap;
import java.util.Map;

/**
 * @author evoup
 */

public class Sender {


    private PoolingHttpClient httpClient;

    private String message;
    private String opentsdbServerUrl;

    public Sender(String message, String opentsdbServerUrl, PoolingHttpClient httpClient) {
        this.message = message;
        this.opentsdbServerUrl = opentsdbServerUrl;
        this.httpClient = httpClient;
    }

    public void myProcessMsgBag() throws IOException {
        String m = this.message;
        String opentsdbServerUrl = this.opentsdbServerUrl;
        //HttpClient client = new HttpClientImpl(opentsdbServerUrl);
        //MetricBuilder builder = MetricBuilder.getInstance();
        // put proc.loadavg.1m 1524995898 1.13 host=evoup-Inspiron-3443
        // put metric value tagkey=tagvalue
        // proc.uptime.total 1525004232 27925.52
        // procstats proc.uptime.total 1525005860 29552.60 host=montioragent2
        // sysload cpu.idle 1525009928 69.80 cpu=0 host=monitoragent2
        if (!StringUtils.isEmpty(m)) {
            String[] split = m.split(" ");
            String metricKey = split[1];
            int timeStamp;
            try {
                timeStamp = Integer.parseInt(split[2]);
            } catch (NumberFormatException | ArrayIndexOutOfBoundsException e) {
                return;
            }
            double value;
            try {
                value = Double.parseDouble(split[3]);
            } catch (NumberFormatException | ArrayIndexOutOfBoundsException e) {
                return;
            }
            Map<String, String> map = new HashMap<>();
            // 第五位开始是tag=tag_value
            for (int i = 4; i < split.length; i++) {
                String tagKv = split[4];
                String[] tagKV = tagKv.split("=");
                try {
                    map.put(tagKV[0], tagKV[1]);
                } catch (ArrayIndexOutOfBoundsException e) {
                    return;
                }
            }

            MetricBuilder builder = MetricBuilder.getInstance();
            builder.addMetric(metricKey).setDataPoint(timeStamp, value).addTags(map);
            SimpleHttpResponse response = httpClient.doPost(
                    opentsdbServerUrl + "/api/put/?details",
                    builder.build());
            System.out.println(response.getStatusCode());
            System.out.println(response.getContent());
        }
    }
}
