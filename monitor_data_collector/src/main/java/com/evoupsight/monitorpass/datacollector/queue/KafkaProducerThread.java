package com.evoupsight.monitorpass.datacollector.queue;


import org.apache.kafka.clients.producer.Callback;
import org.apache.kafka.clients.producer.KafkaProducer;
import org.apache.kafka.clients.producer.ProducerRecord;
import org.apache.kafka.clients.producer.RecordMetadata;
import org.apache.log4j.Logger;

public class KafkaProducerThread implements Runnable {
    private static final Logger LOG = Logger.getLogger(KafkaProducerThread.class);
    private KafkaProducer<String, String> producer;
    private ProducerRecord<String, String> record;

    public KafkaProducerThread(KafkaProducer<String,String> producer, ProducerRecord<String,String> record) {
        this.producer = producer;
        this.record = record;
    }
    @Override
    public void run() {
        producer.send(record, new Callback() {
            @Override
            public void onCompletion(RecordMetadata recordMetadata, Exception e) {
                if (null != e) {
                    LOG.error("Send message occurs exception.", e);
                }
                if (null != recordMetadata) {
                    LOG.info(String.format("offset:%s,partition:%s", recordMetadata.offset(), recordMetadata.partition()));
                }
            }
        });
    }
}
