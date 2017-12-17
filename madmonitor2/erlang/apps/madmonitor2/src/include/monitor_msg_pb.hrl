-ifndef(GENERIC_SUMMARY_PB_H).
-define(GENERIC_SUMMARY_PB_H, true).
-record(generic_summary, {
    load = erlang:error({required, load}),
    systime = erlang:error({required, systime}),
    uptime = erlang:error({required, uptime}),
    tcp_connections = erlang:error({required, tcp_connections}),
    uname
}).
-endif.

-ifndef(FREEBSD_CPU_PB_H).
-define(FREEBSD_CPU_PB_H, true).
-record(freebsd_cpu, {
    user = erlang:error({required, user}),
    nice = erlang:error({required, nice}),
    system = erlang:error({required, system}),
    interrupt = erlang:error({required, interrupt}),
    idle = erlang:error({required, idle})
}).
-endif.

-ifndef(LINUX_CPU_PB_H).
-define(LINUX_CPU_PB_H, true).
-record(linux_cpu, {
    user = erlang:error({required, user}),
    nice = erlang:error({required, nice}),
    system = erlang:error({required, system}),
    iowait = erlang:error({required, iowait}),
    steal = erlang:error({required, steal}),
    idle = erlang:error({required, idle})
}).
-endif.

-ifndef(GENERIC_CPU_PB_H).
-define(GENERIC_CPU_PB_H, true).
-record(generic_cpu, {
    freebsd_cpu,
    linux_cpu
}).
-endif.

-ifndef(FREEBSD_MEM_PB_H).
-define(FREEBSD_MEM_PB_H, true).
-record(freebsd_mem, {
    total = erlang:error({required, total}),
    active = erlang:error({required, active}),
    free = erlang:error({required, free}),
    inactive = erlang:error({required, inactive}),
    wire = erlang:error({required, wire}),
    cached = erlang:error({required, cached})
}).
-endif.

-ifndef(LINUX_MEM_PB_H).
-define(LINUX_MEM_PB_H, true).
-record(linux_mem, {
    total = erlang:error({required, total})
}).
-endif.

-ifndef(GENERIC_MEM_PB_H).
-define(GENERIC_MEM_PB_H, true).
-record(generic_mem, {
    freebsd_mem,
    linux_mem
}).
-endif.

-ifndef(GENERIC_SWAP_PB_H).
-define(GENERIC_SWAP_PB_H, true).
-record(generic_swap, {
    total = erlang:error({required, total}),
    used = erlang:error({required, used})
}).
-endif.

-ifndef(DISK_PARTITION_PB_H).
-define(DISK_PARTITION_PB_H, true).
-record(disk_partition, {
    mounted = erlang:error({required, mounted}),
    capacity = erlang:error({required, capacity}),
    iused = erlang:error({required, iused})
}).
-endif.

-ifndef(DISK_IOSTAT_PB_H).
-define(DISK_IOSTAT_PB_H, true).
-record(disk_iostat, {
    device_name,
    tps
}).
-endif.

-ifndef(GENERIC_DISKS_PB_H).
-define(GENERIC_DISKS_PB_H, true).
-record(generic_disks, {
    disk_partition = [],
    disk_iostat = []
}).
-endif.

-ifndef(GENERIC_PROCESS_PB_H).
-define(GENERIC_PROCESS_PB_H, true).
-record(generic_process, {
    freebsd_process,
    linux_process
}).
-endif.

-ifndef(FREEBSD_PROCESS_PB_H).
-define(FREEBSD_PROCESS_PB_H, true).
-record(freebsd_process, {
    statd = erlang:error({required, statd}),
    stati = erlang:error({required, stati}),
    statl = erlang:error({required, statl}),
    statr = erlang:error({required, statr}),
    stats = erlang:error({required, stats}),
    statt = erlang:error({required, statt}),
    statw = erlang:error({required, statw}),
    statz = erlang:error({required, statz}),
    total = erlang:error({required, total})
}).
-endif.

-ifndef(LINUX_PROCESS_PB_H).
-define(LINUX_PROCESS_PB_H, true).
-record(linux_process, {
    statd = erlang:error({required, statd}),
    statr = erlang:error({required, statr}),
    stats = erlang:error({required, stats}),
    statt = erlang:error({required, statt}),
    statw,
    statx = erlang:error({required, statx}),
    statz = erlang:error({required, statz}),
    total = erlang:error({required, total})
}).
-endif.

-ifndef(NETWORK_INTERFACE_PB_H).
-define(NETWORK_INTERFACE_PB_H, true).
-record(network_interface, {
    ifname,
    ifin,
    ifout
}).
-endif.

-ifndef(GENERIC_NETWORKS_PB_H).
-define(GENERIC_NETWORKS_PB_H, true).
-record(generic_networks, {
    network_interface = []
}).
-endif.

-ifndef(SERVICE_PB_H).
-define(SERVICE_PB_H, true).
-record(service, {
    service_name = erlang:error({required, service_name}),
    service_port = erlang:error({required, service_port}),
    service_status = erlang:error({required, service_status}),
    status_desc
}).
-endif.

-ifndef(GENERIC_SERVICES_PB_H).
-define(GENERIC_SERVICES_PB_H, true).
-record(generic_services, {
    service = []
}).
-endif.

-ifndef(GENERIC_PB_H).
-define(GENERIC_PB_H, true).
-record(generic, {
    summary = erlang:error({required, summary}),
    cpu = erlang:error({required, cpu}),
    mem = erlang:error({required, mem}),
    swap = erlang:error({required, swap}),
    disk = erlang:error({required, disk}),
    process = erlang:error({required, process}),
    network = erlang:error({required, network}),
    service = erlang:error({required, service})
}).
-endif.

-ifndef(MYSQL_SUMMARY_PB_H).
-define(MYSQL_SUMMARY_PB_H, true).
-record(mysql_summary, {
    uptime = erlang:error({required, uptime}),
    threads_created = erlang:error({required, threads_created}),
    slow_queries = erlang:error({required, slow_queries}),
    questions = erlang:error({required, questions}),
    connections = erlang:error({required, connections}),
    cur_connections = erlang:error({required, cur_connections})
}).
-endif.

-ifndef(MYSQL_TRAFFIC_PB_H).
-define(MYSQL_TRAFFIC_PB_H, true).
-record(mysql_traffic, {
    in = erlang:error({required, in}),
    out = erlang:error({required, out})
}).
-endif.

-ifndef(MYSQL_STATEMENT_PB_H).
-define(MYSQL_STATEMENT_PB_H, true).
-record(mysql_statement, {
    delete = erlang:error({required, delete}),
    insert = erlang:error({required, insert}),
    select = erlang:error({required, select}),
    update = erlang:error({required, update})
}).
-endif.

-ifndef(MYSQL_REPLICATION_PB_H).
-define(MYSQL_REPLICATION_PB_H, true).
-record(mysql_replication, {
    onoff = erlang:error({required, onoff})
}).
-endif.

-ifndef(MYSQL_TABLE_PB_H).
-define(MYSQL_TABLE_PB_H, true).
-record(mysql_table, {
    db_name = erlang:error({required, db_name}),
    table_name = erlang:error({required, table_name}),
    engine = erlang:error({required, engine}),
    table_size = erlang:error({required, table_size}),
    rows = erlang:error({required, rows}),
    data_length = erlang:error({required, data_length}),
    index_length = erlang:error({required, index_length}),
    auto_increment = erlang:error({required, auto_increment}),
    update_time = erlang:error({required, update_time}),
    collation = erlang:error({required, collation})
}).
-endif.

-ifndef(MYSQL_TABLES_PB_H).
-define(MYSQL_TABLES_PB_H, true).
-record(mysql_tables, {
    mysql_table = []
}).
-endif.

-ifndef(MYSQL_ROLE_STATUS_PB_H).
-define(MYSQL_ROLE_STATUS_PB_H, true).
-record(mysql_role_status, {
    status = erlang:error({required, status})
}).
-endif.

-ifndef(MYSQL_SECONDS_BEHIND_MASTER_PB_H).
-define(MYSQL_SECONDS_BEHIND_MASTER_PB_H, true).
-record(mysql_seconds_behind_master, {
    sec = erlang:error({required, sec})
}).
-endif.

-ifndef(MYSQL_PB_H).
-define(MYSQL_PB_H, true).
-record(mysql, {
    summary = erlang:error({required, summary}),
    traffic = erlang:error({required, traffic}),
    statement = erlang:error({required, statement}),
    replication = erlang:error({required, replication}),
    tables = erlang:error({required, tables}),
    role_status = erlang:error({required, role_status}),
    seconds_behind_master = erlang:error({required, seconds_behind_master})
}).
-endif.

-ifndef(SERVING_REQUEST_PB_H).
-define(SERVING_REQUEST_PB_H, true).
-record(serving_request, {
    request = erlang:error({required, request})
}).
-endif.

-ifndef(DOMAIN_INFO_PB_H).
-define(DOMAIN_INFO_PB_H, true).
-record(domain_info, {
    domain_id = erlang:error({required, domain_id}),
    adpos_num = erlang:error({required, adpos_num}),
    adcamp_num = erlang:error({required, adcamp_num}),
    deliver_cache_num = erlang:error({required, deliver_cache_num}),
    publish_pkg_serial_num = erlang:error({required, publish_pkg_serial_num}),
    publish_role = erlang:error({required, publish_role})
}).
-endif.

-ifndef(SERVING_ADIMAGE_PB_H).
-define(SERVING_ADIMAGE_PB_H, true).
-record(serving_adimage, {
    domain_info
}).
-endif.

-ifndef(SERVING_LOG_INFO_PB_H).
-define(SERVING_LOG_INFO_PB_H, true).
-record(serving_log_info, {
    total_log = erlang:error({required, total_log}),
    total_camplog = erlang:error({required, total_camplog}),
    sslog_name = erlang:error({required, sslog_name}),
    sslog_md5 = erlang:error({required, sslog_md5})
}).
-endif.

-ifndef(SERVING_TRAFFIC_PB_H).
-define(SERVING_TRAFFIC_PB_H, true).
-record(serving_traffic, {
    traffic = erlang:error({required, traffic})
}).
-endif.

-ifndef(SERVING_ENGINE_STATUS_PB_H).
-define(SERVING_ENGINE_STATUS_PB_H, true).
-record(serving_engine_status, {
    engine_status = erlang:error({required, engine_status})
}).
-endif.

-ifndef(SERVING_PB_H).
-define(SERVING_PB_H, true).
-record(serving, {
    serving_request = erlang:error({required, serving_request}),
    serving_adimage = erlang:error({required, serving_adimage}),
    serving_loginfo = erlang:error({required, serving_loginfo}),
    serving_traffic = erlang:error({required, serving_traffic}),
    serving_engine_status = erlang:error({required, serving_engine_status})
}).
-endif.

-ifndef(DAEMON_WEBSERVER_STATUS_PB_H).
-define(DAEMON_WEBSERVER_STATUS_PB_H, true).
-record(daemon_webserver_status, {
    webserver_status = erlang:error({required, webserver_status})
}).
-endif.

-ifndef(DAEMON_DAEMON_STATUS_PB_H).
-define(DAEMON_DAEMON_STATUS_PB_H, true).
-record(daemon_daemon_status, {
    daemon_status = erlang:error({required, daemon_status})
}).
-endif.

-ifndef(DAEMON_LOGIN_STATUS_PB_H).
-define(DAEMON_LOGIN_STATUS_PB_H, true).
-record(daemon_login_status, {
    login_status = erlang:error({required, login_status})
}).
-endif.

-ifndef(DAEMON_ADSERV_STATUS_PB_H).
-define(DAEMON_ADSERV_STATUS_PB_H, true).
-record(daemon_adserv_status, {
    adserv_status = erlang:error({required, adserv_status})
}).
-endif.

-ifndef(DAEMON_ERRORLOG_STATUS_PB_H).
-define(DAEMON_ERRORLOG_STATUS_PB_H, true).
-record(daemon_errorlog_status, {
    errorlog_status = erlang:error({required, errorlog_status})
}).
-endif.

-ifndef(DAEMON_PB_H).
-define(DAEMON_PB_H, true).
-record(daemon, {
    daemon_webserver_status = erlang:error({required, daemon_webserver_status}),
    daemon_daemon_status = erlang:error({required, daemon_daemon_status}),
    daemon_login_status = erlang:error({required, daemon_login_status}),
    daemon_adserv_status = erlang:error({required, daemon_adserv_status}),
    daemon_errorlog_status = erlang:error({required, daemon_errorlog_status})
}).
-endif.

-ifndef(REPORT_PROCESS_INFO_PB_H).
-define(REPORT_PROCESS_INFO_PB_H, true).
-record(report_process_info, {
    process_info = erlang:error({required, process_info})
}).
-endif.

-ifndef(REPORT_PROCESS_SPEED_PB_H).
-define(REPORT_PROCESS_SPEED_PB_H, true).
-record(report_process_speed, {
    process_speed = erlang:error({required, process_speed})
}).
-endif.

-ifndef(REPORT_WAIT_PROCESS_LOG_NUM_PB_H).
-define(REPORT_WAIT_PROCESS_LOG_NUM_PB_H, true).
-record(report_wait_process_log_num, {
    wait_process_log_num = erlang:error({required, wait_process_log_num})
}).
-endif.

-ifndef(REPORT_PB_H).
-define(REPORT_PB_H, true).
-record(report, {
    process_info = erlang:error({required, process_info}),
    process_speed = erlang:error({required, process_speed}),
    wait_process_log_num = erlang:error({required, wait_process_log_num})
}).
-endif.

-ifndef(MONITOR_MSG_PB_H).
-define(MONITOR_MSG_PB_H, true).
-record(monitor_msg, {
    host_name = erlang:error({required, host_name}),
    client_version = erlang:error({required, client_version}),
    generic = erlang:error({required, generic}),
    mysql,
    serving,
    daemon,
    report
}).
-endif.

