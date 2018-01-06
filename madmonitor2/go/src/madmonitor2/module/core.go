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
)

// start unix timestamp
var StartTime = time.Now().Unix()

// flag define
var version = flag.Bool("version", false, "")
var Debug_level = flag.Int("d", 4, "-d=4")
var Pidfile = flag.String("pidfile", "/var/run/madmonitor2.pid", "Write our pidfile")
var daemonize = flag.Bool("daemonize", false, "Run as a background daemon.")
var daemonizeShort = flag.Bool("D", false, "Run as a background daemon.")
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
		utils.Daemonize(0, 1, pid_file)
	}
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

// load implemented collectors key name of collector,value interval
func loadCollectors() {
	inc.VALID_COLLECTORS["sysload"]=0

}