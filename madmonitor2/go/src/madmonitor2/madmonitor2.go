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
    "github.com/antonholmquist/jason"
    "os"
)

func main() {
    file, err := os.Open("/services/monitor2_deal/conf/madmonitor2.ini")
    if err == nil {
        v, err := jason.NewObjectFromReader(file)
        host, _ := v.GetString("ServerName")
        fmt.Println(host)
        if err == nil {
            fmt.Println(v)
        }
    } else {
        fmt.Println(err)
    }
    hLog := core.Init()
    common.Log(hLog, "main][init done",1, 4)
    fmt.Println(core.StartTime)
    for ;; {
        time.Sleep(5)
    }
}

