package main

import (
    "madmonitor2/inc"
    "fmt"
    "encoding/json"
    "io/ioutil"
    "strings"
)

type scriptsPlugin string

func main() {
    scripts()
}

func (p scriptsPlugin) Collect() {
    defer inc.Wg.Done()
    select {
    case _ = <-inc.Shutdown:
        //We're done!
        return
    default:
        scripts()
    }
}

func scripts() {
    // 读取配置文件中的UserScripts上下文
    file, err := ioutil.ReadFile(inc.PROC_ROOT + "/" + inc.CONF_SUBPATH + inc.CONF_FILE)
    file1, err1 := ioutil.ReadFile(inc.PROC_ROOT + "/" + inc.WORK_SUBPATH + inc.MONITOR_ITEMS_CONF_FILE)
    // 都有才能继续执行，不然都没有下发汇报啥？
    if err == nil && err1 == nil {
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
                // 有缓存的从缓存拿
                foo, found := inc.ConfigCache.Get("monitorItems")
                if found {
                   fmt.Println(foo)
                } else {
                    // 没有就直接解析，调试单个程序时应该会走到这里
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

var ScriptsSo scriptsPlugin
