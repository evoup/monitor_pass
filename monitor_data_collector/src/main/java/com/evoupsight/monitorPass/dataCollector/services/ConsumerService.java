package com.evoupsight.monitorPass.dataCollector.services;

import com.evoupsight.kafkaclient.consumer.KafkaConsumer;
import com.evoupsight.kafkaclient.util.KafkaCallback;
import org.apache.commons.lang3.StringUtils;
import org.opentsdb.client.ExpectResponse;
import org.opentsdb.client.HttpClient;
import org.opentsdb.client.HttpClientImpl;
import org.opentsdb.client.builder.Metric;
import org.opentsdb.client.builder.MetricBuilder;
import org.opentsdb.client.response.Response;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.stereotype.Service;

import javax.annotation.PostConstruct;
import javax.sound.midi.Track;
import java.io.IOException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

@Service
public class ConsumerService {
    private static final Logger LOG = LoggerFactory.getLogger(ConsumerService.class);
    @Value("${kafka.brokers}")
    String brokers;
    @Value("${kafka.topic}")
    String topic;
    @Value("${kafka.groupId}")
    String groupId;
    @Autowired
    KafkaConsumer consumer;

    @PostConstruct
    public void initConsumer() {
        LOG.info("perform a consume");
        consume();
    }

    private void consume() {
        LOG.info("in consume");
        consumer.start(topic, new KafkaCallback() {
            @Override
            public boolean onFetch(String topic, int partition, long offset, byte[] message) {
                System.out.println("in consume2");
                try {
                    String msg = new String(message);
                    System.out.println(String.format("%s-%d-%d-%s", topic, partition, offset, msg));
                    myProcessMsgBag(msg);
                } catch (IOException e) {
                    e.printStackTrace();
                }
                return true;
            }

            private void myProcessMsgBag(String m) throws IOException {
                HttpClient client = new HttpClientImpl("http://localhost:14242");
                MetricBuilder builder = MetricBuilder.getInstance();
                // put proc.loadavg.1m 1524995898 1.13 host=evoup-Inspiron-3443
                // put metric value tagkey=tagvalue
                // proc.uptime.total 1525004232 27925.52
                // procstats proc.uptime.total 1525005860 29552.60 host=montioragent2
                // sysload cpu.idle 1525009928 69.80 cpu=0 host=monitoragent2
                if (StringUtils.isNoneEmpty(m)) {
                    String[] split = m.split(" ");
                    String metricKey = split[1];
                    int timeStamp = 0;
                    try {
                        timeStamp = Integer.parseInt(split[2]);
                    } catch (NumberFormatException | ArrayIndexOutOfBoundsException e) {
                        return;
                    }
                    double value = 0.0;
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
                    builder.addMetric(metricKey).setDataPoint(timeStamp, value).addTags(map);
                    Response response = client.pushMetrics(builder, ExpectResponse.SUMMARY);
                    System.out.println(response);
                }
            }
        });
    }
}
