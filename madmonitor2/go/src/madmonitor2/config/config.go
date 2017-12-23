package config

import "os"
import "madmonitor2/inc"



func GetDefaults() map[string]string{
	hostname, _ := os.Hostname()
	var defaultConf = map[string]string{
		"server_name": hostname,
		"proc_life" : inc.PROC_LIFE,
		"sleep" : inc.SLEEP,
		"send_port"   : inc.SEND_PORT,
		"upload_url" : "",
		"upload_host" : "172.18.0.30",
		"upload_port"  : "80",
		"upload_version"  : "monitor_server2r1_1",
		"upload_suffix"  : "m1",
		"send_host"  : "172.18.0.1",

	}
	return defaultConf
}
