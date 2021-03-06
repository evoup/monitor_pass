INSERT INTO `monitor`.`template` (`name`) VALUES ('Template OS Linux');
INSERT INTO `monitor`.`template` (`name`) VALUES ('Template SNMP Interfaces');
INSERT INTO `monitor`.`template` (`name`) VALUES ('Template SNMP Generic');
INSERT INTO `monitor`.`template` (`name`) VALUES ('Template SNMP Device');
INSERT INTO `monitor`.`template` (`name`) VALUES ('Template SNMP OS Windows');
INSERT INTO `monitor`.`template` (`name`) VALUES ('Template SNMP Disks');
INSERT INTO `monitor`.`template` (`name`) VALUES ('Template SNMP OS Linux');
INSERT INTO `monitor`.`template` (`name`) VALUES ('Template SNMP Processors');
INSERT INTO `monitor`.`template` (`name`) VALUES ('Template App MySQL');
INSERT INTO `monitor`.`template` (`name`) VALUES ('Template JMX Generic');

INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Number of processes', '0', '60', 'Total number of processes in any state.', '', 'proc.num[]', '1', '', '0', '1', '0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Processor load (1 min average per core)', '0', '60', 'The processor load is calculated as system CPU load divided by number of CPU cores.', '', 'system.cpu.load[percpu,avg1]', '1', '', '0','1', '0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Number of running processes', '0', '60', 'Number of processes in running state.', '', 'proc.num[,,run]', '1', '', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Free swap space', '0', '60', '', '', 'system.swap.size[,free]', '1', 'B', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Number of logged in users', '0', '60', 'Number of users who are currently logged in.', '', 'system.users.num', '1', '', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Checksum of /etc/passwd', '0', '3600', 'Number of users who are currently logged in.', '', 'vfs.file.cksum[/etc/passwd]', '1', '', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Agent ping', '0', '60', 'The agent always returns 1 for this item. It could be used in combination with nodata() for availability check.', '', 'agent.ping', '1', '', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('System uptime', '0', '600', '', '', 'system.uptime', '1', 'uptime', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Total memory', '0', '3600', '', '', 'vm.memory.size[total]', '1', 'B', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Total swap space', '0', '3600', '', '', 'system.swap.size[,total]', '1', 'B', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Maximum number of processes', '0', '3600', 'It could be increased by using sysctrl utility or modifying file /etc/sysctl.conf.', '', 'kernel.maxproc', '1', '', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Maximum number of opened files', '0', '3600', 'It could be increased by using sysctrl utility or modifying file /etc/sysctl.conf.', '', 'kernel.maxfiles', '1', '', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Host name', '0', '3600', 'System host name.', '', 'system.hostname', '1', '', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('System information', '0', '3600', 'The information as normally returned by ''uname -a''.', '', 'system.uname', '1', '', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Host boot time', '0', '600', '', '', 'system.boottime', '1', 'unixtime', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Free swap space in %', '0', '60', '', '', 'system.swap.size[,pfree]', '1', '%', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Host local time', '0', '60', '', '', 'system.localtime', '1', 'unixtime', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('CPU idle time', '0', '60', 'The time the CPU has spent doing nothing.', '', 'system.cpu.util[,idle]', '1', '%', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('CPU user time', '0', '60', 'The time the CPU has spent running users'' processes that are not niced.', '', 'system.cpu.util[,user]', '1', '%', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('CPU nice time', '0', '60', 'The time the CPU has spent running users'' processes that have been niced.', '', 'system.cpu.util[,nice]', '1', '%', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('CPU system time', '0', '60', 'The time the CPU has spent running the kernel and its processes.', '', 'system.cpu.util[,system]', '1', '%', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('CPU iowait time', '0', '60', 'Amount of time the CPU has been waiting for I/O to complete.', '', 'system.cpu.util[,iowait]', '1', '%', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Available memory', '0', '60', 'Available memory is defined as free+cached+buffers memory.', '', 'vm.memory.size[available]', '1', 'B', '0','1','0');

INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('MySQL status', '0', '60', 'It requires user parameter mysql.ping, which is defined in userparameter_mysql.conf.

0 - MySQL server is down
1 - MySQL server is up', '', 'mysql.ping', '1', '', '0','9','0');

INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('MySQL bytes received per second', '0', '60', 'It requires user parameter mysql.ping, which is defined in userparameter_mysql.conf.

0 - MySQL server is down
1 - MySQL server is up', '', 'mysql.status[Bytes_received]', '0', 'Bps', '0','9', '1');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Processor load (5 min average per core)', '0', '60', 'The processor load is calculated as system CPU load divided by number of CPU cores.', '', 'system.cpu.load[percpu,avg5]', '1', '', '0','1', '0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Processor load (15 min average per core)', '0', '60', 'The processor load is calculated as system CPU load divided by number of CPU cores.', '', 'system.cpu.load[percpu,avg15]', '1', '', '0','1', '0');

INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`, `template_id`, `delta`) VALUES ('Used disk space on $1', '0', '60', 'Used disk space on $1', '', 'vfs.fs.size[{#FSNAME},pused]', '1', '%', '0', '1', '0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`, `template_id`, `delta`) VALUES ('Total threads', '0', '60', 'Total threads', '', 'proc.loadavg.total_threads', '1', '', '0', '1', '0');

INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('cl Loaded Class Count', '0', '60', '', '', 'jmx["java.lang:type=ClassLoading",LoadedClassCount]', '1', '', '0','10', '0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('cl Total Loaded Class Count', '0', '60', '', '', 'jmx["java.lang:type=ClassLoading",TotalLoadedClassCount]', '1', '', '0','10', '0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('cl Unloaded Class Count', '0', '60', '', '', 'jmx["java.lang:type=ClassLoading",UnloadedClassCount]', '1', '', '0','10', '0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('comp Name of the current JIT compiler', '0', '3600', '', '', 'jmx["java.lang:type=Compilation",Name]', '1', '', '0','10', '0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('comp Accumulated time spent in compilation', '0', '60', '', '', 'jmx["java.lang:type=Compilation",TotalCompilationTime]', '1', '', '0','10', '0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('gc ConcurrentMarkSweep number of collections per second', '0', '60', '', '', 'jmx["java.lang:type=GarbageCollector,name=ConcurrentMarkSweep",CollectionCount]', '1', '', '0','10', '0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('gc ConcurrentMarkSweep accumulated time spent in collection', '0', '60', '', '', 'jmx["java.lang:type=GarbageCollector,name=ConcurrentMarkSweep",CollectionTime]', '1', '', '0','10', '0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('gc Copy number of collections per second', '0', '60', '', '', 'jmx["java.lang:type=GarbageCollector,name=Copy",CollectionCount]', '1', '', '0','10', '0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('gc Copy accumulated time spent in collection', '0', '60', '', '', 'jmx["java.lang:type=GarbageCollector,name=Copy",CollectionTime]', '1', '', '0','10', '0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('gc MarkSweepCompact number of collections per second', '0', '60', '', '', 'jmx["java.lang:type=GarbageCollector,name=MarkSweepCompact",CollectionCount]', '1', '', '0','10', '0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('gc MarkSweepCompact accumulated time spent in collection', '0', '60', '', '', 'jmx["java.lang:type=GarbageCollector,name=MarkSweepCompact",CollectionTime]', '1', '', '0','10', '0');

INSERT INTO `monitor`.`trigger` (`name`, `expression`, `template_id`, `level`) VALUES ('Too many processes on {HOST.NAME}', '{1}>300', '1', '1');
INSERT INTO `monitor`.`trigger` (`name`, `expression`, `template_id`, `level`) VALUES ('Processor load is too high on {HOST.NAME}', '{2}>5', '1', '1');
INSERT INTO `monitor`.`trigger` (`name`, `expression`, `template_id`, `level`) VALUES ('Too many processes running on {HOST.NAME}', '{3}>30', '1', '1');
INSERT INTO `monitor`.`trigger` (`name`, `expression`, `template_id`, `level`) VALUES ('Host information was changed on {HOST.NAME}', '{4}>0', '1', '1');
INSERT INTO `monitor`.`trigger` (`name`, `expression`, `template_id`, `desc`, `level`) VALUES ('Lack of free swap space on {HOST.NAME}', '{5}<50', '1', 'It probably means that the systems requires more physical memory.', '1');


INSERT INTO `monitor`.`function` (`name`, `parameter`, `item_id`, `trigger_id`) VALUES ('avg', '5m', '1', '1');
INSERT INTO `monitor`.`function` (`name`, `parameter`, `item_id`, `trigger_id`) VALUES ('avg', '5m', '2', '2');
INSERT INTO `monitor`.`function` (`name`, `parameter`, `item_id`, `trigger_id`) VALUES ('avg', '5m', '3', '3');
INSERT INTO `monitor`.`function` (`name`, `parameter`, `item_id`, `trigger_id`) VALUES ('diff', '0', '14', '4');
INSERT INTO `monitor`.`function` (`name`, `parameter`, `item_id`, `trigger_id`) VALUES ('last', '0', '16', '5');


INSERT INTO `diagram` (`id`,`name`,`width`,`height`,`template_id`) VALUES (1,'cpu负载','100%','230','1');
INSERT INTO `diagram` (`id`,`name`,`width`,`height`,`template_id`) VALUES (2,'内存使用','100%','230','1');
INSERT INTO `diagram` (`id`,`name`,`width`,`height`,`template_id`) VALUES (3,'所有进程数','100%','230','1');
INSERT INTO `diagram` (`id`,`name`,`width`,`height`,`template_id`) VALUES (4,'所有线程数','100%','230','1');
INSERT INTO `diagram_item` (`id`,`diagram_id`,`item_id`) VALUES (1,1,2);
INSERT INTO `diagram_item` (`id`,`diagram_id`,`item_id`) VALUES (2,1,26);
INSERT INTO `diagram_item` (`id`,`diagram_id`,`item_id`) VALUES (3,1,27);
INSERT INTO `diagram_item` (`id`,`diagram_id`,`item_id`) VALUES (4,2,23);
INSERT INTO `diagram_item` (`id`,`diagram_id`,`item_id`) VALUES (5,3,1);
INSERT INTO `diagram_item` (`id`,`diagram_id`,`item_id`) VALUES (6,4,29);


INSERT INTO `monitor`.`general_config` (`send_warn`, `stop_command`) VALUES ('1', 'rm -rf\nreboot\npoweroff\nsu');
INSERT INTO `monitor`.`notification_mode` (`type`, `name`, `smtp_server`, `smtp_domain`, `smtp_port`, `username`, `passwd`, `enabled`) VALUES ('1', 'email', 'smtp.company.com', 'company.com', '25', 'user', 'passwd', '1');
INSERT INTO `monitor`.`notification_mode` (`type`, `name`, `wechat_agent_id`, `wechat_corp_id`, `wechat_secret`, `enabled`) VALUES ('2', '企业微信', 'agent_id', 'corp_id', 'secret', '1');


INSERT INTO `operation` (`id`,`name`,`status`,`period`,`title`,`content`) VALUES (1,'发送告警至运维人员',1,3600,'{TRIGGER.STATUS}: {TRIGGER.NAME}','Trigger: {TRIGGER.NAME}\nTrigger status: {TRIGGER.STATUS}\nTrigger severity: {TRIGGER.SEVERITY}\nTrigger URL: {TRIGGER.URL}\n\nItem values:\n\n1. {ITEM.NAME1} ({HOST.NAME1}:{ITEM.KEY1}): {ITEM.VALUE1}\n2. {ITEM.NAME2} ({HOST.NAME2}:{ITEM.KEY2}): {ITEM.VALUE2}\n3. {ITEM.NAME3} ({HOST.NAME3}:{ITEM.KEY3}): {ITEM.VALUE3}\n\nOriginal event ID: {EVENT.ID}');

INSERT INTO `monitor`.`operation_condition` (`id`, `type`, `operator`, `value`, `operation_id`) VALUES (1, '0', '0', '0', '1');
