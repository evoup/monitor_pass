/*
 * This file is part of madmonitor2.
 * Copyright (c) 2018. Author: yinjia evoex123@gmail.com
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
	"io/ioutil"
	"log"
	"madmonitor2/inc"
	"madmonitor2/utils"
	"os"
	"time"

	"fmt"
	"plugin"
	"strconv"
	"strings"
)

var GENERATION = inc.GERERATION
var COLLECTORS = inc.COLLECTORS
var HLog = inc.HLog

func Register_collector(name string, interval int, filename string, generation int) {
	mtime := utils.GetMtime(filename)
	lastspawn := 0
	// initialize values map
	values := make(map[string]inc.CollectorValue, 50000)
	collector := inc.Collector{name, interval, filename, mtime, lastspawn,
		0, 0, 0, true, generation, values, 0}
	COLLECTORS[name] = collector
}

func populate_collectors() {
	//dir, err := filepath.Abs(filepath.Dir(os.Args[0]))
	//if err != nil {
	//	utils.Log(HLog, "populate_collectors][err:"+err.Error(), 1, 2)
	//}
	//dirname := dir + "/plugin/"
	dirname := "/usr/local/lib/madmonitor2/"
	files, err := ioutil.ReadDir(dirname)
	if err != nil {
		log.Fatal(err)
		os.Exit(1)
	}
	GENERATION += 1
	generation := GENERATION
	for _, file := range files {
		mtime := utils.GetMtime(dirname + file.Name())
		// if this collector is already 'known', then check if it's
		// been updated (new mtime) so we can kill off the old one
		// (but only if it's interval 0, else we'll just get
		// it next time it runs)
		if _, ok := COLLECTORS[file.Name()]; ok {
			col := COLLECTORS[file.Name()]
			col.Generation = generation
			if col.Mtime < mtime {
				utils.Log(HLog, "populate_collectors]["+col.Name+"has been updated on disk ", 1, 2)
				col.Mtime = mtime
				//utils.Log(HLog, "populate_collectors][Respawning " + col.Name, 1, 2)
				// TODO shutdown, because go can`t close so, we should fully exit
				inc.Shutdown <- 1
				inc.Wg.Wait()
				close(inc.Shutdown)
				os.Exit(1)
			}
		} else {
			Register_collector(file.Name(), 0, dirname+file.Name(), GENERATION)
		}
	}
}

func spawn_children() {
	// Iterates over our defined collectors and performs the logic to
	// determine if we need to spawn, kill, or otherwise take some
	// action on them.
	for key_server, _ := range all_valid_collectors() {
		now := int(time.Now().Unix())
		col, ok := COLLECTORS[key_server]
		if ok {
			spawn_collector(col)
		}
		// I'm not very satisfied with this path.  It seems fragile and
		// overly complex, maybe we should just reply on the asyncproc
		// terminate method, but that would make the main tcollector
		// block until it dies... :|
		if col.NextKill > now {
			continue
		}
		// FIXME >>>add kill collector method
	}
}

// collectors that are not marked dead
func all_valid_collectors() map[string]inc.Collector {
	var valid_cols = map[string]inc.Collector{}
	for key_col, value_col := range all_collectors() {
		now := int(time.Now().Unix())
		if !COLLECTORS[key_col].Dead || (now-COLLECTORS[key_col].LastSpawn > 3600) {
			valid_cols[key_col] = value_col
		}
	}
	return valid_cols
}

func all_collectors() map[string]inc.Collector {
	// Generator to return all collectors.
	return COLLECTORS
}

func spawn_collector(collector inc.Collector) {
	if collector.Dead == false {
		return
	}
	// Takes a Collector object and creates a process for it.
	HLog = utils.GetLogger()
	utils.Log(HLog, "spawn_collector]["+collector.Name+"(interval:"+strconv.Itoa(collector.Interval)+") needs to be spawned", 1, 2)
	///
	mod := collector.Name
	//plug, err := plugin.Open("./plugin/" + mod)
	plug, err := plugin.Open("/usr/local/lib/madmonitor2/" + mod)
	if err != nil {
		fmt.Println(err)
		os.Exit(1)
	}
	modNameArr := strings.Split(mod, ".")            // sysload.so
	modName := strings.Title(modNameArr[0])          // Sysload
	symCollector, err := plug.Lookup(modName + "So") // SysloadSo is plugin exported name
	if err != nil {
		fmt.Println(err)
		os.Exit(1)
	}
	col := symCollector.(inc.ICollector)
	defer func() { // need to define defer first, otherwise we can`t ge panic exception
		if err := recover(); err != nil {
			fmt.Println(err) // this is panic
		}
	}()
	inc.Wg.Add(1)
	go DoCollect(col)
	///
	// The following line needs to move below this line because it is used in
	// other logic and it makes no sense to update the last spawn time if the
	// collector didn't actually start.
	now := int(time.Now().Unix())
	collector.LastSpawn = now
	// Without setting last_datapoint here, a long running check (>15s) will be
	// killed by check_children() the first time check_children is called.
	collector.LastDataPoint = collector.LastSpawn
	collector.Dead = false
	utils.Log(HLog, "spawn_collector]["+collector.Name+" spawned", 1, 2)
	COLLECTORS[collector.Name] = collector
}

func DoCollect(col inc.ICollector) {
	col.Collect()
}
