���˵������ص�restful API

���÷�����

inc/const.m��
�޸����³���Ϊʵ�����л�����hbase����
define('__MDB_HOST',        '127.0.0.1'); // TODO ����Ҳ�ĳ���ini���õķ�ʽ 
define('__MDB_PORT',        '9090');
define('__MDB_SENDTIMEOUT', '6000');  //6 seconds
define('__MDB_RECVTIMEOUT', '6000');  //6 seconds
define('__MQ_HOST',         '127.0.0.1');
define('__MQ_PORT',         '6379');

�޸����³���Ϊʵ�����л�����syslog����
/* {{{ syslog(fancility&level)
 */
if (!$conf['debug']) {
    define('__SYSLOG_FACILITY_API', 'LOG_LOCAL4');
} else {
    define('__SYSLOG_FACILITY_API', 'LOG_LOCAL5');
}
//level
define('__SYSLOG_LV_DEBUG',     'LOG_ERR');
/* }}} */
