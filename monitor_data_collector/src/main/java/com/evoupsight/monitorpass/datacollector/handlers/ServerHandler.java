package com.evoupsight.monitorpass.datacollector.handlers;


import com.evoupsight.monitorpass.datacollector.auth.ScramSha1;
import com.evoupsight.monitorpass.datacollector.auth.exception.InvalidProtocolException;
import com.evoupsight.monitorpass.datacollector.domain.HostTemplates;
import com.evoupsight.monitorpass.datacollector.domain.ItemIdItem;
import com.evoupsight.monitorpass.datacollector.domain.ItemSets;
import com.evoupsight.monitorpass.datacollector.domain.TemplateSets;
import com.evoupsight.monitorpass.datacollector.queue.KafkaProducerThread;
import com.evoupsight.monitorpass.datacollector.server.ServerState;
import com.google.gson.Gson;
import com.sun.org.apache.xerces.internal.impl.dv.util.Base64;
import io.netty.buffer.Unpooled;
import io.netty.channel.ChannelFutureListener;
import io.netty.channel.ChannelHandler.Sharable;
import io.netty.channel.ChannelHandlerContext;
import io.netty.channel.ChannelInboundHandlerAdapter;
import io.netty.util.AttributeKey;
import org.apache.commons.lang3.ArrayUtils;
import org.apache.commons.lang3.StringUtils;
import org.apache.kafka.clients.producer.KafkaProducer;
import org.apache.kafka.clients.producer.ProducerRecord;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.beans.factory.annotation.Qualifier;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.stereotype.Component;
import redis.clients.jedis.Jedis;
import redis.clients.jedis.JedisPool;

import java.io.UnsupportedEncodingException;
import java.security.InvalidKeyException;
import java.security.NoSuchAlgorithmException;
import java.security.spec.InvalidKeySpecException;
import java.util.*;
import java.util.concurrent.ExecutorService;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

@Component
@Qualifier("serverHandler")
@Sharable
public class ServerHandler extends ChannelInboundHandlerAdapter {
    private static final Logger LOG = LoggerFactory.getLogger(ServerHandler.class);
    @Autowired
    @Qualifier("new_kafka_producer_pool")
    ExecutorService kafkaProducerPool;
    @Autowired
    @Qualifier("new_kafka_producer")
    KafkaProducer<String, String> kafkaProducer;

    @Value("${kafka.brokers}")
    String brokers;
    @Value("${kafka.topic}")
    String topic;
    @Autowired
    protected JedisPool jedisPool;

    private static final Pattern
            CLIENT_FIRST_MESSAGE = Pattern.compile("^(([pny])=?([^,]*),([^,]*),)(m?=?[^,]*,?n=([^,]*),r=([^,]*),?.*)$");
    //    private static final Pattern
//            CLIENT_FINAL_MESSAGE = Pattern.compile("(c=([^,]*),r=([^,]*)),p=(.*)$");
    private static final Pattern
            CLIENT_GET_CONF_MESSAGE = Pattern.compile("getconf\\|(.*)");

    @Override
    public void channelReadComplete(ChannelHandlerContext ctx) throws Exception {
        ctx.writeAndFlush(Unpooled.EMPTY_BUFFER).addListener(ChannelFutureListener.CLOSE_ON_FAILURE);
    }

    @Override
    public void channelRead(ChannelHandlerContext ctx, Object msg) throws Exception {
        System.out.println("client message:" + msg);

        if (dispatchClientConfig(ctx, msg)) {
            return;
        }

        if (!ctx.channel().hasAttr(AttributeKey.valueOf("clientId")) ||
                ctx.channel().attr(AttributeKey.valueOf("serverState")).get().equals(ServerState.INITIAL)) {
            // 看消息是不是client first message
            // 消息格式为n,,n=clientName,r=oJnNPGsiuz
            String clientName;
            Matcher m = CLIENT_FIRST_MESSAGE.matcher(msg.toString());
            if (!m.matches()) {
                ctx.channel().write("invalid protocol\n");
                return;
            }
            String clientFirstMessageBare = m.group(5);
            clientName = m.group(6);
            String clientNonce = m.group(7);
            ctx.channel().attr(AttributeKey.valueOf("clientFirstMessageBare")).set(clientFirstMessageBare);
            ctx.channel().attr(AttributeKey.valueOf("clientId")).set(clientName);
            ctx.channel().attr(AttributeKey.valueOf("clientNonce")).set(clientNonce);
            ctx.channel().attr(AttributeKey.valueOf("serverState")).set(ServerState.FIRST_CLIENT_MESSAGE_HANDLED);
            // 写server first message
            String serverNonce = UUID.randomUUID().toString();
            String salt = UUID.randomUUID().toString();
            String saltBase64 = Base64.encode(salt.getBytes());
            String iterations = "4096";
            ctx.channel().attr(AttributeKey.valueOf("serverNonce")).set(serverNonce);
            ctx.channel().attr(AttributeKey.valueOf("salt")).set(saltBase64);
            ctx.channel().attr(AttributeKey.valueOf("iterations")).set(iterations);
            StringBuffer sb = new StringBuffer();
            sb.append("r=").append(clientNonce).append(serverNonce).append(",s=").append(saltBase64).append(",i=").append(iterations);
            ctx.channel().attr(AttributeKey.valueOf("serverFirstMessage")).set(sb);
            ctx.channel().write(sb);
            ctx.write(sb);
            return;
        }
        if (ctx.channel().attr(AttributeKey.valueOf("serverState")).get().equals(ServerState.FIRST_CLIENT_MESSAGE_HANDLED)) {
            // 看新消息是不是client final message
            // 消息格式为c=biws,r=oJnNPGsiuz07eae15f-609a-420a-bee3-10676c383a78,p=p4TLaQoE9WjA/upN5Ns/o9gc5Mk=
            ctx.channel().attr(AttributeKey.valueOf("serverState")).set(ServerState.PREPARED_FIRST);
            StringBuilder nonce = new StringBuilder();
            Object clientNonce = ctx.channel().attr(AttributeKey.valueOf("clientNonce")).get();
            nonce.append(clientNonce);
            Object serverNonce = ctx.channel().attr(AttributeKey.valueOf("serverNonce")).get();
            nonce.append(serverNonce);
            // 验证通过,写server final message
            ScramSha1 scramSha1 = new ScramSha1();
            scramSha1.setcName(ctx.channel().attr(AttributeKey.valueOf("clientId")).get().toString());
            scramSha1.setmNonce(nonce.toString());
            scramSha1.setcNonce(clientNonce.toString());
            scramSha1.setsNonce(serverNonce.toString());
            scramSha1.setSalt(ctx.channel().attr(AttributeKey.valueOf("salt")).get().toString());
            scramSha1.setIterations(ctx.channel().attr(AttributeKey.valueOf("iterations")).get().toString());
            scramSha1.setmClientFirstMessageBare(ctx.channel().attr(AttributeKey.valueOf("clientFirstMessageBare")).get().toString());
            scramSha1.setmServerFirstMessage(ctx.channel().attr(AttributeKey.valueOf("serverFirstMessage")).get().toString());
            String serverFinalMessage;
            try {
                serverFinalMessage = scramSha1.prepareFinalMessage(msg.toString());
                ctx.channel().write(serverFinalMessage);
                ctx.write(serverFinalMessage);
            } catch (InvalidProtocolException | InvalidKeySpecException | NoSuchAlgorithmException |
                    UnsupportedEncodingException | InvalidKeyException e) {
                LOG.error(e.getMessage(), e);
                // 验证不通过
                ctx.channel().write("invalid protocol\n");
                ctx.channel().attr(AttributeKey.valueOf("serverState")).set(ServerState.INITIAL);
                return;
            }
            ctx.channel().attr(AttributeKey.valueOf("serverState")).set(ServerState.ENDED);
            return;
        }
        if (ctx.channel().attr(AttributeKey.valueOf("serverState")).get().equals(ServerState.ENDED)) {
            // 正式开始发送
            LOG.debug("got a message here");
            String msgstr = msg.toString();
            if (StringUtils.isNotEmpty(msgstr)) {
                String[] m = msgstr.split("\n");
                if (ArrayUtils.isNotEmpty(m)) {
                    ProducerRecord<String, String> record = null;
                    for (String s : m) {
                        if (StringUtils.isNotEmpty(s)) {
                            s = s + " host=" + ctx.channel().attr(AttributeKey.valueOf("clientId")).get().toString();
                            record = new ProducerRecord<>(topic, s);
                            kafkaProducerPool.submit(new KafkaProducerThread(kafkaProducer, record));
                        }
                    }
                }
            }
            return;
        }
        // 奇怪的状态，认为是初始化中
        ctx.channel().attr(AttributeKey.valueOf("serverState")).set(ServerState.INITIAL);
    }

    /**
     * 分发客户端配置文件
     * @param ctx
     * @param msg
     * @return
     */
    private boolean dispatchClientConfig(ChannelHandlerContext ctx, Object msg) {
        if (msg != null) {
            Matcher matcher = CLIENT_GET_CONF_MESSAGE.matcher(msg.toString());
            String confHostName = "";
            while (matcher.find()) {
                int counter = matcher.groupCount();
                if (counter != 1) {
                    ctx.channel().write("get conf err, protocol error\n");
                    break;
                }
                confHostName = matcher.group(1);
                // 找到就退出防止死循环
                break;
            }
            System.out.println("hostName:" + confHostName);
            if (StringUtils.isNotEmpty(confHostName)) {
                HostTemplates hostTemplates;
                TemplateSets templateSets;
                ItemSets itemSets = null;
                ItemIdItem itemIdItem = null;
                Jedis resource = null;
                try {
                    resource = jedisPool.getResource();
                    String value1 = resource.get("key1");
                    hostTemplates = new Gson().fromJson(value1, HostTemplates.class);
                    String value2 = resource.get("key2");
                    templateSets = new Gson().fromJson(value2, TemplateSets.class);
                    String value3 = resource.get("key3");
                    itemSets = new Gson().fromJson(value3, ItemSets.class);
                    String value4 = resource.get("key4");
                    itemIdItem = new Gson().fromJson(value4, ItemIdItem.class);
                    // 返回以host为key，旗下若干item为value的map作为配置文件下发
                    if (hostTemplates != null) {
                        Set<Map.Entry<String, String[]>> entries = hostTemplates.entrySet();
                        for (Map.Entry<String, String[]> entry : entries) {
                            String hostName = entry.getKey();
                            String[] templateIds = entry.getValue();
                            for (String templateId : templateIds) {
                                HashSet<String> sets = templateSets.get(templateId);
                                if (sets != null) {
                                    for (String setId : sets) {
                                        System.out.println("HostName:" + hostName + " setId:" + setId);
                                        if (hostName.equals(confHostName)) {
                                            System.out.println("found!");
                                        }
                                    }
                                }
                            }
                        }
                    }
                    System.out.println(value1);
                    // 返回客户端配置信息
                    ctx.channel().write(value1);
                    ctx.channel().close();
                } catch (Exception ex) {
                    ex.printStackTrace();
                } finally {
                    jedisPool.returnResource(resource);
                }
                return true;
            }
        }
        return false;
    }

    @Override
    public void channelActive(ChannelHandlerContext ctx) throws Exception {
        LOG.debug("Channel is active");
        super.channelActive(ctx);
    }

    @Override
    public void channelInactive(ChannelHandlerContext ctx) throws Exception {
        LOG.debug("Channel is disconnected");
        //NettyChannelMap.remove((SocketChannel) ctx.channel());
        super.channelInactive(ctx);
    }

}
