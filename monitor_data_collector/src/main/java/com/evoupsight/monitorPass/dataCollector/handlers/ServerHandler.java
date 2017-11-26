package com.evoupsight.monitorPass.dataCollector.handlers;

import com.evoupsight.monitorPass.dataCollector.server.Main;
import io.netty.channel.ChannelHandler.Sharable;
import io.netty.channel.ChannelHandlerContext;
import io.netty.channel.SimpleChannelInboundHandler;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Qualifier;
import org.springframework.stereotype.Component;

@Component
@Qualifier("serverHandler")
@Sharable
public class ServerHandler extends SimpleChannelInboundHandler<String> {
	private static final Logger LOG = LoggerFactory.getLogger(ServerHandler.class);
	@Override
	public void channelRead0(ChannelHandlerContext ctx, String msg)
			throws Exception {
		System.out.print(msg);
		LOG.debug("got a message here");
		ctx.channel().writeAndFlush(msg);
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
