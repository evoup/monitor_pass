项目说明：
    站点测速API。由监控系统收集统计url测速数据存入hbase和redis之后，该api从redis获取数据返回给调用段。
    hbase和监控系统整合需要考虑，redis则为了性能考虑另外写一份提供报表数据。

配置方法：
    conf/api.conf文件
    debug是否为调试，线上版本直接设置为false就可以了。
    syslog_str默认为local2.debug，可以按照需要自行设置。
    mdb_host为hbase的IP
    mdb_ip为hbase的端口
    redis_host为redis的IP
    redis_port为redis的端口
    services默认为speed,testspeed_site，就是让speed和testspeed_site2个接口启用。可以根据部署需要删减。
