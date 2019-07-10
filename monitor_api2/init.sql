INSERT INTO `monitor`.`template` (`name`) VALUES ('Template OS Linux');
INSERT INTO `monitor`.`template` (`name`) VALUES ('Template SNMP Interfaces');
INSERT INTO `monitor`.`template` (`name`) VALUES ('Template SNMP Generic');
INSERT INTO `monitor`.`template` (`name`) VALUES ('Template SNMP Device');
INSERT INTO `monitor`.`template` (`name`) VALUES ('Template SNMP OS Windows');
INSERT INTO `monitor`.`template` (`name`) VALUES ('Template SNMP Disks');
INSERT INTO `monitor`.`template` (`name`) VALUES ('Template SNMP OS Linux');
INSERT INTO `monitor`.`template` (`name`) VALUES ('Template SNMP Processors');
INSERT INTO `monitor`.`template` (`name`) VALUES ('Template App MySQL');

INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Number of processes', '0', '60', 'Total number of processes in any state.', '', 'proc.num[]', '0', '', '0', '1', '0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Processor load (1 min average per core)', '0', '60', 'The processor load is calculated as system CPU load divided by number of CPU cores.', '', 'system.cpu.load[percpu,avg1]', '0', '', '0','1', '0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Number of running processes', '0', '60', 'Number of processes in running state.', '', 'proc.num[,,run]', '0', '', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Free swap space', '0', '60', '', '', 'system.swap.size[,free]', '0', 'B', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Number of logged in users', '0', '60', 'Number of users who are currently logged in.', '', 'system.users.num', '0', '', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Checksum of /etc/passwd', '0', '3600', 'Number of users who are currently logged in.', '', 'vfs.file.cksum[/etc/passwd]', '0', '', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Agent ping', '0', '60', 'The agent always returns 1 for this item. It could be used in combination with nodata() for availability check.', '', 'agent.ping', '0', '', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('System uptime', '0', '600', '', '', 'system.uptime', '0', 'uptime', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Total memory', '0', '3600', '', '', 'vm.memory.size[total]', '0', 'B', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Total swap space', '0', '3600', '', '', 'system.swap.size[,total]', '0', 'B', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Maximum number of processes', '0', '3600', 'It could be increased by using sysctrl utility or modifying file /etc/sysctl.conf.', '', 'kernel.maxproc', '0', '', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Maximum number of opened files', '0', '3600', 'It could be increased by using sysctrl utility or modifying file /etc/sysctl.conf.', '', 'kernel.maxfiles', '0', '', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Host name', '0', '3600', 'System host name.', '', 'system.hostname', '0', '', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('System information', '0', '3600', 'The information as normally returned by ''uname -a''.', '', 'system.uname', '0', '', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Host boot time', '0', '600', '', '', 'system.boottime', '0', 'unixtime', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Free swap space in %', '0', '60', '', '', 'system.swap.size[,pfree]', '0', '%', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Host local time', '0', '60', '', '', 'system.localtime', '0', 'unixtime', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('CPU idle time', '0', '60', 'The time the CPU has spent doing nothing.', '', 'system.cpu.util[,idle]', '0', '%', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('CPU user time', '0', '60', 'The time the CPU has spent running users'' processes that are not niced.', '', 'system.cpu.util[,user]', '0', '%', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('CPU nice time', '0', '60', 'The time the CPU has spent running users'' processes that have been niced.', '', 'system.cpu.util[,nice]', '0', '%', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('CPU system time', '0', '60', 'The time the CPU has spent running the kernel and its processes.', '', 'system.cpu.util[,system]', '0', '%', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('CPU iowait time', '0', '60', 'Amount of time the CPU has been waiting for I/O to complete.', '', 'system.cpu.util[,iowait]', '0', '%', '0','1','0');
INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('Available memory', '0', '60', 'Available memory is defined as free+cached+buffers memory.', '', 'vm.memory.size[available]', '0', 'B', '0','1','0');

INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('MySQL status', '0', '60', 'It requires user parameter mysql.ping, which is defined in userparameter_mysql.conf.

0 - MySQL server is down
1 - MySQL server is up', '', 'mysql.ping', '0', '', '0','9','0');

INSERT INTO `monitor`.`item` (`name`, `data_type`, `delay`, `desc`, `error`, `key`, `multiplier`, `unit`, `host_id`,`template_id`, `delta`) VALUES ('MySQL bytes received per second', '0', '60', 'It requires user parameter mysql.ping, which is defined in userparameter_mysql.conf.

0 - MySQL server is down
1 - MySQL server is up', '', 'mysql.status[Bytes_received]', '0', 'Bps', '0','9', '1');



INSERT INTO `monitor`.`trigger` (`desc`, `expression`, `template_id`) VALUES ('', '{1}>300', '1');
INSERT INTO `monitor`.`trigger` (`desc`, `expression`, `template_id`) VALUES ('', '{3}>5', '1');


INSERT INTO `monitor`.`function` (`name`, `parameter`, `item_id`, `trigger_id`) VALUES ('avg', '5m', '1', '1');
INSERT INTO `monitor`.`function` (`name`, `parameter`, `item_id`, `trigger_id`) VALUES ('avg', '5m', '2', '1');

