package com.evoupsight.monitorpass.datacollector.cfg;

import com.evoupsight.monitorpass.datacollector.utils.DbUtils;


import org.apache.commons.dbcp2.BasicDataSource;
import org.apache.ibatis.session.SqlSessionFactory;
import org.mybatis.spring.SqlSessionFactoryBean;
import org.mybatis.spring.SqlSessionTemplate;
import org.mybatis.spring.annotation.MapperScan;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.context.annotation.PropertySource;
import org.springframework.core.io.ClassPathResource;
import org.springframework.core.io.Resource;
import org.springframework.core.io.support.PathMatchingResourcePatternResolver;
import org.springframework.core.io.support.ResourcePatternResolver;
import org.springframework.jdbc.core.JdbcTemplate;
import org.springframework.jdbc.datasource.DataSourceTransactionManager;
import org.springframework.transaction.PlatformTransactionManager;
import org.springframework.transaction.annotation.EnableTransactionManagement;


import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;

/**
 * @author evoup
 */
@Configuration
@EnableTransactionManagement
@PropertySource(value = {"classpath:jdbc.properties", "file:${external_conf}/jdbc.properties"}, ignoreResourceNotFound = true)
@MapperScan(basePackages = {"com.evoupsight.monitorpass.datacollector.dao.mapper"},sqlSessionFactoryRef = "sqlSessionFactoryBean")
public class JdbcConfig {

    @Value("${db.driver}")
    private String driver;

    @Value("${jdbc.url}")
    private String url;

    @Value("${jdbc.username}")
    private String username;

    @Value("${jdbc.password}")
    private String password;

    @Bean
    public BasicDataSource dataSource() {
        return new DbUtils().getBasicDataSource(driver, url, username, password);
    }

    @Bean(name = "sqlSessionFactoryBean")
    public SqlSessionFactory sqlSessionFactoryBean() throws Exception {
        SqlSessionFactoryBean session = new SqlSessionFactoryBean();

        ClassPathResource configLocation = new ClassPathResource("mybatis.xml");
        session.setConfigLocation(configLocation);

        Resource[] mapperResources = loadAllResourceMappers();
        session.setMapperLocations(mapperResources);
        session.setDataSource(dataSource());
        session.setTypeAliasesPackage("com.evoupsight.monitorpass.datacollector.dao.model");
        return session.getObject();
    }


    /**
     * 扫描sqlMapper
     *
     * @return
     * @throws Exception
     */
    private Resource[] loadAllResourceMappers() throws Exception {
        ResourcePatternResolver resourcePatternResolver = new PathMatchingResourcePatternResolver();
        Resource[] mapperResources = resourcePatternResolver.getResources("classpath*:mappers/*.xml");

        List<Resource> list = new ArrayList<>(Arrays.asList(mapperResources));
        return list.toArray(new Resource[]{});
    }

    @Bean(name = "sqlSession")
    public SqlSessionTemplate SqlSessionTemplate() throws Exception {
        return new SqlSessionTemplate(sqlSessionFactoryBean());
    }

    /**
     * 事务 默认事务的bean name即transactionManager
     *
     * @return
     */
    @Bean
    public PlatformTransactionManager transactionManager() {
        return new DataSourceTransactionManager(dataSource());
    }

    @Bean(name = "jdbcTemplate")
    public JdbcTemplate jdbcTemplate() {
        return new JdbcTemplate(dataSource());
    }

}
