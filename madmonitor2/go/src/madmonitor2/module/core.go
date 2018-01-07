/*
 * This file is part of madmonitor2.
 * Copyright (c) 2017. Author: yinjia evoex123@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  This program is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Lesser
 * General Public License for more details.  You should have received a copy
 * of the GNU Lesser General Public License along with this program.  If not,
 * see <http://www.gnu.org/licenses/>.
 */

package module

import (
	"log"
	"os"
	"madmonitor2/utils"
	"madmonitor2/inc"
	"fmt"
	"time"
	"flag"
	"madmonitor2/config"
	"github.com/antonholmquist/jason"
	"github.com/takama/daemon"
)

// start unix timestamp
var StartTime = time.Now().Unix()

// flag define
var version = flag.Bool("version", false, "")
var Debug_level = flag.Int("d", 4, "-d=4")
var Pidfile = flag.String("pidfile", "/var/run/madmonitor2.pid", "Write our pidfile")
var daemonize = flag.Bool("daemonize", false, "Run as a background daemon.")
var daemonizeShort = flag.Bool("D", false, "Run as a background daemon.")
var Service_install  = flag.Bool("service_install", false, "")
var Service_remove = flag.Bool("service_remove", false, "")
var Service_start = flag.Bool("service_start", false, "")
var Service_stop = flag.Bool("service_stop", false, "")
var Service_status = flag.Bool("service_status", false, "")

// Service has embedded daemon
type Service struct {
	daemon.Daemon
}

func Init() (*log.Logger, *jason.Object) {
	flag.Parse()
	if *version {
		fmt.Printf("Version %s\n", inc.CLIENT_VERSION)
		os.Exit(0)
	}

	utils.Debug_level = *Debug_level

	logger := utils.GetLogger()
	utils.Log(logger, "core.Init][Initiating server........................", 4, *Debug_level)
	/** make sure only one process running **/
	var pid_file = *Pidfile
	pid := utils.FileGetContent(pid_file)
	if pid == "" {
		utils.FilePutContent(pid_file, fmt.Sprintf("%d", os.Getpid()))
	}
	if false == utils.SingleProc(pid_file) {
		utils.Log(logger, "core.Init][last upload process exists", 4, *Debug_level)
		os.Exit(0)
	}
	/** if first run, we make config folder **/
	buildConf(logger)
	object, err := parseConf()
	if err != nil {
		utils.Log(logger, "core.Init][err:"+err.Error(), 1, *Debug_level)
		os.Exit(0)
	}
	if err != nil {
		utils.Log(logger, "core.Init][err:"+err.Error(), 1, *Debug_level)
		os.Exit(0)
	}
	var optionDaedmon = (*daemonize || *daemonizeShort)
	if (optionDaedmon) {
		//utils.Daemonize(0, 1, pid_file)
	} else {

	}
	//////
	dependencies := []string{}
	srv, err := daemon.New(inc.SERVICE_NAME, inc.SERVICE_DESC, dependencies...)
	if err != nil {
		utils.Log(logger, "core.Init][err:"+err.Error(), 1, *Debug_level)
		os.Exit(1)
	}
	service := &Service{srv}
	status, err := service.Manage()
	if err != nil {
		utils.Log(logger, "core.Init][status:" + status + "][err:"+err.Error(), 1, *Debug_level)
		os.Exit(1)
	}

	//////
	loadCollectors()
	return logger, object
}

func buildConf(logger *log.Logger) {
	confExists := utils.FileExists(inc.PROC_ROOT + "/" + inc.CONF_SUBPATH + inc.CONF_FILE)
	if confExists {
		utils.Log(logger, "core.Init][conf and work dir existed", 4, *Debug_level)
	} else {
		utils.Log(logger, "core.Init][conf and work dir not existed", 4, *Debug_level)
		wd, _ := os.Getwd()
		utils.Log(logger, "core.Init][current dir:"+wd, 4, *Debug_level)
		utils.MakeDir(inc.PROC_ROOT, "0755")
		utils.MakeDir(inc.PROC_ROOT+"/"+inc.CONF_SUBPATH, "0755")
		utils.MakeDir(inc.PROC_ROOT+"/"+inc.WORK_SUBPATH, "0755")
		config.WriteDefaultsJson(inc.PROC_ROOT + "/" + inc.CONF_SUBPATH + inc.CONF_FILE)
		utils.Log(logger, "core.Init][build configuration file,done. run again", 4, *Debug_level)
		os.Exit(0)
	}
}

func parseConf() (*jason.Object, error) {
	file, err := os.Open(inc.PROC_ROOT + "/" + inc.CONF_SUBPATH + inc.CONF_FILE)
	if err == nil {
		conf, err := jason.NewObjectFromReader(file)
		if err == nil {
			return conf, nil
		}
		return nil, err
	} else {
		return nil, err
	}
}
// Manage by daemon commands or run the daemon
func (service *Service) Manage() (string, error) {

	//usage := "Usage: myservice install | remove | start | stop | status"

	// if received any kind of command, do it
	if *Service_install {
		return service.Install()
	}
	if *Service_remove {
		return service.Remove()
	}
	if *Service_start {
		return service.Start()
	}
	if *Service_stop {
		return service.Stop()
	}
	if *Service_status {
		return service.Status()
	}

	//if len(os.Args) > 1 {
	//	command := os.Args[1]
	//	switch command {
	//	case "install":
	//		return service.Install()
	//	case "remove":
	//		return service.Remove()
	//	case "start":
	//		return service.Start()
	//	case "stop":
	//		return service.Stop()
	//	case "status":
	//		return service.Status()
	//	default:
	//		return usage, nil
	//	}
	//}
	return main_loop()
}

func main_loop() (string, error) {
	// 检查collector的心跳，每10分钟一次
	next_heartbeat := int(time.Now().Unix() + 600)
	for ; ; {
		populate_collectors()
		time.Sleep(time.Second * 15)
		now := int(time.Now().Unix())
		if now > next_heartbeat {
			next_heartbeat = now + 600
		}
	}
}



// load implemented collectors key name of collector,value interval
func loadCollectors() {
	inc.VALID_COLLECTORS["sysload"] = 0

}
