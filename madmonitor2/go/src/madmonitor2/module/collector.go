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
	"madmonitor2/inc"
	"madmonitor2/utils"
	"log"
	"os"
	"io/ioutil"
	"path/filepath"
)
var GENERATION = inc.GERERATION
var COLLECTORS = inc.COLLECTORS
var HLog = inc.HLog
func Register_collector(name string, interval int, filename string, generation int) {
	mtime := utils.GetMtime(filename)
	lastspawn := 0
	collector := inc.Collector{name, interval, filename, mtime, lastspawn, false, generation}
	COLLECTORS[name] = collector
}


func populate_collectors() {
	dir, err := filepath.Abs(filepath.Dir(os.Args[0]))
	if err != nil {
		utils.Log(HLog, "populate_collectors][err:" + err.Error(), 1, 2)
	}
	dirname := dir + "/plugin/"
	files, err := ioutil.ReadDir(dirname)
	if err != nil {
		log.Fatal(err)
		os.Exit(1)
	}
	GENERATION += 1
	generation := GENERATION
	for _, file := range files {
		file.Name()
		mtime := utils.GetMtime(dirname + file.Name())
		// if this collector is already 'known', then check if it's
		// been updated (new mtime) so we can kill off the old one
		// (but only if it's interval 0, else we'll just get
		// it next time it runs)
		if _, ok := COLLECTORS[file.Name()]; ok {
			col := COLLECTORS[file.Name()]
			col.Generation = generation
			if col.Mtime < mtime {
				utils.Log(HLog, "populate_collectors][" + col.Name + "has been updated on disk ", 1, 2)
				col.Mtime = mtime
				// TODO shutdown, because go can`t close so, we should fully exit
				//utils.Log(HLog, "populate_collectors][Respawning " + col.Name, 1, 2)
			}
		} else {
			Register_collector(file.Name(), 0, dirname + file.Name(), GENERATION)
		}
	}
}