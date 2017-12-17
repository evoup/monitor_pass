/**
 *Project: madmonitor2 
 *Name: inc.go
 *Auther: yinjia evoex123@gmail.com
 *Create:
 *Last Modified:
 */
package inc

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
    __PROC_LIFE    = 3600
    __SLEEP        = 10
    __LOGTAG_READ  = "MadRead"
    __LOGTAG_DELIVER = "MadDeliver"
    __LOGTAG_PF = "pf_monitor"
    __LOGTAG_LOG = "access_monitor"
)

var confInfo map[string] string
    /*confInfo = make(map[string] string)*/
    /*confInfo["server_name"] = ""*/
    /*confInfo["sleep"] = __SLEEP*/
    /*confInfo["upload_url"] = ""*/
    /*confInfo["upload_host"] = ""*/
    /*confInfo["upload_port"] = ""*/
    /*confInfo["upload_version"] = ""*/
    /*confInfo["upload_suffix"] = ""*/
