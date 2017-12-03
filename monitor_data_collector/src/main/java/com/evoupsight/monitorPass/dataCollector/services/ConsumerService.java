package com.evoupsight.monitorPass.dataCollector.services;

import com.evoupsight.kafkaclient.consumer.KafkaConsumer;
import com.evoupsight.kafkaclient.util.KafkaCallback;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.stereotype.Service;

import javax.annotation.PostConstruct;
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

    @PostConstruct
    public void initConsumer() {
        KafkaConsumer consumer = new KafkaConsumer(brokers, groupId);
        consumer.start(topic, new KafkaCallback() {
            @Override
            public boolean onFetch(String topic, int partition, long offset, byte[] message) {
                String msg = new String(message);
                System.out.println(String.format("%s-%d-%d-%s", topic, partition, offset, msg));
                messageBag.add(msg);
                if (messageBag.size() > 10) {
                    System.out.println("send all messages");
                    messageBag = new ArrayList<>();
                }
                return true;
            }
        });
    }
}
