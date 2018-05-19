package com.evoupsight.monitorpass.datacollector.services;

import com.evoupsight.monitorpass.datacollector.queue.KafkaConsumerThread;
import org.apache.log4j.Logger;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.stereotype.Service;

import javax.annotation.PostConstruct;
import java.util.HashMap;
import java.util.Map;

@Service
public class ConsumeService {
    private static final Logger LOG = Logger.getLogger(ConsumeService.class);
    @Value("${kafka.brokers}")
    String brokers;
    @Value("${kafka.topic}")
    String topic;
    @Value("${kafka.groupId}")
    String groupId;
    @Value("${opentsdb.serverurl}")
    String opentsdbServerUrl;

    @PostConstruct
    public void initConsumer() {
        LOG.info("perform a consume");
        try {
            consume();
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    private void consume() {
        Map<String, Object> config = new HashMap<String, Object>();
        config.put("bootstrap.servers", brokers);
        config.put("group.id", groupId);
        config.put("enable.auto.commit", true);
        config.put("auto.commit.interval.ms", 1000);
        config.put("key.deserializer", "org.apache.kafka.common.serialization.StringDeserializer");
        config.put("value.deserializer", "org.apache.kafka.common.serialization.ByteArrayDeserializer");
        for (int i = 0; i < 5; i++) {
            new KafkaConsumerThread(config, topic).start();
        }
    }
}
