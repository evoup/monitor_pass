package com.evoupsight.monitorpass.server.cfg;

import org.springframework.beans.factory.annotation.Value;
import org.springframework.context.annotation.Configuration;
import org.springframework.context.annotation.PropertySource;
import org.springframework.context.annotation.PropertySources;

@Configuration
@PropertySources({
        @PropertySource("server.properties")
})
public class Config {

    @Value("${zk.servers}")
    String zkServers;

}
