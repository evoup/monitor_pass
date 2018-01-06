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
    "madmonitor2/fun"
    "madmonitor2/module"
    "fmt"
    "time"
    "madmonitor2/inc"
    "io/ioutil"
    "log"
    "plugin"
    "os"
)

var COLLECTORS = map[string]string{}
var GENERATION = 0
type ICollector interface {
    Collect()
}


func main() {
    hLog, conf := core.Init()
    host, _ := conf.GetString("ServerName")
    fmt.Println(host)
    common.Log(hLog, "main][init done",1, 4)
    fmt.Println(core.StartTime)

    main_loop()
}

// 执行我们模块的收集方法
// main_loop(options, modules, sender, tags)
func main_loop() {
    // 检查collector的心跳，每10分钟一次
    next_heartbeat := int(time.Now().Unix() + 600)
    for ;; {
        populate_collectors()
        time.Sleep(time.Second * 15)
        now := int(time.Now().Unix())
        if now > next_heartbeat {
            next_heartbeat = now + 600
        }
    }
}

// 更新或者添加collector
func populate_collectors() {
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
        col.Collect()
    }

}

