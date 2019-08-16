package com.evoupsight.monitorpass.datacollector.handlers;


import com.evoupsight.monitorpass.datacollector.auth.ScramSha1;
import com.evoupsight.monitorpass.datacollector.auth.exception.InvalidProtocolException;
import com.evoupsight.monitorpass.datacollector.domain.*;
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
import java.net.InetSocketAddress;
import java.security.InvalidKeyException;
import java.security.NoSuchAlgorithmException;
import java.security.spec.InvalidKeySpecException;
import java.util.*;
import java.util.concurrent.ExecutorService;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

/**
 * @author evoup
 */
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
    private static final Pattern
            CLIENT_GET_CONF_MESSAGE = Pattern.compile("getconf\\|(.*)");

    @Override
    public void channelReadComplete(ChannelHandlerContext ctx) {
        ctx.writeAndFlush(Unpooled.EMPTY_BUFFER).addListener(ChannelFutureListener.CLOSE_ON_FAILURE);
    }

    @Override
    public void channelRead(ChannelHandlerContext ctx, Object msg) throws Exception {
        System.out.println("client message:" + msg);
        /**
         * 分发方式有问题，应该是数据收集器直接请求到监控代理的端口，一次性下发。
         if (dispatchClientConfig(ctx, msg)) {
         return;
         }*/

        if (!ctx.channel().hasAttr(AttributeKey.valueOf("clientId")) ||
                ctx.channel().attr(AttributeKey.valueOf("serverState")).get().equals(ServerState.INITIAL)) {
            // 看消息是不是client first message
            // 消息格式为n,,n=clientName,r=oJnNPGsiuz
            String clientName;
            Matcher m = CLIENT_FIRST_MESSAGE.matcher(msg.toString());
            if (!m.matches()) {
                LOG.error("invalid protocol:" + msg.toString());
                ctx.channel().write("invalid protocol\n");
                ctx.channel().close();
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
                            s = s + " host=" + ctx.channel().attr(AttributeKey.valueOf("clientId")).get().toString()
                                    + " ip=" + ((InetSocketAddress)ctx.channel().remoteAddress()).getAddress().getHostAddress();
                            record = new ProducerRecord<>(topic, s);
                            kafkaProducerPool.submit(new KafkaProducerThread(kafkaProducer, record));
                        }
                    }
                }
            }
            return;
        }
        // 奇怪的状态，认为是初始化中
        LOG.warn("strange status, set to initial");
        ctx.channel().attr(AttributeKey.valueOf("serverState")).set(ServerState.INITIAL);
    }

    /**
     * 分发客户端配置文件
     *
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
            if (StringUtils.isNotEmpty(confHostName)) {
                HostTemplates hostTemplates;
                TemplateSets templateSets;
                ItemSets itemSets = null;
                ItemIdItem itemIdItem = null;
                Jedis redis = null;
                try {
                    redis = jedisPool.getResource();
                    String value1 = redis.get("key1");
                    hostTemplates = new Gson().fromJson(value1, HostTemplates.class);
                    String value2 = redis.get("key2");
                    templateSets = new Gson().fromJson(value2, TemplateSets.class);
                    String value3 = redis.get("key3");
                    itemSets = new Gson().fromJson(value3, ItemSets.class);
                    String value4 = redis.get("key4");
                    itemIdItem = new Gson().fromJson(value4, ItemIdItem.class);
                    // 返回以host为key，旗下若干item为value的map作为配置文件下发
                    HashSet<String> matchItems = new HashSet<>();
                    if (hostTemplates != null) {
                        Set<Map.Entry<String, String[]>> entries = hostTemplates.entrySet();
                        for (Map.Entry<String, String[]> entry : entries) {
                            String hostName = entry.getKey();
                            String[] templateIds = entry.getValue();
                            for (String templateId : templateIds) {
                                HashSet<String> sets = templateSets.get(templateId);
                                if (sets != null) {
                                    for (String setId : sets) {
                                        if (hostName.equals(confHostName)) {
                                            Set<Map.Entry<String, HashSet<String>>> entriesItemSets = itemSets.entrySet();
                                            for (Map.Entry<String, HashSet<String>> entriesItemSet : entriesItemSets) {
                                                HashSet<String> mySets = entriesItemSet.getValue();
                                                if (mySets != null) {
                                                    for (String mySet : mySets) {
                                                        if (mySet.equals(setId)) {
                                                            matchItems.add(entriesItemSet.getKey());
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    ArrayList<Item> items = new ArrayList<>();
                    for (String matchItem : matchItems) {
                        Item item = itemIdItem.get(matchItem);
                        items.add(item);
                    }
                    String itemsJson = new Gson().toJson(items);
                    // 返回客户端配置信息
                    ctx.channel().writeAndFlush(itemsJson).sync();
                    ctx.channel().close();
                } catch (Exception ex) {
                    ex.printStackTrace();
                } finally {
                    if (redis != null) {
                        redis.close();
                    }
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
