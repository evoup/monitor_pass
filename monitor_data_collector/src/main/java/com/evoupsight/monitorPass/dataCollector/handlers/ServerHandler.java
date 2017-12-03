package com.evoupsight.monitorPass.dataCollector.handlers;

import com.evoupsight.kafkaclient.producer.KafkaProducer;
import com.evoupsight.kafkaclient.util.KafkaCallback;
import com.evoupsight.kafkaclient.util.KafkaMessage;
import io.netty.channel.ChannelHandler.Sharable;
import io.netty.channel.ChannelHandlerContext;
import io.netty.channel.SimpleChannelInboundHandler;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Qualifier;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.stereotype.Component;

@Component
@Qualifier("serverHandler")
@Sharable
public class ServerHandler extends SimpleChannelInboundHandler<String> {
    private static final Logger LOG = LoggerFactory.getLogger(ServerHandler.class);
    @Value("${kafka.brokers}")
    String brokers;
    @Value("${kafka.topic}")
    String topic;

    @Override
    public void channelRead0(ChannelHandlerContext ctx, String msg)
            throws Exception {
        System.out.print(msg);
        LOG.debug("got a message here");
        //ctx.channel().writeAndFlush(msg);
        KafkaProducer producer = new KafkaProducer(brokers, 5, null);
        if (producer.start(new KafkaCallback() {
            @Override
            public void onCompletion(KafkaMessage message, Exception e) {
                if (e != null) {
                    System.out.println(e.toString());
                }
            }
        })) {
            producer.sendMessage(topic, msg.getBytes());
        }
    }

    @Override
    public void channelActive(ChannelHandlerContext ctx) throws Exception {
        LOG.debug("Channel is active");
        super.channelActive(ctx);
    }

    @Override
    public void channelInactive(ChannelHandlerContext ctx) throws Exception {
        LOG.debug("Channel is disconnected");
        super.channelInactive(ctx);
    }

}
