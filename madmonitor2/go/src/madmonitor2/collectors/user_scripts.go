package main

import (
    "madmonitor2/inc"
    "fmt"
    "encoding/json"
    "io/ioutil"
    "strings"
    "time"
)

type userScriptsPlugin string

func main() {
    userScripts()
}

func (p userScriptsPlugin) Collect() {
    defer inc.Wg.Done()
    select {
    case _ = <-inc.Shutdown:
        //We're done!
        return
    default:
        userScripts()
    }
}

func userScripts() {
    // 读取配置文件中的UserScripts上下文
    time.Sleep(time.Second * time.Duration(5))
    file, err := ioutil.ReadFile(inc.PROC_ROOT + "/" + inc.CONF_SUBPATH + inc.CONF_FILE)
    file1, err := ioutil.ReadFile(inc.PROC_ROOT + "/" + inc.WORK_SUBPATH + inc.MONITOR_ITEMS_CONF_FILE)

    if err == nil {
        conf := inc.Conf{}
        err = json.Unmarshal([]byte(file), &conf)
        fmt.Println(conf)
        if err == nil {
            fmt.Println(conf)
            for i := range conf.UserScripts {
                line := conf.UserScripts[i]
                fmt.Println(line)
                cmdArr := strings.Split(line, ",")
                key := cmdArr[0]
                shell := cmdArr[1]
                fmt.Println(key)
                fmt.Println(shell)
                foo, found := inc.ConfigCache.Get("monitorItems")
                if found {
                   fmt.Println(foo)
                } else {
                    keys := make([]inc.MonitorItem, 0)
                    json.Unmarshal([]byte(file1), &keys)
                    fmt.Println(keys)
                }
            }
        }
    } else {
    }

    fmt.Println("ok")
}

var userScriptsSo userScriptsPlugin
