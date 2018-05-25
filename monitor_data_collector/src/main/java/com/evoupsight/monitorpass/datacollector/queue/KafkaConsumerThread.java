package com.evoupsight.monitorpass.datacollector.queue;

import com.evoupsight.monitorpass.datacollector.sender.Sender;
import org.apache.kafka.clients.consumer.ConsumerRecord;
import org.apache.kafka.clients.consumer.ConsumerRecords;
import org.apache.kafka.clients.consumer.KafkaConsumer;
import org.opentsdb.client.PoolingHttpClient;
import java.util.Arrays;
import java.util.Map;
import java.util.Properties;


public class KafkaConsumerThread extends Thread {


    private KafkaConsumer<String, String> consumer;
    private String openstdbServerUrl;
    private PoolingHttpClient httpClient;

    public KafkaConsumerThread(Map<String, Object> consumerConfig, String topic, String openstdbServerUrl, PoolingHttpClient httpClient) {
        Properties props = new Properties();
        props.putAll(consumerConfig);
        this.consumer = new KafkaConsumer<>(props);
        consumer.subscribe(Arrays.asList(topic));
        this.openstdbServerUrl = openstdbServerUrl;
        this.httpClient = httpClient;
    }

    @Override
    public void run() {
        try {
            while (true) {
                ConsumerRecords<String, String> records = consumer.poll(1000);
                for (ConsumerRecord<String, String> record : records) {
                    System.out.printf("threadId=%s,partition=%d,offset=%d,key=%s,value=%s%n",
                            Thread.currentThread().getId(),
                            record.partition(), record.offset(), record.key(), record.value());
                    new Sender(record.value(), this.openstdbServerUrl, this.httpClient).myProcessMsgBag();
                }
            }
        } catch (Exception e) {
            e.printStackTrace();
        } finally {
            consumer.close();
        }
    }
}
