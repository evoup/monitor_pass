/**
 *Project: madmonitor2 
 *Name: inc.go
 *Auther: yinjia evoex123@gmail.com
 *Create:
 *Last Modified:
 */
package inc

import (
	"fmt"
	"os"
)

const (
    LOG_SUFFIX   = "madmonitor2"
    SVN_VERSION  = "1630"
    PROC_NAME    = "madmonitor2"
    PROC_ROOT    = "/services/monitor2_deal"
    CONF_FILE    = "madmonitor2.ini"
    RUN_SUBPATH  = "run/"
    CONF_SUBPATH = "conf/"
    STATUS_SUBPATH = "status/"
    WORK_SUBPATH = "work/"
    __PROC_LIFE    = "3600"
    __SLEEP        = "10"
    __LOGTAG_READ  = "MadRead"
    __LOGTAG_DELIVER = "MadDeliver"
    __LOGTAG_PF = "pf_monitor"
    __LOGTAG_LOG = "access_monitor"
)


func gConf() {
	data, _ := os.Hostname()
	proc_life := __PROC_LIFE
	sleep := __SLEEP
	var mapConf = map[string]string{
		"server_name": data,
		"proc_life" : proc_life,
		"sleep" : sleep,
		"upload_url" : "",
		"upload_host" : "172.18.0.30",
		"upload_port"  : "80",
		"upload_version"  : "monitor_server2r1_1",
		"upload_suffix"  : "m1",
		"send_host"  : "172.18.0.1",
		"send_port"   : "8090",
	}
	fmt.Print(mapConf)
}
