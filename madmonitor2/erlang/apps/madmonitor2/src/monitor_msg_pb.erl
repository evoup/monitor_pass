-file("src/monitor_msg_pb.erl", 1).

-module(monitor_msg_pb).

-export([encode_monitor_msg/1, decode_monitor_msg/1,
	 encode_report/1, decode_report/1,
	 encode_report_wait_process_log_num/1,
	 decode_report_wait_process_log_num/1,
	 encode_report_process_speed/1,
	 decode_report_process_speed/1,
	 encode_report_process_info/1,
	 decode_report_process_info/1, encode_daemon/1,
	 decode_daemon/1, encode_daemon_errorlog_status/1,
	 decode_daemon_errorlog_status/1,
	 encode_daemon_adserv_status/1,
	 decode_daemon_adserv_status/1,
	 encode_daemon_login_status/1,
	 decode_daemon_login_status/1,
	 encode_daemon_daemon_status/1,
	 decode_daemon_daemon_status/1,
	 encode_daemon_webserver_status/1,
	 decode_daemon_webserver_status/1, encode_serving/1,
	 decode_serving/1, encode_serving_engine_status/1,
	 decode_serving_engine_status/1,
	 encode_serving_traffic/1, decode_serving_traffic/1,
	 encode_serving_log_info/1, decode_serving_log_info/1,
	 encode_serving_adimage/1, decode_serving_adimage/1,
	 encode_domain_info/1, decode_domain_info/1,
	 encode_serving_request/1, decode_serving_request/1,
	 encode_mysql/1, decode_mysql/1,
	 encode_mysql_seconds_behind_master/1,
	 decode_mysql_seconds_behind_master/1,
	 encode_mysql_role_status/1, decode_mysql_role_status/1,
	 encode_mysql_tables/1, decode_mysql_tables/1,
	 encode_mysql_table/1, decode_mysql_table/1,
	 encode_mysql_replication/1, decode_mysql_replication/1,
	 encode_mysql_statement/1, decode_mysql_statement/1,
	 encode_mysql_traffic/1, decode_mysql_traffic/1,
	 encode_mysql_summary/1, decode_mysql_summary/1,
	 encode_generic/1, decode_generic/1,
	 encode_generic_services/1, decode_generic_services/1,
	 encode_service/1, decode_service/1,
	 encode_generic_networks/1, decode_generic_networks/1,
	 encode_network_interface/1, decode_network_interface/1,
	 encode_linux_process/1, decode_linux_process/1,
	 encode_freebsd_process/1, decode_freebsd_process/1,
	 encode_generic_process/1, decode_generic_process/1,
	 encode_generic_disks/1, decode_generic_disks/1,
	 encode_disk_iostat/1, decode_disk_iostat/1,
	 encode_disk_partition/1, decode_disk_partition/1,
	 encode_generic_swap/1, decode_generic_swap/1,
	 encode_generic_mem/1, decode_generic_mem/1,
	 encode_linux_mem/1, decode_linux_mem/1,
	 encode_freebsd_mem/1, decode_freebsd_mem/1,
	 encode_generic_cpu/1, decode_generic_cpu/1,
	 encode_linux_cpu/1, decode_linux_cpu/1,
	 encode_freebsd_cpu/1, decode_freebsd_cpu/1,
	 encode_generic_summary/1, decode_generic_summary/1]).

-export([has_extension/2, extension_size/1,
	 get_extension/2, set_extension/3]).

-export([decode_extensions/1]).

-export([encode/1, decode/2]).

-record(monitor_msg,
	{host_name, client_version, generic, mysql, serving,
	 daemon, report}).

-record(report,
	{process_info, process_speed, wait_process_log_num}).

-record(report_wait_process_log_num,
	{wait_process_log_num}).

-record(report_process_speed, {process_speed}).

-record(report_process_info, {process_info}).

-record(daemon,
	{daemon_webserver_status, daemon_daemon_status,
	 daemon_login_status, daemon_adserv_status,
	 daemon_errorlog_status}).

-record(daemon_errorlog_status, {errorlog_status}).

-record(daemon_adserv_status, {adserv_status}).

-record(daemon_login_status, {login_status}).

-record(daemon_daemon_status, {daemon_status}).

-record(daemon_webserver_status, {webserver_status}).

-record(serving,
	{serving_request, serving_adimage, serving_loginfo,
	 serving_traffic, serving_engine_status}).

-record(serving_engine_status, {engine_status}).

-record(serving_traffic, {traffic}).

-record(serving_log_info,
	{total_log, total_camplog, sslog_name, sslog_md5}).

-record(serving_adimage, {domain_info}).

-record(domain_info,
	{domain_id, adpos_num, adcamp_num, deliver_cache_num,
	 publish_pkg_serial_num, publish_role}).

-record(serving_request, {request}).

-record(mysql,
	{summary, traffic, statement, replication, tables,
	 role_status, seconds_behind_master}).

-record(mysql_seconds_behind_master, {sec}).

-record(mysql_role_status, {status}).

-record(mysql_tables, {mysql_table}).

-record(mysql_table,
	{db_name, table_name, engine, table_size, rows,
	 data_length, index_length, auto_increment, update_time,
	 collation}).

-record(mysql_replication, {onoff}).

-record(mysql_statement,
	{delete, insert, select, update}).

-record(mysql_traffic, {in, out}).

-record(mysql_summary,
	{uptime, threads_created, slow_queries, questions,
	 connections, cur_connections}).

-record(generic,
	{summary, cpu, mem, swap, disk, process, network,
	 service}).

-record(generic_services, {service}).

-record(service,
	{service_name, service_port, service_status,
	 status_desc}).

-record(generic_networks, {network_interface}).

-record(network_interface, {ifname, ifin, ifout}).

-record(linux_process,
	{statd, statr, stats, statt, statw, statx, statz,
	 total}).

-record(freebsd_process,
	{statd, stati, statl, statr, stats, statt, statw, statz,
	 total}).

-record(generic_process,
	{freebsd_process, linux_process}).

-record(generic_disks, {disk_partition, disk_iostat}).

-record(disk_iostat, {device_name, tps}).

-record(disk_partition, {mounted, capacity, iused}).

-record(generic_swap, {total, used}).

-record(generic_mem, {freebsd_mem, linux_mem}).

-record(linux_mem, {total}).

-record(freebsd_mem,
	{total, active, free, inactive, wire, cached}).

-record(generic_cpu, {freebsd_cpu, linux_cpu}).

-record(linux_cpu,
	{user, nice, system, iowait, steal, idle}).

-record(freebsd_cpu,
	{user, nice, system, interrupt, idle}).

-record(generic_summary,
	{load, systime, uptime, tcp_connections, uname}).

encode(Record) -> encode(element(1, Record), Record).

encode_monitor_msg(Record)
    when is_record(Record, monitor_msg) ->
    encode(monitor_msg, Record).

encode_report(Record) when is_record(Record, report) ->
    encode(report, Record).

encode_report_wait_process_log_num(Record)
    when is_record(Record, report_wait_process_log_num) ->
    encode(report_wait_process_log_num, Record).

encode_report_process_speed(Record)
    when is_record(Record, report_process_speed) ->
    encode(report_process_speed, Record).

encode_report_process_info(Record)
    when is_record(Record, report_process_info) ->
    encode(report_process_info, Record).

encode_daemon(Record) when is_record(Record, daemon) ->
    encode(daemon, Record).

encode_daemon_errorlog_status(Record)
    when is_record(Record, daemon_errorlog_status) ->
    encode(daemon_errorlog_status, Record).

encode_daemon_adserv_status(Record)
    when is_record(Record, daemon_adserv_status) ->
    encode(daemon_adserv_status, Record).

encode_daemon_login_status(Record)
    when is_record(Record, daemon_login_status) ->
    encode(daemon_login_status, Record).

encode_daemon_daemon_status(Record)
    when is_record(Record, daemon_daemon_status) ->
    encode(daemon_daemon_status, Record).

encode_daemon_webserver_status(Record)
    when is_record(Record, daemon_webserver_status) ->
    encode(daemon_webserver_status, Record).

encode_serving(Record)
    when is_record(Record, serving) ->
    encode(serving, Record).

encode_serving_engine_status(Record)
    when is_record(Record, serving_engine_status) ->
    encode(serving_engine_status, Record).

encode_serving_traffic(Record)
    when is_record(Record, serving_traffic) ->
    encode(serving_traffic, Record).

encode_serving_log_info(Record)
    when is_record(Record, serving_log_info) ->
    encode(serving_log_info, Record).

encode_serving_adimage(Record)
    when is_record(Record, serving_adimage) ->
    encode(serving_adimage, Record).

encode_domain_info(Record)
    when is_record(Record, domain_info) ->
    encode(domain_info, Record).

encode_serving_request(Record)
    when is_record(Record, serving_request) ->
    encode(serving_request, Record).

encode_mysql(Record) when is_record(Record, mysql) ->
    encode(mysql, Record).

encode_mysql_seconds_behind_master(Record)
    when is_record(Record, mysql_seconds_behind_master) ->
    encode(mysql_seconds_behind_master, Record).

encode_mysql_role_status(Record)
    when is_record(Record, mysql_role_status) ->
    encode(mysql_role_status, Record).

encode_mysql_tables(Record)
    when is_record(Record, mysql_tables) ->
    encode(mysql_tables, Record).

encode_mysql_table(Record)
    when is_record(Record, mysql_table) ->
    encode(mysql_table, Record).

encode_mysql_replication(Record)
    when is_record(Record, mysql_replication) ->
    encode(mysql_replication, Record).

encode_mysql_statement(Record)
    when is_record(Record, mysql_statement) ->
    encode(mysql_statement, Record).

encode_mysql_traffic(Record)
    when is_record(Record, mysql_traffic) ->
    encode(mysql_traffic, Record).

encode_mysql_summary(Record)
    when is_record(Record, mysql_summary) ->
    encode(mysql_summary, Record).

encode_generic(Record)
    when is_record(Record, generic) ->
    encode(generic, Record).

encode_generic_services(Record)
    when is_record(Record, generic_services) ->
    encode(generic_services, Record).

encode_service(Record)
    when is_record(Record, service) ->
    encode(service, Record).

encode_generic_networks(Record)
    when is_record(Record, generic_networks) ->
    encode(generic_networks, Record).

encode_network_interface(Record)
    when is_record(Record, network_interface) ->
    encode(network_interface, Record).

encode_linux_process(Record)
    when is_record(Record, linux_process) ->
    encode(linux_process, Record).

encode_freebsd_process(Record)
    when is_record(Record, freebsd_process) ->
    encode(freebsd_process, Record).

encode_generic_process(Record)
    when is_record(Record, generic_process) ->
    encode(generic_process, Record).

encode_generic_disks(Record)
    when is_record(Record, generic_disks) ->
    encode(generic_disks, Record).

encode_disk_iostat(Record)
    when is_record(Record, disk_iostat) ->
    encode(disk_iostat, Record).

encode_disk_partition(Record)
    when is_record(Record, disk_partition) ->
    encode(disk_partition, Record).

encode_generic_swap(Record)
    when is_record(Record, generic_swap) ->
    encode(generic_swap, Record).

encode_generic_mem(Record)
    when is_record(Record, generic_mem) ->
    encode(generic_mem, Record).

encode_linux_mem(Record)
    when is_record(Record, linux_mem) ->
    encode(linux_mem, Record).

encode_freebsd_mem(Record)
    when is_record(Record, freebsd_mem) ->
    encode(freebsd_mem, Record).

encode_generic_cpu(Record)
    when is_record(Record, generic_cpu) ->
    encode(generic_cpu, Record).

encode_linux_cpu(Record)
    when is_record(Record, linux_cpu) ->
    encode(linux_cpu, Record).

encode_freebsd_cpu(Record)
    when is_record(Record, freebsd_cpu) ->
    encode(freebsd_cpu, Record).

encode_generic_summary(Record)
    when is_record(Record, generic_summary) ->
    encode(generic_summary, Record).

encode(generic_summary, Record) ->
    [iolist(generic_summary, Record)
     | encode_extensions(Record)];
encode(freebsd_cpu, Record) ->
    [iolist(freebsd_cpu, Record)
     | encode_extensions(Record)];
encode(linux_cpu, Record) ->
    [iolist(linux_cpu, Record) | encode_extensions(Record)];
encode(generic_cpu, Record) ->
    [iolist(generic_cpu, Record)
     | encode_extensions(Record)];
encode(freebsd_mem, Record) ->
    [iolist(freebsd_mem, Record)
     | encode_extensions(Record)];
encode(linux_mem, Record) ->
    [iolist(linux_mem, Record) | encode_extensions(Record)];
encode(generic_mem, Record) ->
    [iolist(generic_mem, Record)
     | encode_extensions(Record)];
encode(generic_swap, Record) ->
    [iolist(generic_swap, Record)
     | encode_extensions(Record)];
encode(disk_partition, Record) ->
    [iolist(disk_partition, Record)
     | encode_extensions(Record)];
encode(disk_iostat, Record) ->
    [iolist(disk_iostat, Record)
     | encode_extensions(Record)];
encode(generic_disks, Record) ->
    [iolist(generic_disks, Record)
     | encode_extensions(Record)];
encode(generic_process, Record) ->
    [iolist(generic_process, Record)
     | encode_extensions(Record)];
encode(freebsd_process, Record) ->
    [iolist(freebsd_process, Record)
     | encode_extensions(Record)];
encode(linux_process, Record) ->
    [iolist(linux_process, Record)
     | encode_extensions(Record)];
encode(network_interface, Record) ->
    [iolist(network_interface, Record)
     | encode_extensions(Record)];
encode(generic_networks, Record) ->
    [iolist(generic_networks, Record)
     | encode_extensions(Record)];
encode(service, Record) ->
    [iolist(service, Record) | encode_extensions(Record)];
encode(generic_services, Record) ->
    [iolist(generic_services, Record)
     | encode_extensions(Record)];
encode(generic, Record) ->
    [iolist(generic, Record) | encode_extensions(Record)];
encode(mysql_summary, Record) ->
    [iolist(mysql_summary, Record)
     | encode_extensions(Record)];
encode(mysql_traffic, Record) ->
    [iolist(mysql_traffic, Record)
     | encode_extensions(Record)];
encode(mysql_statement, Record) ->
    [iolist(mysql_statement, Record)
     | encode_extensions(Record)];
encode(mysql_replication, Record) ->
    [iolist(mysql_replication, Record)
     | encode_extensions(Record)];
encode(mysql_table, Record) ->
    [iolist(mysql_table, Record)
     | encode_extensions(Record)];
encode(mysql_tables, Record) ->
    [iolist(mysql_tables, Record)
     | encode_extensions(Record)];
encode(mysql_role_status, Record) ->
    [iolist(mysql_role_status, Record)
     | encode_extensions(Record)];
encode(mysql_seconds_behind_master, Record) ->
    [iolist(mysql_seconds_behind_master, Record)
     | encode_extensions(Record)];
encode(mysql, Record) ->
    [iolist(mysql, Record) | encode_extensions(Record)];
encode(serving_request, Record) ->
    [iolist(serving_request, Record)
     | encode_extensions(Record)];
encode(domain_info, Record) ->
    [iolist(domain_info, Record)
     | encode_extensions(Record)];
encode(serving_adimage, Record) ->
    [iolist(serving_adimage, Record)
     | encode_extensions(Record)];
encode(serving_log_info, Record) ->
    [iolist(serving_log_info, Record)
     | encode_extensions(Record)];
encode(serving_traffic, Record) ->
    [iolist(serving_traffic, Record)
     | encode_extensions(Record)];
encode(serving_engine_status, Record) ->
    [iolist(serving_engine_status, Record)
     | encode_extensions(Record)];
encode(serving, Record) ->
    [iolist(serving, Record) | encode_extensions(Record)];
encode(daemon_webserver_status, Record) ->
    [iolist(daemon_webserver_status, Record)
     | encode_extensions(Record)];
encode(daemon_daemon_status, Record) ->
    [iolist(daemon_daemon_status, Record)
     | encode_extensions(Record)];
encode(daemon_login_status, Record) ->
    [iolist(daemon_login_status, Record)
     | encode_extensions(Record)];
encode(daemon_adserv_status, Record) ->
    [iolist(daemon_adserv_status, Record)
     | encode_extensions(Record)];
encode(daemon_errorlog_status, Record) ->
    [iolist(daemon_errorlog_status, Record)
     | encode_extensions(Record)];
encode(daemon, Record) ->
    [iolist(daemon, Record) | encode_extensions(Record)];
encode(report_process_info, Record) ->
    [iolist(report_process_info, Record)
     | encode_extensions(Record)];
encode(report_process_speed, Record) ->
    [iolist(report_process_speed, Record)
     | encode_extensions(Record)];
encode(report_wait_process_log_num, Record) ->
    [iolist(report_wait_process_log_num, Record)
     | encode_extensions(Record)];
encode(report, Record) ->
    [iolist(report, Record) | encode_extensions(Record)];
encode(monitor_msg, Record) ->
    [iolist(monitor_msg, Record)
     | encode_extensions(Record)].

encode_extensions(_) -> [].

iolist(generic_summary, Record) ->
    [pack(1, required,
	  with_default(Record#generic_summary.load, none), string,
	  []),
     pack(2, required,
	  with_default(Record#generic_summary.systime, none),
	  string, []),
     pack(3, required,
	  with_default(Record#generic_summary.uptime, none),
	  string, []),
     pack(4, required,
	  with_default(Record#generic_summary.tcp_connections,
		       none),
	  string, []),
     pack(5, optional,
	  with_default(Record#generic_summary.uname, none),
	  string, [])];
iolist(freebsd_cpu, Record) ->
    [pack(1, required,
	  with_default(Record#freebsd_cpu.user, none), string,
	  []),
     pack(2, required,
	  with_default(Record#freebsd_cpu.nice, none), string,
	  []),
     pack(3, required,
	  with_default(Record#freebsd_cpu.system, none), string,
	  []),
     pack(4, required,
	  with_default(Record#freebsd_cpu.interrupt, none),
	  string, []),
     pack(5, required,
	  with_default(Record#freebsd_cpu.idle, none), string,
	  [])];
iolist(linux_cpu, Record) ->
    [pack(1, required,
	  with_default(Record#linux_cpu.user, none), string, []),
     pack(2, required,
	  with_default(Record#linux_cpu.nice, none), string, []),
     pack(3, required,
	  with_default(Record#linux_cpu.system, none), string,
	  []),
     pack(4, required,
	  with_default(Record#linux_cpu.iowait, none), string,
	  []),
     pack(5, required,
	  with_default(Record#linux_cpu.steal, none), string, []),
     pack(6, required,
	  with_default(Record#linux_cpu.idle, none), string, [])];
iolist(generic_cpu, Record) ->
    [pack(1, optional,
	  with_default(Record#generic_cpu.freebsd_cpu, none),
	  freebsd_cpu, []),
     pack(2, optional,
	  with_default(Record#generic_cpu.linux_cpu, none),
	  linux_cpu, [])];
iolist(freebsd_mem, Record) ->
    [pack(1, required,
	  with_default(Record#freebsd_mem.total, none), string,
	  []),
     pack(2, required,
	  with_default(Record#freebsd_mem.active, none), string,
	  []),
     pack(3, required,
	  with_default(Record#freebsd_mem.free, none), string,
	  []),
     pack(4, required,
	  with_default(Record#freebsd_mem.inactive, none), string,
	  []),
     pack(5, required,
	  with_default(Record#freebsd_mem.wire, none), string,
	  []),
     pack(6, required,
	  with_default(Record#freebsd_mem.cached, none), string,
	  [])];
iolist(linux_mem, Record) ->
    [pack(1, required,
	  with_default(Record#linux_mem.total, none), string,
	  [])];
iolist(generic_mem, Record) ->
    [pack(1, optional,
	  with_default(Record#generic_mem.freebsd_mem, none),
	  freebsd_mem, []),
     pack(2, optional,
	  with_default(Record#generic_mem.linux_mem, none),
	  linux_mem, [])];
iolist(generic_swap, Record) ->
    [pack(1, required,
	  with_default(Record#generic_swap.total, none), string,
	  []),
     pack(2, required,
	  with_default(Record#generic_swap.used, none), string,
	  [])];
iolist(disk_partition, Record) ->
    [pack(1, required,
	  with_default(Record#disk_partition.mounted, none),
	  string, []),
     pack(2, required,
	  with_default(Record#disk_partition.capacity, none),
	  int32, []),
     pack(3, required,
	  with_default(Record#disk_partition.iused, none), int32,
	  [])];
iolist(disk_iostat, Record) ->
    [pack(1, optional,
	  with_default(Record#disk_iostat.device_name, none),
	  string, []),
     pack(2, optional,
	  with_default(Record#disk_iostat.tps, none), string,
	  [])];
iolist(generic_disks, Record) ->
    [pack(1, repeated,
	  with_default(Record#generic_disks.disk_partition, none),
	  disk_partition, []),
     pack(2, repeated,
	  with_default(Record#generic_disks.disk_iostat, none),
	  disk_iostat, [])];
iolist(generic_process, Record) ->
    [pack(1, optional,
	  with_default(Record#generic_process.freebsd_process,
		       none),
	  freebsd_process, []),
     pack(2, optional,
	  with_default(Record#generic_process.linux_process,
		       none),
	  linux_process, [])];
iolist(freebsd_process, Record) ->
    [pack(1, required,
	  with_default(Record#freebsd_process.statd, none),
	  string, []),
     pack(2, required,
	  with_default(Record#freebsd_process.stati, none),
	  string, []),
     pack(3, required,
	  with_default(Record#freebsd_process.statl, none),
	  string, []),
     pack(4, required,
	  with_default(Record#freebsd_process.statr, none),
	  string, []),
     pack(5, required,
	  with_default(Record#freebsd_process.stats, none),
	  string, []),
     pack(6, required,
	  with_default(Record#freebsd_process.statt, none),
	  string, []),
     pack(7, required,
	  with_default(Record#freebsd_process.statw, none),
	  string, []),
     pack(8, required,
	  with_default(Record#freebsd_process.statz, none),
	  string, []),
     pack(9, required,
	  with_default(Record#freebsd_process.total, none),
	  string, [])];
iolist(linux_process, Record) ->
    [pack(1, required,
	  with_default(Record#linux_process.statd, none), string,
	  []),
     pack(2, required,
	  with_default(Record#linux_process.statr, none), string,
	  []),
     pack(3, required,
	  with_default(Record#linux_process.stats, none), string,
	  []),
     pack(4, required,
	  with_default(Record#linux_process.statt, none), string,
	  []),
     pack(5, optional,
	  with_default(Record#linux_process.statw, none), string,
	  []),
     pack(6, required,
	  with_default(Record#linux_process.statx, none), string,
	  []),
     pack(7, required,
	  with_default(Record#linux_process.statz, none), string,
	  []),
     pack(8, required,
	  with_default(Record#linux_process.total, none), string,
	  [])];
iolist(network_interface, Record) ->
    [pack(1, optional,
	  with_default(Record#network_interface.ifname, none),
	  string, []),
     pack(2, optional,
	  with_default(Record#network_interface.ifin, none),
	  string, []),
     pack(3, optional,
	  with_default(Record#network_interface.ifout, none),
	  string, [])];
iolist(generic_networks, Record) ->
    [pack(1, repeated,
	  with_default(Record#generic_networks.network_interface,
		       none),
	  network_interface, [])];
iolist(service, Record) ->
    [pack(1, required,
	  with_default(Record#service.service_name, none), string,
	  []),
     pack(2, required,
	  with_default(Record#service.service_port, none), int32,
	  []),
     pack(3, required,
	  with_default(Record#service.service_status, none),
	  int32, []),
     pack(4, optional,
	  with_default(Record#service.status_desc, none), string,
	  [])];
iolist(generic_services, Record) ->
    [pack(1, repeated,
	  with_default(Record#generic_services.service, none),
	  service, [])];
iolist(generic, Record) ->
    [pack(1, required,
	  with_default(Record#generic.summary, none),
	  generic_summary, []),
     pack(2, required,
	  with_default(Record#generic.cpu, none), generic_cpu,
	  []),
     pack(3, required,
	  with_default(Record#generic.mem, none), generic_mem,
	  []),
     pack(4, required,
	  with_default(Record#generic.swap, none), generic_swap,
	  []),
     pack(5, required,
	  with_default(Record#generic.disk, none), generic_disks,
	  []),
     pack(6, required,
	  with_default(Record#generic.process, none),
	  generic_process, []),
     pack(7, required,
	  with_default(Record#generic.network, none),
	  generic_networks, []),
     pack(8, required,
	  with_default(Record#generic.service, none),
	  generic_services, [])];
iolist(mysql_summary, Record) ->
    [pack(1, required,
	  with_default(Record#mysql_summary.uptime, none), string,
	  []),
     pack(2, required,
	  with_default(Record#mysql_summary.threads_created,
		       none),
	  string, []),
     pack(3, required,
	  with_default(Record#mysql_summary.slow_queries, none),
	  string, []),
     pack(4, required,
	  with_default(Record#mysql_summary.questions, none),
	  string, []),
     pack(5, required,
	  with_default(Record#mysql_summary.connections, none),
	  string, []),
     pack(6, required,
	  with_default(Record#mysql_summary.cur_connections,
		       none),
	  string, [])];
iolist(mysql_traffic, Record) ->
    [pack(1, required,
	  with_default(Record#mysql_traffic.in, none), string,
	  []),
     pack(2, required,
	  with_default(Record#mysql_traffic.out, none), string,
	  [])];
iolist(mysql_statement, Record) ->
    [pack(1, required,
	  with_default(Record#mysql_statement.delete, none),
	  string, []),
     pack(2, required,
	  with_default(Record#mysql_statement.insert, none),
	  string, []),
     pack(3, required,
	  with_default(Record#mysql_statement.select, none),
	  string, []),
     pack(4, required,
	  with_default(Record#mysql_statement.update, none),
	  string, [])];
iolist(mysql_replication, Record) ->
    [pack(1, required,
	  with_default(Record#mysql_replication.onoff, none),
	  string, [])];
iolist(mysql_table, Record) ->
    [pack(1, required,
	  with_default(Record#mysql_table.db_name, none), string,
	  []),
     pack(2, required,
	  with_default(Record#mysql_table.table_name, none),
	  string, []),
     pack(3, required,
	  with_default(Record#mysql_table.engine, none), string,
	  []),
     pack(4, required,
	  with_default(Record#mysql_table.table_size, none),
	  string, []),
     pack(5, required,
	  with_default(Record#mysql_table.rows, none), string,
	  []),
     pack(6, required,
	  with_default(Record#mysql_table.data_length, none),
	  string, []),
     pack(7, required,
	  with_default(Record#mysql_table.index_length, none),
	  string, []),
     pack(8, required,
	  with_default(Record#mysql_table.auto_increment, none),
	  string, []),
     pack(9, required,
	  with_default(Record#mysql_table.update_time, none),
	  string, []),
     pack(10, required,
	  with_default(Record#mysql_table.collation, none),
	  string, [])];
iolist(mysql_tables, Record) ->
    [pack(1, repeated,
	  with_default(Record#mysql_tables.mysql_table, none),
	  mysql_table, [])];
iolist(mysql_role_status, Record) ->
    [pack(1, required,
	  with_default(Record#mysql_role_status.status, none),
	  int32, [])];
iolist(mysql_seconds_behind_master, Record) ->
    [pack(1, required,
	  with_default(Record#mysql_seconds_behind_master.sec,
		       none),
	  string, [])];
iolist(mysql, Record) ->
    [pack(1, required,
	  with_default(Record#mysql.summary, none), mysql_summary,
	  []),
     pack(2, required,
	  with_default(Record#mysql.traffic, none), mysql_traffic,
	  []),
     pack(3, required,
	  with_default(Record#mysql.statement, none),
	  mysql_statement, []),
     pack(4, required,
	  with_default(Record#mysql.replication, none),
	  mysql_replication, []),
     pack(5, required,
	  with_default(Record#mysql.tables, none), mysql_tables,
	  []),
     pack(6, required,
	  with_default(Record#mysql.role_status, none),
	  mysql_role_status, []),
     pack(7, required,
	  with_default(Record#mysql.seconds_behind_master, none),
	  mysql_seconds_behind_master, [])];
iolist(serving_request, Record) ->
    [pack(1, required,
	  with_default(Record#serving_request.request, none),
	  string, [])];
iolist(domain_info, Record) ->
    [pack(1, required,
	  with_default(Record#domain_info.domain_id, none),
	  string, []),
     pack(2, required,
	  with_default(Record#domain_info.adpos_num, none),
	  string, []),
     pack(3, required,
	  with_default(Record#domain_info.adcamp_num, none),
	  string, []),
     pack(4, required,
	  with_default(Record#domain_info.deliver_cache_num,
		       none),
	  string, []),
     pack(5, required,
	  with_default(Record#domain_info.publish_pkg_serial_num,
		       none),
	  string, []),
     pack(6, required,
	  with_default(Record#domain_info.publish_role, none),
	  string, [])];
iolist(serving_adimage, Record) ->
    [pack(1, optional,
	  with_default(Record#serving_adimage.domain_info, none),
	  domain_info, [])];
iolist(serving_log_info, Record) ->
    [pack(1, required,
	  with_default(Record#serving_log_info.total_log, none),
	  string, []),
     pack(2, required,
	  with_default(Record#serving_log_info.total_camplog,
		       none),
	  string, []),
     pack(3, required,
	  with_default(Record#serving_log_info.sslog_name, none),
	  string, []),
     pack(4, required,
	  with_default(Record#serving_log_info.sslog_md5, none),
	  string, [])];
iolist(serving_traffic, Record) ->
    [pack(1, required,
	  with_default(Record#serving_traffic.traffic, none),
	  string, [])];
iolist(serving_engine_status, Record) ->
    [pack(1, required,
	  with_default(Record#serving_engine_status.engine_status,
		       none),
	  int32, [])];
iolist(serving, Record) ->
    [pack(1, required,
	  with_default(Record#serving.serving_request, none),
	  serving_request, []),
     pack(2, required,
	  with_default(Record#serving.serving_adimage, none),
	  serving_adimage, []),
     pack(3, required,
	  with_default(Record#serving.serving_loginfo, none),
	  serving_log_info, []),
     pack(4, required,
	  with_default(Record#serving.serving_traffic, none),
	  serving_traffic, []),
     pack(5, required,
	  with_default(Record#serving.serving_engine_status,
		       none),
	  serving_engine_status, [])];
iolist(daemon_webserver_status, Record) ->
    [pack(1, required,
	  with_default(Record#daemon_webserver_status.webserver_status,
		       none),
	  int32, [])];
iolist(daemon_daemon_status, Record) ->
    [pack(1, required,
	  with_default(Record#daemon_daemon_status.daemon_status,
		       none),
	  int32, [])];
iolist(daemon_login_status, Record) ->
    [pack(1, required,
	  with_default(Record#daemon_login_status.login_status,
		       none),
	  int32, [])];
iolist(daemon_adserv_status, Record) ->
    [pack(1, required,
	  with_default(Record#daemon_adserv_status.adserv_status,
		       none),
	  int32, [])];
iolist(daemon_errorlog_status, Record) ->
    [pack(1, required,
	  with_default(Record#daemon_errorlog_status.errorlog_status,
		       none),
	  int32, [])];
iolist(daemon, Record) ->
    [pack(1, required,
	  with_default(Record#daemon.daemon_webserver_status,
		       none),
	  daemon_webserver_status, []),
     pack(2, required,
	  with_default(Record#daemon.daemon_daemon_status, none),
	  daemon_daemon_status, []),
     pack(3, required,
	  with_default(Record#daemon.daemon_login_status, none),
	  daemon_login_status, []),
     pack(4, required,
	  with_default(Record#daemon.daemon_adserv_status, none),
	  daemon_adserv_status, []),
     pack(5, required,
	  with_default(Record#daemon.daemon_errorlog_status,
		       none),
	  daemon_errorlog_status, [])];
iolist(report_process_info, Record) ->
    [pack(1, required,
	  with_default(Record#report_process_info.process_info,
		       none),
	  string, [])];
iolist(report_process_speed, Record) ->
    [pack(1, required,
	  with_default(Record#report_process_speed.process_speed,
		       none),
	  string, [])];
iolist(report_wait_process_log_num, Record) ->
    [pack(1, required,
	  with_default(Record#report_wait_process_log_num.wait_process_log_num,
		       none),
	  string, [])];
iolist(report, Record) ->
    [pack(1, required,
	  with_default(Record#report.process_info, none),
	  report_process_info, []),
     pack(2, required,
	  with_default(Record#report.process_speed, none),
	  report_process_speed, []),
     pack(3, required,
	  with_default(Record#report.wait_process_log_num, none),
	  report_wait_process_log_num, [])];
iolist(monitor_msg, Record) ->
    [pack(1, required,
	  with_default(Record#monitor_msg.host_name, none),
	  string, []),
     pack(2, required,
	  with_default(Record#monitor_msg.client_version, none),
	  string, []),
     pack(3, required,
	  with_default(Record#monitor_msg.generic, none), generic,
	  []),
     pack(4, optional,
	  with_default(Record#monitor_msg.mysql, none), mysql,
	  []),
     pack(5, optional,
	  with_default(Record#monitor_msg.serving, none), serving,
	  []),
     pack(6, optional,
	  with_default(Record#monitor_msg.daemon, none), daemon,
	  []),
     pack(7, optional,
	  with_default(Record#monitor_msg.report, none), report,
	  [])].

with_default(Default, Default) -> undefined;
with_default(Val, _) -> Val.

pack(_, optional, undefined, _, _) -> [];
pack(_, repeated, undefined, _, _) -> [];
pack(_, repeated_packed, undefined, _, _) -> [];
pack(_, repeated_packed, [], _, _) -> [];
pack(FNum, required, undefined, Type, _) ->
    exit({error,
	  {required_field_is_undefined, FNum, Type}});
pack(_, repeated, [], _, Acc) -> lists:reverse(Acc);
pack(FNum, repeated, [Head | Tail], Type, Acc) ->
    pack(FNum, repeated, Tail, Type,
	 [pack(FNum, optional, Head, Type, []) | Acc]);
pack(FNum, repeated_packed, Data, Type, _) ->
    protobuffs:encode_packed(FNum, Data, Type);
pack(FNum, _, Data, _, _) when is_tuple(Data) ->
    [RecName | _] = tuple_to_list(Data),
    protobuffs:encode(FNum, encode(RecName, Data), bytes);
pack(FNum, _, Data, Type, _)
    when Type =:= bool;
	 Type =:= int32;
	 Type =:= uint32;
	 Type =:= int64;
	 Type =:= uint64;
	 Type =:= sint32;
	 Type =:= sint64;
	 Type =:= fixed32;
	 Type =:= sfixed32;
	 Type =:= fixed64;
	 Type =:= sfixed64;
	 Type =:= string;
	 Type =:= bytes;
	 Type =:= float;
	 Type =:= double ->
    protobuffs:encode(FNum, Data, Type);
pack(FNum, _, Data, Type, _) when is_atom(Data) ->
    protobuffs:encode(FNum, enum_to_int(Type, Data), enum).

enum_to_int(pikachu, value) -> 1.

int_to_enum(_, Val) -> Val.

decode_monitor_msg(Bytes) when is_binary(Bytes) ->
    decode(monitor_msg, Bytes).

decode_report(Bytes) when is_binary(Bytes) ->
    decode(report, Bytes).

decode_report_wait_process_log_num(Bytes)
    when is_binary(Bytes) ->
    decode(report_wait_process_log_num, Bytes).

decode_report_process_speed(Bytes)
    when is_binary(Bytes) ->
    decode(report_process_speed, Bytes).

decode_report_process_info(Bytes)
    when is_binary(Bytes) ->
    decode(report_process_info, Bytes).

decode_daemon(Bytes) when is_binary(Bytes) ->
    decode(daemon, Bytes).

decode_daemon_errorlog_status(Bytes)
    when is_binary(Bytes) ->
    decode(daemon_errorlog_status, Bytes).

decode_daemon_adserv_status(Bytes)
    when is_binary(Bytes) ->
    decode(daemon_adserv_status, Bytes).

decode_daemon_login_status(Bytes)
    when is_binary(Bytes) ->
    decode(daemon_login_status, Bytes).

decode_daemon_daemon_status(Bytes)
    when is_binary(Bytes) ->
    decode(daemon_daemon_status, Bytes).

decode_daemon_webserver_status(Bytes)
    when is_binary(Bytes) ->
    decode(daemon_webserver_status, Bytes).

decode_serving(Bytes) when is_binary(Bytes) ->
    decode(serving, Bytes).

decode_serving_engine_status(Bytes)
    when is_binary(Bytes) ->
    decode(serving_engine_status, Bytes).

decode_serving_traffic(Bytes) when is_binary(Bytes) ->
    decode(serving_traffic, Bytes).

decode_serving_log_info(Bytes) when is_binary(Bytes) ->
    decode(serving_log_info, Bytes).

decode_serving_adimage(Bytes) when is_binary(Bytes) ->
    decode(serving_adimage, Bytes).

decode_domain_info(Bytes) when is_binary(Bytes) ->
    decode(domain_info, Bytes).

decode_serving_request(Bytes) when is_binary(Bytes) ->
    decode(serving_request, Bytes).

decode_mysql(Bytes) when is_binary(Bytes) ->
    decode(mysql, Bytes).

decode_mysql_seconds_behind_master(Bytes)
    when is_binary(Bytes) ->
    decode(mysql_seconds_behind_master, Bytes).

decode_mysql_role_status(Bytes) when is_binary(Bytes) ->
    decode(mysql_role_status, Bytes).

decode_mysql_tables(Bytes) when is_binary(Bytes) ->
    decode(mysql_tables, Bytes).

decode_mysql_table(Bytes) when is_binary(Bytes) ->
    decode(mysql_table, Bytes).

decode_mysql_replication(Bytes) when is_binary(Bytes) ->
    decode(mysql_replication, Bytes).

decode_mysql_statement(Bytes) when is_binary(Bytes) ->
    decode(mysql_statement, Bytes).

decode_mysql_traffic(Bytes) when is_binary(Bytes) ->
    decode(mysql_traffic, Bytes).

decode_mysql_summary(Bytes) when is_binary(Bytes) ->
    decode(mysql_summary, Bytes).

decode_generic(Bytes) when is_binary(Bytes) ->
    decode(generic, Bytes).

decode_generic_services(Bytes) when is_binary(Bytes) ->
    decode(generic_services, Bytes).

decode_service(Bytes) when is_binary(Bytes) ->
    decode(service, Bytes).

decode_generic_networks(Bytes) when is_binary(Bytes) ->
    decode(generic_networks, Bytes).

decode_network_interface(Bytes) when is_binary(Bytes) ->
    decode(network_interface, Bytes).

decode_linux_process(Bytes) when is_binary(Bytes) ->
    decode(linux_process, Bytes).

decode_freebsd_process(Bytes) when is_binary(Bytes) ->
    decode(freebsd_process, Bytes).

decode_generic_process(Bytes) when is_binary(Bytes) ->
    decode(generic_process, Bytes).

decode_generic_disks(Bytes) when is_binary(Bytes) ->
    decode(generic_disks, Bytes).

decode_disk_iostat(Bytes) when is_binary(Bytes) ->
    decode(disk_iostat, Bytes).

decode_disk_partition(Bytes) when is_binary(Bytes) ->
    decode(disk_partition, Bytes).

decode_generic_swap(Bytes) when is_binary(Bytes) ->
    decode(generic_swap, Bytes).

decode_generic_mem(Bytes) when is_binary(Bytes) ->
    decode(generic_mem, Bytes).

decode_linux_mem(Bytes) when is_binary(Bytes) ->
    decode(linux_mem, Bytes).

decode_freebsd_mem(Bytes) when is_binary(Bytes) ->
    decode(freebsd_mem, Bytes).

decode_generic_cpu(Bytes) when is_binary(Bytes) ->
    decode(generic_cpu, Bytes).

decode_linux_cpu(Bytes) when is_binary(Bytes) ->
    decode(linux_cpu, Bytes).

decode_freebsd_cpu(Bytes) when is_binary(Bytes) ->
    decode(freebsd_cpu, Bytes).

decode_generic_summary(Bytes) when is_binary(Bytes) ->
    decode(generic_summary, Bytes).

decode(enummsg_values, 1) -> value1;
decode(generic_summary, Bytes) when is_binary(Bytes) ->
    Types = [{5, uname, string, []},
	     {4, tcp_connections, string, []},
	     {3, uptime, string, []}, {2, systime, string, []},
	     {1, load, string, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(generic_summary, Decoded);
decode(freebsd_cpu, Bytes) when is_binary(Bytes) ->
    Types = [{5, idle, string, []},
	     {4, interrupt, string, []}, {3, system, string, []},
	     {2, nice, string, []}, {1, user, string, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(freebsd_cpu, Decoded);
decode(linux_cpu, Bytes) when is_binary(Bytes) ->
    Types = [{6, idle, string, []}, {5, steal, string, []},
	     {4, iowait, string, []}, {3, system, string, []},
	     {2, nice, string, []}, {1, user, string, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(linux_cpu, Decoded);
decode(generic_cpu, Bytes) when is_binary(Bytes) ->
    Types = [{2, linux_cpu, linux_cpu, [is_record]},
	     {1, freebsd_cpu, freebsd_cpu, [is_record]}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(generic_cpu, Decoded);
decode(freebsd_mem, Bytes) when is_binary(Bytes) ->
    Types = [{6, cached, string, []}, {5, wire, string, []},
	     {4, inactive, string, []}, {3, free, string, []},
	     {2, active, string, []}, {1, total, string, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(freebsd_mem, Decoded);
decode(linux_mem, Bytes) when is_binary(Bytes) ->
    Types = [{1, total, string, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(linux_mem, Decoded);
decode(generic_mem, Bytes) when is_binary(Bytes) ->
    Types = [{2, linux_mem, linux_mem, [is_record]},
	     {1, freebsd_mem, freebsd_mem, [is_record]}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(generic_mem, Decoded);
decode(generic_swap, Bytes) when is_binary(Bytes) ->
    Types = [{2, used, string, []}, {1, total, string, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(generic_swap, Decoded);
decode(disk_partition, Bytes) when is_binary(Bytes) ->
    Types = [{3, iused, int32, []},
	     {2, capacity, int32, []}, {1, mounted, string, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(disk_partition, Decoded);
decode(disk_iostat, Bytes) when is_binary(Bytes) ->
    Types = [{2, tps, string, []},
	     {1, device_name, string, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(disk_iostat, Decoded);
decode(generic_disks, Bytes) when is_binary(Bytes) ->
    Types = [{2, disk_iostat, disk_iostat,
	      [is_record, repeated]},
	     {1, disk_partition, disk_partition,
	      [is_record, repeated]}],
    Defaults = [{1, disk_partition, []},
		{2, disk_iostat, []}],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(generic_disks, Decoded);
decode(generic_process, Bytes) when is_binary(Bytes) ->
    Types = [{2, linux_process, linux_process, [is_record]},
	     {1, freebsd_process, freebsd_process, [is_record]}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(generic_process, Decoded);
decode(freebsd_process, Bytes) when is_binary(Bytes) ->
    Types = [{9, total, string, []}, {8, statz, string, []},
	     {7, statw, string, []}, {6, statt, string, []},
	     {5, stats, string, []}, {4, statr, string, []},
	     {3, statl, string, []}, {2, stati, string, []},
	     {1, statd, string, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(freebsd_process, Decoded);
decode(linux_process, Bytes) when is_binary(Bytes) ->
    Types = [{8, total, string, []}, {7, statz, string, []},
	     {6, statx, string, []}, {5, statw, string, []},
	     {4, statt, string, []}, {3, stats, string, []},
	     {2, statr, string, []}, {1, statd, string, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(linux_process, Decoded);
decode(network_interface, Bytes)
    when is_binary(Bytes) ->
    Types = [{3, ifout, string, []}, {2, ifin, string, []},
	     {1, ifname, string, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(network_interface, Decoded);
decode(generic_networks, Bytes) when is_binary(Bytes) ->
    Types = [{1, network_interface, network_interface,
	      [is_record, repeated]}],
    Defaults = [{1, network_interface, []}],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(generic_networks, Decoded);
decode(service, Bytes) when is_binary(Bytes) ->
    Types = [{4, status_desc, string, []},
	     {3, service_status, int32, []},
	     {2, service_port, int32, []},
	     {1, service_name, string, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(service, Decoded);
decode(generic_services, Bytes) when is_binary(Bytes) ->
    Types = [{1, service, service, [is_record, repeated]}],
    Defaults = [{1, service, []}],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(generic_services, Decoded);
decode(generic, Bytes) when is_binary(Bytes) ->
    Types = [{8, service, generic_services, [is_record]},
	     {7, network, generic_networks, [is_record]},
	     {6, process, generic_process, [is_record]},
	     {5, disk, generic_disks, [is_record]},
	     {4, swap, generic_swap, [is_record]},
	     {3, mem, generic_mem, [is_record]},
	     {2, cpu, generic_cpu, [is_record]},
	     {1, summary, generic_summary, [is_record]}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(generic, Decoded);
decode(mysql_summary, Bytes) when is_binary(Bytes) ->
    Types = [{6, cur_connections, string, []},
	     {5, connections, string, []},
	     {4, questions, string, []},
	     {3, slow_queries, string, []},
	     {2, threads_created, string, []},
	     {1, uptime, string, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(mysql_summary, Decoded);
decode(mysql_traffic, Bytes) when is_binary(Bytes) ->
    Types = [{2, out, string, []}, {1, in, string, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(mysql_traffic, Decoded);
decode(mysql_statement, Bytes) when is_binary(Bytes) ->
    Types = [{4, update, string, []},
	     {3, select, string, []}, {2, insert, string, []},
	     {1, delete, string, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(mysql_statement, Decoded);
decode(mysql_replication, Bytes)
    when is_binary(Bytes) ->
    Types = [{1, onoff, string, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(mysql_replication, Decoded);
decode(mysql_table, Bytes) when is_binary(Bytes) ->
    Types = [{10, collation, string, []},
	     {9, update_time, string, []},
	     {8, auto_increment, string, []},
	     {7, index_length, string, []},
	     {6, data_length, string, []}, {5, rows, string, []},
	     {4, table_size, string, []}, {3, engine, string, []},
	     {2, table_name, string, []}, {1, db_name, string, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(mysql_table, Decoded);
decode(mysql_tables, Bytes) when is_binary(Bytes) ->
    Types = [{1, mysql_table, mysql_table,
	      [is_record, repeated]}],
    Defaults = [{1, mysql_table, []}],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(mysql_tables, Decoded);
decode(mysql_role_status, Bytes)
    when is_binary(Bytes) ->
    Types = [{1, status, int32, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(mysql_role_status, Decoded);
decode(mysql_seconds_behind_master, Bytes)
    when is_binary(Bytes) ->
    Types = [{1, sec, string, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(mysql_seconds_behind_master, Decoded);
decode(mysql, Bytes) when is_binary(Bytes) ->
    Types = [{7, seconds_behind_master,
	      mysql_seconds_behind_master, [is_record]},
	     {6, role_status, mysql_role_status, [is_record]},
	     {5, tables, mysql_tables, [is_record]},
	     {4, replication, mysql_replication, [is_record]},
	     {3, statement, mysql_statement, [is_record]},
	     {2, traffic, mysql_traffic, [is_record]},
	     {1, summary, mysql_summary, [is_record]}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(mysql, Decoded);
decode(serving_request, Bytes) when is_binary(Bytes) ->
    Types = [{1, request, string, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(serving_request, Decoded);
decode(domain_info, Bytes) when is_binary(Bytes) ->
    Types = [{6, publish_role, string, []},
	     {5, publish_pkg_serial_num, string, []},
	     {4, deliver_cache_num, string, []},
	     {3, adcamp_num, string, []}, {2, adpos_num, string, []},
	     {1, domain_id, string, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(domain_info, Decoded);
decode(serving_adimage, Bytes) when is_binary(Bytes) ->
    Types = [{1, domain_info, domain_info, [is_record]}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(serving_adimage, Decoded);
decode(serving_log_info, Bytes) when is_binary(Bytes) ->
    Types = [{4, sslog_md5, string, []},
	     {3, sslog_name, string, []},
	     {2, total_camplog, string, []},
	     {1, total_log, string, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(serving_log_info, Decoded);
decode(serving_traffic, Bytes) when is_binary(Bytes) ->
    Types = [{1, traffic, string, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(serving_traffic, Decoded);
decode(serving_engine_status, Bytes)
    when is_binary(Bytes) ->
    Types = [{1, engine_status, int32, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(serving_engine_status, Decoded);
decode(serving, Bytes) when is_binary(Bytes) ->
    Types = [{5, serving_engine_status,
	      serving_engine_status, [is_record]},
	     {4, serving_traffic, serving_traffic, [is_record]},
	     {3, serving_loginfo, serving_log_info, [is_record]},
	     {2, serving_adimage, serving_adimage, [is_record]},
	     {1, serving_request, serving_request, [is_record]}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(serving, Decoded);
decode(daemon_webserver_status, Bytes)
    when is_binary(Bytes) ->
    Types = [{1, webserver_status, int32, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(daemon_webserver_status, Decoded);
decode(daemon_daemon_status, Bytes)
    when is_binary(Bytes) ->
    Types = [{1, daemon_status, int32, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(daemon_daemon_status, Decoded);
decode(daemon_login_status, Bytes)
    when is_binary(Bytes) ->
    Types = [{1, login_status, int32, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(daemon_login_status, Decoded);
decode(daemon_adserv_status, Bytes)
    when is_binary(Bytes) ->
    Types = [{1, adserv_status, int32, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(daemon_adserv_status, Decoded);
decode(daemon_errorlog_status, Bytes)
    when is_binary(Bytes) ->
    Types = [{1, errorlog_status, int32, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(daemon_errorlog_status, Decoded);
decode(daemon, Bytes) when is_binary(Bytes) ->
    Types = [{5, daemon_errorlog_status,
	      daemon_errorlog_status, [is_record]},
	     {4, daemon_adserv_status, daemon_adserv_status,
	      [is_record]},
	     {3, daemon_login_status, daemon_login_status,
	      [is_record]},
	     {2, daemon_daemon_status, daemon_daemon_status,
	      [is_record]},
	     {1, daemon_webserver_status, daemon_webserver_status,
	      [is_record]}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(daemon, Decoded);
decode(report_process_info, Bytes)
    when is_binary(Bytes) ->
    Types = [{1, process_info, string, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(report_process_info, Decoded);
decode(report_process_speed, Bytes)
    when is_binary(Bytes) ->
    Types = [{1, process_speed, string, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(report_process_speed, Decoded);
decode(report_wait_process_log_num, Bytes)
    when is_binary(Bytes) ->
    Types = [{1, wait_process_log_num, string, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(report_wait_process_log_num, Decoded);
decode(report, Bytes) when is_binary(Bytes) ->
    Types = [{3, wait_process_log_num,
	      report_wait_process_log_num, [is_record]},
	     {2, process_speed, report_process_speed, [is_record]},
	     {1, process_info, report_process_info, [is_record]}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(report, Decoded);
decode(monitor_msg, Bytes) when is_binary(Bytes) ->
    Types = [{7, report, report, [is_record]},
	     {6, daemon, daemon, [is_record]},
	     {5, serving, serving, [is_record]},
	     {4, mysql, mysql, [is_record]},
	     {3, generic, generic, [is_record]},
	     {2, client_version, string, []},
	     {1, host_name, string, []}],
    Defaults = [],
    Decoded = decode(Bytes, Types, Defaults),
    to_record(monitor_msg, Decoded).

decode(<<>>, _, Acc) -> Acc;
decode(Bytes, Types, Acc) ->
    {ok, FNum} = protobuffs:next_field_num(Bytes),
    case lists:keyfind(FNum, 1, Types) of
      {FNum, Name, Type, Opts} ->
	  {Value1, Rest1} = case lists:member(is_record, Opts) of
			      true ->
				  {{FNum, V}, R} = protobuffs:decode(Bytes,
								     bytes),
				  RecVal = decode(Type, V),
				  {RecVal, R};
			      false ->
				  case lists:member(repeated_packed, Opts) of
				    true ->
					{{FNum, V}, R} =
					    protobuffs:decode_packed(Bytes,
								     Type),
					{V, R};
				    false ->
					{{FNum, V}, R} =
					    protobuffs:decode(Bytes, Type),
					{unpack_value(V, Type), R}
				  end
			    end,
	  case lists:member(repeated, Opts) of
	    true ->
		case lists:keytake(FNum, 1, Acc) of
		  {value, {FNum, Name, List}, Acc1} ->
		      decode(Rest1, Types,
			     [{FNum, Name,
			       lists:reverse([int_to_enum(Type, Value1)
					      | lists:reverse(List)])}
			      | Acc1]);
		  false ->
		      decode(Rest1, Types,
			     [{FNum, Name, [int_to_enum(Type, Value1)]} | Acc])
		end;
	    false ->
		decode(Rest1, Types,
		       [{FNum, Name, int_to_enum(Type, Value1)} | Acc])
	  end;
      false ->
	  case lists:keyfind('$extensions', 2, Acc) of
	    {_, _, Dict} ->
		{{FNum, _V}, R} = protobuffs:decode(Bytes, bytes),
		Diff = size(Bytes) - size(R),
		<<V:Diff/binary, _/binary>> = Bytes,
		NewDict = dict:store(FNum, V, Dict),
		NewAcc = lists:keyreplace('$extensions', 2, Acc,
					  {false, '$extensions', NewDict}),
		decode(R, Types, NewAcc);
	    _ ->
		{ok, Skipped} = protobuffs:skip_next_field(Bytes),
		decode(Skipped, Types, Acc)
	  end
    end.

unpack_value(Binary, string) when is_binary(Binary) ->
    binary_to_list(Binary);
unpack_value(Value, _) -> Value.

to_record(generic_summary, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       generic_summary),
						   Record, Name, Val)
			  end,
			  #generic_summary{}, DecodedTuples),
    Record1;
to_record(freebsd_cpu, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       freebsd_cpu),
						   Record, Name, Val)
			  end,
			  #freebsd_cpu{}, DecodedTuples),
    Record1;
to_record(linux_cpu, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       linux_cpu),
						   Record, Name, Val)
			  end,
			  #linux_cpu{}, DecodedTuples),
    Record1;
to_record(generic_cpu, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       generic_cpu),
						   Record, Name, Val)
			  end,
			  #generic_cpu{}, DecodedTuples),
    Record1;
to_record(freebsd_mem, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       freebsd_mem),
						   Record, Name, Val)
			  end,
			  #freebsd_mem{}, DecodedTuples),
    Record1;
to_record(linux_mem, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       linux_mem),
						   Record, Name, Val)
			  end,
			  #linux_mem{}, DecodedTuples),
    Record1;
to_record(generic_mem, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       generic_mem),
						   Record, Name, Val)
			  end,
			  #generic_mem{}, DecodedTuples),
    Record1;
to_record(generic_swap, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       generic_swap),
						   Record, Name, Val)
			  end,
			  #generic_swap{}, DecodedTuples),
    Record1;
to_record(disk_partition, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       disk_partition),
						   Record, Name, Val)
			  end,
			  #disk_partition{}, DecodedTuples),
    Record1;
to_record(disk_iostat, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       disk_iostat),
						   Record, Name, Val)
			  end,
			  #disk_iostat{}, DecodedTuples),
    Record1;
to_record(generic_disks, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       generic_disks),
						   Record, Name, Val)
			  end,
			  #generic_disks{}, DecodedTuples),
    Record1;
to_record(generic_process, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       generic_process),
						   Record, Name, Val)
			  end,
			  #generic_process{}, DecodedTuples),
    Record1;
to_record(freebsd_process, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       freebsd_process),
						   Record, Name, Val)
			  end,
			  #freebsd_process{}, DecodedTuples),
    Record1;
to_record(linux_process, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       linux_process),
						   Record, Name, Val)
			  end,
			  #linux_process{}, DecodedTuples),
    Record1;
to_record(network_interface, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       network_interface),
						   Record, Name, Val)
			  end,
			  #network_interface{}, DecodedTuples),
    Record1;
to_record(generic_networks, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       generic_networks),
						   Record, Name, Val)
			  end,
			  #generic_networks{}, DecodedTuples),
    Record1;
to_record(service, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields, service),
						   Record, Name, Val)
			  end,
			  #service{}, DecodedTuples),
    Record1;
to_record(generic_services, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       generic_services),
						   Record, Name, Val)
			  end,
			  #generic_services{}, DecodedTuples),
    Record1;
to_record(generic, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields, generic),
						   Record, Name, Val)
			  end,
			  #generic{}, DecodedTuples),
    Record1;
to_record(mysql_summary, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       mysql_summary),
						   Record, Name, Val)
			  end,
			  #mysql_summary{}, DecodedTuples),
    Record1;
to_record(mysql_traffic, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       mysql_traffic),
						   Record, Name, Val)
			  end,
			  #mysql_traffic{}, DecodedTuples),
    Record1;
to_record(mysql_statement, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       mysql_statement),
						   Record, Name, Val)
			  end,
			  #mysql_statement{}, DecodedTuples),
    Record1;
to_record(mysql_replication, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       mysql_replication),
						   Record, Name, Val)
			  end,
			  #mysql_replication{}, DecodedTuples),
    Record1;
to_record(mysql_table, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       mysql_table),
						   Record, Name, Val)
			  end,
			  #mysql_table{}, DecodedTuples),
    Record1;
to_record(mysql_tables, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       mysql_tables),
						   Record, Name, Val)
			  end,
			  #mysql_tables{}, DecodedTuples),
    Record1;
to_record(mysql_role_status, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       mysql_role_status),
						   Record, Name, Val)
			  end,
			  #mysql_role_status{}, DecodedTuples),
    Record1;
to_record(mysql_seconds_behind_master, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       mysql_seconds_behind_master),
						   Record, Name, Val)
			  end,
			  #mysql_seconds_behind_master{}, DecodedTuples),
    Record1;
to_record(mysql, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields, mysql),
						   Record, Name, Val)
			  end,
			  #mysql{}, DecodedTuples),
    Record1;
to_record(serving_request, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       serving_request),
						   Record, Name, Val)
			  end,
			  #serving_request{}, DecodedTuples),
    Record1;
to_record(domain_info, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       domain_info),
						   Record, Name, Val)
			  end,
			  #domain_info{}, DecodedTuples),
    Record1;
to_record(serving_adimage, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       serving_adimage),
						   Record, Name, Val)
			  end,
			  #serving_adimage{}, DecodedTuples),
    Record1;
to_record(serving_log_info, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       serving_log_info),
						   Record, Name, Val)
			  end,
			  #serving_log_info{}, DecodedTuples),
    Record1;
to_record(serving_traffic, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       serving_traffic),
						   Record, Name, Val)
			  end,
			  #serving_traffic{}, DecodedTuples),
    Record1;
to_record(serving_engine_status, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       serving_engine_status),
						   Record, Name, Val)
			  end,
			  #serving_engine_status{}, DecodedTuples),
    Record1;
to_record(serving, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields, serving),
						   Record, Name, Val)
			  end,
			  #serving{}, DecodedTuples),
    Record1;
to_record(daemon_webserver_status, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       daemon_webserver_status),
						   Record, Name, Val)
			  end,
			  #daemon_webserver_status{}, DecodedTuples),
    Record1;
to_record(daemon_daemon_status, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       daemon_daemon_status),
						   Record, Name, Val)
			  end,
			  #daemon_daemon_status{}, DecodedTuples),
    Record1;
to_record(daemon_login_status, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       daemon_login_status),
						   Record, Name, Val)
			  end,
			  #daemon_login_status{}, DecodedTuples),
    Record1;
to_record(daemon_adserv_status, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       daemon_adserv_status),
						   Record, Name, Val)
			  end,
			  #daemon_adserv_status{}, DecodedTuples),
    Record1;
to_record(daemon_errorlog_status, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       daemon_errorlog_status),
						   Record, Name, Val)
			  end,
			  #daemon_errorlog_status{}, DecodedTuples),
    Record1;
to_record(daemon, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields, daemon),
						   Record, Name, Val)
			  end,
			  #daemon{}, DecodedTuples),
    Record1;
to_record(report_process_info, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       report_process_info),
						   Record, Name, Val)
			  end,
			  #report_process_info{}, DecodedTuples),
    Record1;
to_record(report_process_speed, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       report_process_speed),
						   Record, Name, Val)
			  end,
			  #report_process_speed{}, DecodedTuples),
    Record1;
to_record(report_wait_process_log_num, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       report_wait_process_log_num),
						   Record, Name, Val)
			  end,
			  #report_wait_process_log_num{}, DecodedTuples),
    Record1;
to_record(report, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields, report),
						   Record, Name, Val)
			  end,
			  #report{}, DecodedTuples),
    Record1;
to_record(monitor_msg, DecodedTuples) ->
    Record1 = lists:foldr(fun ({_FNum, Name, Val},
			       Record) ->
				  set_record_field(record_info(fields,
							       monitor_msg),
						   Record, Name, Val)
			  end,
			  #monitor_msg{}, DecodedTuples),
    Record1.

decode_extensions(Record) -> Record.

decode_extensions(_Types, [], Acc) ->
    dict:from_list(Acc);
decode_extensions(Types, [{Fnum, Bytes} | Tail], Acc) ->
    NewAcc = case lists:keyfind(Fnum, 1, Types) of
	       {Fnum, Name, Type, Opts} ->
		   {Value1, Rest1} = case lists:member(is_record, Opts) of
				       true ->
					   {{FNum, V}, R} =
					       protobuffs:decode(Bytes, bytes),
					   RecVal = decode(Type, V),
					   {RecVal, R};
				       false ->
					   case lists:member(repeated_packed,
							     Opts)
					       of
					     true ->
						 {{FNum, V}, R} =
						     protobuffs:decode_packed(Bytes,
									      Type),
						 {V, R};
					     false ->
						 {{FNum, V}, R} =
						     protobuffs:decode(Bytes,
								       Type),
						 {unpack_value(V, Type), R}
					   end
				     end,
		   case lists:member(repeated, Opts) of
		     true ->
			 case lists:keytake(FNum, 1, Acc) of
			   {value, {FNum, Name, List}, Acc1} ->
			       decode(Rest1, Types,
				      [{FNum, Name,
					lists:reverse([int_to_enum(Type, Value1)
						       | lists:reverse(List)])}
				       | Acc1]);
			   false ->
			       decode(Rest1, Types,
				      [{FNum, Name, [int_to_enum(Type, Value1)]}
				       | Acc])
			 end;
		     false ->
			 [{Fnum,
			   {optional, int_to_enum(Type, Value1), Type, Opts}}
			  | Acc]
		   end;
	       false -> [{Fnum, Bytes} | Acc]
	     end,
    decode_extensions(Types, Tail, NewAcc).

set_record_field(Fields, Record, '$extensions',
		 Value) ->
    Decodable = [],
    NewValue = decode_extensions(element(1, Record),
				 Decodable, dict:to_list(Value)),
    Index = list_index('$extensions', Fields),
    erlang:setelement(Index + 1, Record, NewValue);
set_record_field(Fields, Record, Field, Value) ->
    Index = list_index(Field, Fields),
    erlang:setelement(Index + 1, Record, Value).

list_index(Target, List) -> list_index(Target, List, 1).

list_index(Target, [Target | _], Index) -> Index;
list_index(Target, [_ | Tail], Index) ->
    list_index(Target, Tail, Index + 1);
list_index(_, [], _) -> -1.

extension_size(_) -> 0.

has_extension(_Record, _FieldName) -> false.

get_extension(_Record, _FieldName) -> undefined.

set_extension(Record, _, _) -> {error, Record}.

