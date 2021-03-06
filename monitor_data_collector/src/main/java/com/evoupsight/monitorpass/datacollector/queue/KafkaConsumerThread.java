package com.evoupsight.monitorpass.datacollector.queue;

import com.evoupsight.monitorpass.datacollector.sender.Sender;
import org.apache.kafka.clients.consumer.ConsumerRecord;
import org.apache.kafka.clients.consumer.ConsumerRecords;
import org.apache.kafka.clients.consumer.KafkaConsumer;
import org.apache.log4j.Logger;

import java.util.Collections;
import java.util.Map;
import java.util.Properties;


/**
 * @author evoup
 */
public class KafkaConsumerThread extends Thread {
    private static final Logger LOG = Logger.getLogger(KafkaConsumerThread.class);
    private KafkaConsumer<String, String> consumer;
    private String openstdbServerUrl;
    private String dataCollectorServerName;

    public KafkaConsumerThread(Map<String, Object> consumerConfig, String topic, String openstdbServerUrl,
                               String dataCollectorServerName) {
        Properties props = new Properties();
        props.putAll(consumerConfig);
        this.consumer = new KafkaConsumer<>(props);
        consumer.subscribe(Collections.singletonList(topic));
        this.openstdbServerUrl = openstdbServerUrl;
        this.dataCollectorServerName = dataCollectorServerName;
    }

    @Override
    public void run() {
        try {
            while (true) {
                ConsumerRecords<String, String> records = consumer.poll(100);
                for (ConsumerRecord<String, String> record : records) {
                    System.out.printf("threadId=%s,partition=%d,offset=%d,key=%s,value=%s%n",
                            Thread.currentThread().getId(),
                            record.partition(), record.offset(), record.key(), record.value());
                    new Sender(record.value(), this.openstdbServerUrl, this.dataCollectorServerName).myProcessMsgBag();
                }
            }
        } catch (Exception e) {
            LOG.error(e.getMessage(), e);
            e.printStackTrace();
        } finally {
            // 不要关闭消费者
        }
    }
}
