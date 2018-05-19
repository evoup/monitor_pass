package com.evoupsight.monitorpass.datacollector.server;

import io.netty.bootstrap.ServerBootstrap;
import io.netty.channel.ChannelFuture;
import io.netty.channel.ChannelOption;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.beans.factory.annotation.Qualifier;
import org.springframework.stereotype.Component;

import javax.annotation.PostConstruct;
import javax.annotation.PreDestroy;
import java.net.InetSocketAddress;

@Component
public class TCPServer {

	@Autowired
	@Qualifier("serverBootstrap")
	private ServerBootstrap bootstrap;
	
	@Autowired
	@Qualifier("tcpSocketAddress")
	private InetSocketAddress tcpPort;

	private ChannelFuture serverChannelFuture;

	@PostConstruct
	public void start() throws Exception {
		System.out.println("Starting server at " + tcpPort);
		bootstrap.option(ChannelOption.SO_BACKLOG, 128);
		bootstrap.option(ChannelOption.TCP_NODELAY, true);
		bootstrap.childOption(ChannelOption.SO_KEEPALIVE, true);
		serverChannelFuture = bootstrap.bind(tcpPort).sync();
	}

	@PreDestroy
	public void stop() throws Exception {
	    serverChannelFuture.channel().closeFuture().sync();
	}

	public ServerBootstrap getBootstrap() {
		return bootstrap;
	}

	public void setBootstrap(ServerBootstrap bootstrap) {
		this.bootstrap = bootstrap;
	}

	public InetSocketAddress getTcpPort() {
		return tcpPort;
	}

	public void setTcpPort(InetSocketAddress tcpPort) {
		this.tcpPort = tcpPort;
	}

}
