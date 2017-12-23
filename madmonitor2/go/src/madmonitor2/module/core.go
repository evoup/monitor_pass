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
	"madmonitor2/config"
)
// start unix timestamp
var StartTime = time.Now().UnixNano()/1000/1000/1000

// flag define
var version = flag.Bool("version", false, "")
var Debug_level = flag.Int("d", 4, "-d=4")
var daemonize = flag.Bool("daemonize", false, "Run as a background daemon.")
var daemonizeShort = flag.Bool("D", false, "Run as a background daemon.")
var Pidfile = flag.String("pidfile", "/var/run/madmonitor2.pid", "Write our pidfile")

func Init() *log.Logger {
	flag.Parse()
	if *version {
		fmt.Printf("Version %s\n", inc.CLIENT_VERSION)
		os.Exit(0)
	}

	common.Debug_level = *Debug_level

	hLog := common.GetLogger()
	common.Log(hLog, "core.Init][Initiating server........................", 4, *Debug_level)
	/** make sure only one process running **/
	var pid_file = *Pidfile
	pid := common.FileGetContent(pid_file)
	if pid == "" {
		common.FilePutContent(pid_file, fmt.Sprintf("%d", os.Getpid()))
	}
	if false == common.SingleProc(pid_file) {
		common.Log(hLog, "core.Init][last upload process exists", 4, *Debug_level)
		os.Exit(0)
	} else {
		common.Log(hLog, "core.Init][single proc check ok", 4, *Debug_level)
		if *daemonize || *daemonizeShort {
			common.Daemonize(0, 1, pid_file)
		}
	}


	bool_existed := common.FileExists(inc.PROC_ROOT + "/" + inc.CONF_SUBPATH + inc.CONF_FILE)
	fmt.Println(inc.PROC_ROOT + "/" + inc.CONF_SUBPATH + inc.CONF_FILE)
	if bool_existed {
		common.Log(hLog, "core.Init][conf and work dir existed", 4, *Debug_level)
	} else {
		common.Log(hLog, "core.Init][conf and work dir not existed", 4, *Debug_level)
		wd, _ := os.Getwd()
		common.Log(hLog, "core.Init][current dir:"+wd, 4, *Debug_level)
		common.MakeDir(inc.PROC_ROOT, "0755")
		common.MakeDir(inc.PROC_ROOT+"/"+inc.CONF_SUBPATH, "0755")
		common.MakeDir(inc.PROC_ROOT+"/"+inc.WORK_SUBPATH, "0755")
		writeConf(inc.PROC_ROOT + "/" + inc.CONF_SUBPATH + inc.CONF_FILE)
		common.Log(hLog, "core.Init][build configuration file,done. run again", 4, *Debug_level)
		os.Exit(0)
	}
	return hLog
}

func writeConf(confFileIn string) error {
	conf := config.GetDefaults()
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



