package com.evoupsight.monitorpass.datacollector.cfg;


import com.evoupsight.monitorpass.datacollector.handlers.StringProtocolInitalizer;
import com.google.common.cache.CacheBuilder;
import com.google.common.cache.CacheLoader;
import com.google.common.cache.LoadingCache;
import io.netty.bootstrap.ServerBootstrap;
import io.netty.channel.ChannelOption;
import io.netty.channel.nio.NioEventLoopGroup;
import io.netty.channel.socket.nio.NioServerSocketChannel;
import io.netty.handler.codec.string.StringDecoder;
import io.netty.handler.codec.string.StringEncoder;
import org.apache.kafka.clients.producer.KafkaProducer;
import org.apache.kafka.clients.producer.ProducerConfig;
import org.apache.kafka.common.serialization.StringSerializer;
import org.opentsdb.client.PoolingHttpClient;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.beans.factory.annotation.Qualifier;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.context.annotation.*;
import org.springframework.context.support.PropertySourcesPlaceholderConfigurer;
import redis.clients.jedis.JedisPool;
import redis.clients.jedis.JedisPoolConfig;
import redis.clients.jedis.Protocol;

import java.net.InetSocketAddress;
import java.net.URI;
import java.net.URISyntaxException;
import java.util.HashMap;
import java.util.Map;
import java.util.Properties;
import java.util.Set;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;


/**
 * This class contains the bean definitions for this netty server. These beans
 * are autowired into the respective java classes in org.nerdronix.* packages
 * using component scan feature of spring. Properties are injected using the
 * PropertySource. For more information look <a href=
 * "http://static.springsource.org/spring/docs/3.2.2.RELEASE/javadoc-api/org/springframework/context/annotation/Configuration.html"
 * >here</a> and <a href=
 * "http://blog.springsource.com/2011/06/10/spring-3-1-m2-configuration-enhancements/"
 * >here</a>
 *
 * @author Abraham Menacherry
 */
@Configuration
@ComponentScan("com.evoupsight")
@PropertySources({
        @PropertySource("classpath:netty-server.properties"),
        @PropertySource("data-collector.properties"),
        @PropertySource("kafka-client.properties")
})
public class SpringConfig {

    @Value("${boss.thread.count}")
    private int bossCount;

    @Value("${worker.thread.count}")
    private int workerCount;

    @Value("${tcp.port}")
    private int tcpPort;

    @Value("${so.keepalive}")
    private boolean keepAlive;

    @Value("${so.backlog}")
    private int backlog;

    @Value("${log4j.configuration}")
    private String log4jConfiguration;

    @Value("${kafka.brokers}")
    String brokers;

//	@Value("${kafka.topic}")
//	String topic;

    @Value("${kafka.groupId}")
    String groupId;

    @Value("${redis.host}")
    String redisHost;

    @Autowired
    @Qualifier("springProtocolInitializer")
    private StringProtocolInitalizer protocolInitalizer;

    @SuppressWarnings("unchecked")
    @Bean(name = "serverBootstrap")
    public ServerBootstrap bootstrap() {
        ServerBootstrap b = new ServerBootstrap();
        b.group(bossGroup(), workerGroup())
                .channel(NioServerSocketChannel.class)
                .childHandler(protocolInitalizer);
        Map<ChannelOption<?>, Object> tcpChannelOptions = tcpChannelOptions();
        Set<ChannelOption<?>> keySet = tcpChannelOptions.keySet();
        for (@SuppressWarnings("rawtypes")
                ChannelOption option : keySet) {
            b.option(option, tcpChannelOptions.get(option));
        }
        return b;
    }

    @Bean(name = "bossGroup", destroyMethod = "shutdownGracefully")
    public NioEventLoopGroup bossGroup() {
        return new NioEventLoopGroup(bossCount);
    }

    @Bean(name = "workerGroup", destroyMethod = "shutdownGracefully")
    public NioEventLoopGroup workerGroup() {
        return new NioEventLoopGroup(workerCount);
    }

    @Bean(name = "tcpSocketAddress")
    public InetSocketAddress tcpPort() {
        return new InetSocketAddress(tcpPort);
    }

    @Bean(name = "tcpChannelOptions")
    public Map<ChannelOption<?>, Object> tcpChannelOptions() {
        Map<ChannelOption<?>, Object> options = new HashMap<ChannelOption<?>, Object>();
        options.put(ChannelOption.SO_KEEPALIVE, keepAlive);
        options.put(ChannelOption.SO_BACKLOG, backlog);
        return options;
    }

    @Bean(name = "stringEncoder")
    public StringEncoder stringEncoder() {
        return new StringEncoder();
    }

    @Bean(name = "stringDecoder")
    public StringDecoder stringDecoder() {
        return new StringDecoder();
    }

    /**
     * Necessary to make the Value annotations work.
     *
     * @return
     */
    @Bean
    public static PropertySourcesPlaceholderConfigurer propertyPlaceholderConfigurer() {
        return new PropertySourcesPlaceholderConfigurer();
    }


    @Bean(name = "new_kafka_producer")
    public KafkaProducer<String, String> producer() {
        Properties props = new Properties();
        props.put("bootstrap.servers", brokers);
        props.put(ProducerConfig.KEY_SERIALIZER_CLASS_CONFIG, StringSerializer.class.getName());
        props.put(ProducerConfig.VALUE_SERIALIZER_CLASS_CONFIG, StringSerializer.class.getName());
        props.put("group.id", groupId);
        props.put("enable.auto.commit", false);
        props.put("auto.offset.reset", "earliest");
        props.put("heartbeat.interval.ms", 3000);
        props.put("session.timeout.ms", 30000);
        return new KafkaProducer<String, String>(props);
    }

    @Bean(name = "new_kafka_producer_pool")
    public ExecutorService kafkaExecutorService() {
        return Executors.newFixedThreadPool(5);
    }

    @Bean(name = "http_client_pool")
    public PoolingHttpClient poolingHttpClient() {
        return new PoolingHttpClient();
    }


    @Bean
    public JedisPool getJedisPool() {
        try {
            URI jedisURI = new URI(redisHost);
            return new JedisPool(new JedisPoolConfig(), jedisURI.getHost(),
                    jedisURI.getPort(), Protocol.DEFAULT_TIMEOUT, null);
        } catch (URISyntaxException e) {
            throw new RuntimeException(
                    "Redis couldn't be configured from URL in REDISTOGO_URL env var:"
                            + redisHost);
        }
    }

//    @Bean
//    public org.apache.hadoop.conf.Configuration hbaseConf() {
//        org.apache.hadoop.conf.Configuration config = HBaseConfiguration.create();
//        ClassLoader classLoader = this.getClass().getClassLoader();
//        URL resource = classLoader.getResource("hbase-site.xml");
//        if (resource != null) {
//            String path = resource.getPath();
//            config.addResource(new Path(path));
//            return config;
//        }
//        throw new RuntimeException("can not load hbase config");
//    }


    @Bean(name = "jmxExecutorServiceThreadPool")
    public ExecutorService jmxExecutorServiceThreadPool() {
        return Executors.newFixedThreadPool(5);
    }

    @Bean(name = "snmpExecutorServiceThreadPool")
    public ExecutorService snmpExecutorServiceThreadPool() {
        return Executors.newFixedThreadPool(5);
    }

    @Bean(name = "guava_cache")
    public LoadingCache<String, String> guavaCacheBean() {
        CacheLoader<String, String> loader;
        loader = new CacheLoader<String, String>() {
            @Override
            public String load(String key) {
                return key.toUpperCase();
            }
        };
        return CacheBuilder.newBuilder().build(loader);
    }
}
