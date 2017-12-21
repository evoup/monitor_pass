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
	"bufio"
)

var StartTime = time.Now()

var version = flag.Bool("version", false, "0.1")
var Debug_level = flag.Int("d", 4, "-d=4")

func Init() *log.Logger {

	//var version *bool
	//version = flag.Bool("version", false, "0.1")
	//debug_level := flag.Int("d", 4, "-d=4")
	flag.Parse()
	if *version {
		fmt.Printf("Version %s\n", inc.CLIENT_VERSION)
		os.Exit(0)
	}
	common.Debug_level = *Debug_level

	hLog := common.LogInit()
	common.Log(hLog, "core.Init][Initiating server........................", 4, *Debug_level)
	/** make sure only one process running **/
	pid_file := inc.PROC_ROOT + "/" + inc.RUN_SUBPATH + inc.PROC_NAME + ".pid"
	common.MakeDir(inc.PROC_ROOT+"/"+inc.RUN_SUBPATH, "0755")
	pid := common.FileGetContent(pid_file)
	if pid == "" {
		common.FilePutContent(pid_file, fmt.Sprintf("%d", os.Getpid()))
	}
	if false == common.SingleProc(pid_file) {
		common.Log(hLog, "core.Init][last upload process exists", 4, *Debug_level)
		os.Exit(0)
	} else {
		common.Log(hLog, "core.Init][single proc check ok", 4, *Debug_level)
		common.Daemonize(0, 1)
	}

	bool_existed, _ := common.FileExists(inc.PROC_ROOT + "/" + inc.CONF_FILE)
	if bool_existed {
		common.Log(hLog, "core.Init][conf and work dir existed", 4, *Debug_level)
	} else {
		wd, _ := os.Getwd()
		common.Log(hLog, "core.Init][current dir:"+wd, 4, *Debug_level)
		common.MakeDir(inc.PROC_ROOT, "0755")
		common.MakeDir(inc.PROC_ROOT+"/"+inc.CONF_SUBPATH, "0755")
		common.MakeDir(inc.PROC_ROOT+"/"+inc.WORK_SUBPATH, "0755")
		common.Log(hLog, "core.Init][build configuration file,done. run again", 4, *Debug_level)
		//WriteConf(wd + "/docs/conf/madmonitor2.conf.in")
		WriteConf(inc.PROC_ROOT + "/" + inc.CONF_SUBPATH + inc.CONF_FILE)
	}
	return hLog
}

func WriteConf(confFileIn string) error {
	conf := inc.GenConf()
	file, err := os.Create(confFileIn)
	if err != nil {
		return err
	}
	defer file.Close()
	w := bufio.NewWriter(file)
	for k, v := range conf {
		fmt.Fprintln(w, k + "=" + v)
	}
	return w.Flush()
}

// writeLines writes the lines to the given file.
func writeLines(lines []string, path string) error {
	file, err := os.Create(path)
	if err != nil {
		return err
	}
	defer file.Close()

	w := bufio.NewWriter(file)
	for _, line := range lines {
		fmt.Fprintln(w, line)
	}
	return w.Flush()
}

