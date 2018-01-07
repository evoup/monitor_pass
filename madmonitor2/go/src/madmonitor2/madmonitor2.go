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

package main

import (
	"madmonitor2/module"
	"madmonitor2/inc"
	"fmt"

	"io/ioutil"
	"log"
	"plugin"
	"os"
)

var COLLECTORS =inc.COLLECTORS
var GENERATION = inc.GERERATION
var HLog = inc.HLog
type ICollector interface {
	Collect()
}

func main() {
	// Set up channel on which to send signal notifications.
	// We must use a buffered channel or risk missing the signal
	// if we're not ready to receive when the signal is sent.
	module.Init()
	//hLog, conf := module.Init()
	//HLog = hLog
	//host, _ := conf.GetString("ServerName")
	//fmt.Println(host)
	//utils.Log(HLog, "main][init done", 1, 4)
	//fmt.Println(module.StartTime)
//	module.Main_loop()
}

// 执行我们模块的收集方法
// main_loop(options, modules, sender, tags)





// 更新或者添加collector
func populate_collectors0() {

	GENERATION += 1
	for collector_name := range inc.VALID_COLLECTORS {
		fmt.Println(collector_name)
	}
	files, err := ioutil.ReadDir("./plugin/")
	if err != nil {
		log.Fatal(err)
	}

	for _, f := range files {
		fmt.Println(f.Name())
		mod := f.Name()
		plug, err := plugin.Open("./plugin/" + mod)
		if err != nil {
			fmt.Println(err)
			os.Exit(1)
		}
		symCollector, err := plug.Lookup("CollectorSo")
		if err != nil {
			fmt.Println(err)
			os.Exit(1)
		}
		col := symCollector.(ICollector)
		doCollect(col)
	}

}

func doCollect(col ICollector) {
	col.Collect()
}
