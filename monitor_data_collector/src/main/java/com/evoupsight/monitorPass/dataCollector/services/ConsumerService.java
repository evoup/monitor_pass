package com.evoupsight.monitorPass.dataCollector.services;

import com.evoupsight.kafkaclient.consumer.KafkaConsumer;
import com.evoupsight.kafkaclient.util.KafkaCallback;
import org.opentsdb.client.ExpectResponse;
import org.opentsdb.client.HttpClient;
import org.opentsdb.client.HttpClientImpl;
import org.opentsdb.client.builder.MetricBuilder;
import org.opentsdb.client.response.Response;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.stereotype.Service;

import javax.annotation.PostConstruct;
import java.io.IOException;
import java.util.ArrayList;
import java.util.List;

@Service
public class ConsumerService {
    private static final Logger LOG = LoggerFactory.getLogger(ConsumerService.class);
    @Value("${kafka.brokers}")
    String brokers;
    @Value("${kafka.topic}")
    String topic;
    @Value("${kafka.groupId}")
    String groupId;
    private List<String> messageBag = new ArrayList<>();
    @Autowired
    KafkaConsumer consumer;

    @PostConstruct
    public void initConsumer() {
        //KafkaConsumer consumer = new KafkaConsumer(brokers, groupId);
        consumer.start(topic, new KafkaCallback() {
            @Override
            public boolean onFetch(String topic, int partition, long offset, byte[] message) {
                String msg = new String(message);
                System.out.println(String.format("%s-%d-%d-%s", topic, partition, offset, msg));
                messageBag.add(msg);
                // TODO 这里改成如果时间超过指定时间的话也要发送掉
                if (messageBag != null && messageBag.size() > 10) {
                    System.out.println("send all messages");
//                    ////////
//                    HttpClient client = new HttpClientImpl("http://localhost:8242");
//
//
//                    MetricBuilder builder = MetricBuilder.getInstance();
//
//                    builder.addMetric("metric1").setDataPoint(2, 30L)
//                            .addTag("tag1", "tab1value").addTag("tag2", "tab2value");
//
//                    builder.addMetric("metric2").setDataPoint(2, 232.34)
//                            .addTag("tag3", "tab3value");
//
//                    try {
//                        Response response = client.pushMetrics(builder,
//                                ExpectResponse.SUMMARY);
//                        System.out.println(response);
//                    } catch (IOException e) {
//                        e.printStackTrace();
//                    }
//                    ////////
                    messageBag = new ArrayList<>();
                }
                return true;
            }
        });
    }
}
