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
	"madmonitor2/utils"
	"madmonitor2/module"
	"madmonitor2/inc"
	"fmt"
	"time"
	"io/ioutil"
	"log"
	"plugin"
	"os"
)

var COLLECTORS =inc.COLLECTORS
var GENERATION = 0
var HLog, Conf = module.Init()
type ICollector interface {
	Collect()
}

func main() {

	host, _ := Conf.GetString("ServerName")
	fmt.Println(host)
	utils.Log(HLog, "main][init done", 1, 4)
	fmt.Println(module.StartTime)

	main_loop()
}

// 执行我们模块的收集方法
// main_loop(options, modules, sender, tags)
func main_loop() {
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



func populate_collectors() {
	dirname := "./plugin/"
	files, err := ioutil.ReadDir(dirname)
	if err != nil {
		log.Fatal(err)
		os.Exit(1)
	}
	GENERATION += 1
	for _, file := range files {
		file.Name()
		mtime := utils.GetMtime(dirname + file.Name())
		// if this collector is already 'known', then check if it's
		// been updated (new mtime) so we can kill off the old one
		// (but only if it's interval 0, else we'll just get
		// it next time it runs)
		if _, ok := COLLECTORS[file.Name()]; ok {
			col := COLLECTORS[file.Name()]
			col.Generation = GENERATION
			if col.Mtime < mtime {
				utils.Log(HLog, "populate_collectors][" + col.Name + "has been updated on disk ", 1, 2)
				col.Mtime = mtime
				utils.Log(HLog, "populate_collectors][Respawning " + col.Name, 1, 2)
			}
		} else {
			module.Register_collector(file.Name(), 0, dirname + file.Name(), GENERATION)
		}
	}
}

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
