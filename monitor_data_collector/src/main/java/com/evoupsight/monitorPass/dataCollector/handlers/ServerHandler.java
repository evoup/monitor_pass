package com.evoupsight.monitorPass.dataCollector.handlers;

import com.evoupsight.kafkaclient.producer.KafkaProducer;
import com.evoupsight.kafkaclient.util.KafkaCallback;
import com.evoupsight.kafkaclient.util.KafkaMessage;
import com.evoupsight.monitorPass.dataCollector.server.ClientState;
import com.evoupsight.monitorPass.dataCollector.server.NettyChannelMap;
import io.netty.buffer.Unpooled;
import io.netty.channel.ChannelFutureListener;
import io.netty.channel.ChannelHandler.Sharable;
import io.netty.channel.ChannelHandlerContext;
import io.netty.channel.ChannelInboundHandlerAdapter;
import io.netty.channel.SimpleChannelInboundHandler;
import io.netty.channel.socket.SocketChannel;
import io.netty.util.AttributeKey;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Qualifier;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.stereotype.Component;

import java.util.UUID;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

@Component
@Qualifier("serverHandler")
@Sharable
public class ServerHandler extends ChannelInboundHandlerAdapter {
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
    public void channelReadComplete(ChannelHandlerContext ctx) throws Exception {
        ctx.writeAndFlush(Unpooled.EMPTY_BUFFER).addListener(ChannelFutureListener.CLOSE_ON_FAILURE);
    }

    @Override
    public void channelRead(ChannelHandlerContext ctx, Object msg) throws Exception {
        System.out.print(msg);
        // 看消息是不是client first message
        // 消息格式为n,,n=clientName,r=oJnNPGsiuz
        String clientName;
        if (!ctx.channel().hasAttr(AttributeKey.valueOf("clientId")) ||
                ctx.channel().attr(AttributeKey.valueOf("clientState")).get().equals(ClientState.INITIAL)) {
            Matcher m = CLIENT_FIRST_MESSAGE.matcher(msg.toString());
            if (!m.matches()) {
                ctx.channel().write("invalid protocol\n");
                return;
            }
            clientName = m.group(6);
            String clientNonce = m.group(7);
            ctx.channel().attr(AttributeKey.valueOf("clientId")).set(clientName);
            ctx.channel().attr(AttributeKey.valueOf("clientNonce")).set(clientNonce);
            ctx.channel().attr(AttributeKey.valueOf("clientState")).set(ClientState.FIRST_CLIENT_MESSAGE_HANDLED);
            // 写server first message
            String serverNonce = UUID.randomUUID().toString();
            String salt = UUID.randomUUID().toString();
            String iterator = "4096";
            ctx.channel().attr(AttributeKey.valueOf("serverNonce")).set(serverNonce);
            ctx.channel().attr(AttributeKey.valueOf("salt")).set(salt);
            ctx.channel().attr(AttributeKey.valueOf("iterator")).set(iterator);
            StringBuffer sb = new StringBuffer();
            sb.append("r=").append(clientNonce).append(serverNonce).append(",s=").append(salt).append(",i=").append(iterator);
            ctx.channel().write(sb);
            ctx.write(sb);
            return;
        }
        if (ctx.channel().attr(AttributeKey.valueOf("clientState")).get().equals(ClientState.FIRST_CLIENT_MESSAGE_HANDLED)) {
            // 看新消息是不是client final message
            ctx.channel().attr(AttributeKey.valueOf("clientState")).set(ClientState.PREPARED_FIRST);
            // 验证通过,写server final message
            ctx.channel().attr(AttributeKey.valueOf("clientState")).set(ClientState.ENDED);
            // 验证不通过
            ctx.channel().attr(AttributeKey.valueOf("clientState")).set(ClientState.INITIAL);
            return;
        }
        if (ctx.channel().attr(AttributeKey.valueOf("clientState")).get().equals(ClientState.ENDED)) {
            // 正式开始发送
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
                producer.sendMessage(topic, msg.toString().getBytes());
            }
            return;
        }
        // 奇怪的状态，认为是初始化中
        ctx.channel().attr(AttributeKey.valueOf("clientState")).set(ClientState.INITIAL);
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
