/**
 *Project: madmonitor2 
 *Name: core_init.go
 *Auther: yinjia evoex123@gmail.com
 *Create:
 *Last Modified:
 */

package core

import (
    "log"
    "os"
    "madmonitor2/fun"
    "madmonitor2/inc"
    "fmt"
    "time"
    "flag"
)

var StartTime = time.Now()

func Init() *log.Logger {
	var version *bool
	version = flag.Bool("version", false, "0.1")

	flag.Parse()

	if *version {
		fmt.Printf("Version %s\n", inc.CLIENT_VERSION)
		os.Exit(0)
	}

    hLog := common.LogInit()
    common.Log(hLog, "core.Init][Initiating server........................")
    /** make sure only one process running **/
    pid_file:=inc.PROC_ROOT+"/"+inc.RUN_SUBPATH+inc.PROC_NAME+".pid"
    common.MakeDir(inc.PROC_ROOT+"/"+inc.RUN_SUBPATH, "0755")
    pid:=common.FileGetContent(pid_file)
    if pid=="" {
        common.FilePutContent(pid_file,fmt.Sprintf("%d",os.Getpid()))
    }
    if false==common.SingleProc(pid_file) {
        common.Log(hLog, "core.Init][last upload process exists")
        os.Exit(0)
    } else {
        common.Log(hLog, "core.Init][single proc check ok")
        common.Daemonize(0, 1)
    }

    bool_existed, _ := common.FileExists(inc.PROC_ROOT)
    if bool_existed {
        common.Log(hLog, "core.Init][conf and work dir existed")
    } else {
        wd, _ := os.Getwd()
        common.Log(hLog, "core.Init][current dir:"+wd)
        common.MakeDir(inc.PROC_ROOT, "0755")
        common.MakeDir(inc.PROC_ROOT+"/"+inc.CONF_SUBPATH, "0755")
        common.MakeDir(inc.PROC_ROOT+"/"+inc.WORK_SUBPATH, "0755")
        common.Log(hLog, "core.Init][build configuration file,done. run again")
        WriteConf(wd + "/docs/conf/madmonitor2.conf.in")
    }
    return hLog
}

func WriteConf(confFileIn string) {
}

