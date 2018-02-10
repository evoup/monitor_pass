package com.evoupsight.monitorPass.dataCollector.handlers;

import com.evoupsight.kafkaclient.producer.KafkaProducer;
import com.evoupsight.kafkaclient.util.KafkaCallback;
import com.evoupsight.kafkaclient.util.KafkaMessage;
import com.evoupsight.monitorPass.dataCollector.server.ClientStateMap;
import com.evoupsight.monitorPass.dataCollector.server.ClientState;
import com.evoupsight.monitorPass.dataCollector.server.NettyChannelMap;
import io.netty.channel.ChannelHandler.Sharable;
import io.netty.channel.ChannelHandlerContext;
import io.netty.channel.SimpleChannelInboundHandler;
import io.netty.channel.socket.SocketChannel;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Qualifier;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.stereotype.Component;

import java.util.regex.Matcher;
import java.util.regex.Pattern;

@Component
@Qualifier("serverHandler")
@Sharable
public class ServerHandler extends SimpleChannelInboundHandler<String> {
    private static final Logger LOG = LoggerFactory.getLogger(ServerHandler.class);
    @Value("${kafka.brokers}")
    String brokers;
    @Value("${kafka.topic}")
    String topic;
    private static final Pattern
            CLIENT_FIRST_MESSAGE = Pattern.compile("^(([pny])=?([^,]*),([^,]*),)(m?=?[^,]*,?n=([^,]*),r=([^,]*),?.*)$");
    private static final Pattern
            CLIENT_FINAL_MESSAGE = Pattern.compile("(c=([^,]*),r=([^,]*)),p=(.*)$");

    @Override
    public void channelRead0(ChannelHandlerContext channelHandlerContext, String msg) throws Exception {
        System.out.print(msg);
        boolean seemToBeClientFirstMessage = false;
        String clientName = "cl";
        // 看消息是不是client first message
        // 消息格式为n,,n=clientName,r=oJnNPGsiuz
        if (ClientStateMap.get(clientName) == null || ClientStateMap.get(clientName).equals(ClientState.INITIAL) ||
                seemToBeClientFirstMessage) {
            Matcher m = CLIENT_FIRST_MESSAGE.matcher(msg);
            if (!m.matches()) {
                return;
            }
            ClientStateMap.set(clientName, ClientState.CLIENT_FIRST_SENT);
            // 写server first message
            return;
        }
        if (ClientStateMap.get(clientName) == ClientState.CLIENT_FIRST_SENT) {
            // 看新消息是不是client final message
            ClientStateMap.set(clientName, ClientState.CLIENT_FINAL_SENT);
            // 验证通过,写server final message
            ClientStateMap.set(clientName, ClientState.ENDED);
            // 验证不通过
            ClientStateMap.remove("");
            return;
        }
        if (ClientStateMap.get(clientName).equals(ClientState.ENDED)) {
            // 正式开始发送
            NettyChannelMap.add(clientName, (SocketChannel) channelHandlerContext.channel());
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
            return;
        }
        // 奇怪的状态，认为是初始化中
        ClientStateMap.set(clientName, ClientState.INITIAL);
    }

    @Override
    public void channelActive(ChannelHandlerContext ctx) throws Exception {
        LOG.debug("Channel is active");
        super.channelActive(ctx);
    }

    @Override
    public void channelInactive(ChannelHandlerContext ctx) throws Exception {
        LOG.debug("Channel is disconnected");
        NettyChannelMap.remove((SocketChannel) ctx.channel());
        super.channelInactive(ctx);
    }

}
