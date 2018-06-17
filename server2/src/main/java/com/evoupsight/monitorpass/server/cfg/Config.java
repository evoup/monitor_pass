package com.evoupsight.monitorpass.server.cfg;

import org.springframework.context.annotation.Configuration;
import org.springframework.context.annotation.PropertySource;
import org.springframework.context.annotation.PropertySources;


/**
 * @author evoup
 */
@Configuration
@PropertySources({
        @PropertySource("server.properties")
})
public class Config {


}
