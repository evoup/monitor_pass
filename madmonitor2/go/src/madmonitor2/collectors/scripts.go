package main

import (
    "encoding/json"
    "fmt"
    "io/ioutil"
    "madmonitor2/inc"
    "regexp"
    "strconv"
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
                userScriptKey := cmdArr[0]
                shell := cmdArr[1]
                reg := regexp.MustCompile(`(.*)\[(.*)\]`)
                m := reg.FindAllStringSubmatch(userScriptKey, -1)
                hasParam := false
                functionName := ""
                if len(m) > 0 && len(m[0]) == 3 {
                    fmt.Println(m)
                    hasParam = true
                    functionName = m[0][1]
                }
                fmt.Println(hasParam)
                fmt.Println(functionName)
                fmt.Println(shell)
                // 合法的形式参数
                validStyleParam := []string{"$1", "$2", "$3", "$4", "$5", "$6", "$7", "$7", "$8", "$9"}
                // 有缓存的从缓存拿
                foo, found := inc.ConfigCache.Get("monitorItems")
                if found {
                    fmt.Println(foo)
                } else {
                    // 没有就直接解析，调试单个程序时应该会走到这里
                    keys := make([]inc.MonitorItem, 0)
                    json.Unmarshal([]byte(file1), &keys)
                    for i := range keys {
                        monitorItemKey := keys[i].Key
                        monitorItemDelay := keys[i].Delay
                        // userScriptKey为用户脚本的key
                        if monitorItemKey == userScriptKey {
                            fmt.Printf("userScriptKey: %v", userScriptKey)
                            // key之后要发消息到channel
                            // shell是要用来执行的
                            runSingleScript(userScriptKey, monitorItemDelay, shell)
                        } else {
                            // 另外一种情况，统配
                            reg := regexp.MustCompile(`(.*)\[(.*)\]`)
                            m := reg.FindAllStringSubmatch(monitorItemKey, -1)
                            if len(m) > 0 && len(m[0]) == 3 && functionName == m[0][1] {
                                // 配置里
                                monitorItemParam := m[0][2]
                                fmt.Println(monitorItemParam)
                                var splitParam []string
                                if monitorItemParam != "" {
                                    splitParam = strings.Split(monitorItemParam, ",")
                                }
                                // 匹配参数位置
                                s := strings.Split(shell, " ")
                                fmt.Println(s)
                                // shell后面有参数$1-$9
                                lastShell := ""
                                if len(s) > 0 {
                                    for i := range s {
                                        if i == 0 {
                                            // 就是纯命令
                                            lastShell = s[0]
                                            continue
                                        }
                                        if stringInSlice(s[i], validStyleParam) {
                                            s[i] = strings.Replace(s[i], "$", "", -1)
                                            // -1是因为下发监控项参数解析成数组从0开始，而用户配置的$1从1开始
                                            atoi, err := strconv.Atoi(s[i])
                                            if err == nil {
                                                lastShell = lastShell + " " + splitParam[atoi-1]
                                            }
                                        }
                                    }
                                }
                                // 填充对应位置参数值
                                runSingleScript(monitorItemKey, monitorItemDelay, lastShell)
                            }
                        }
                    }
                    fmt.Println(keys)
                }
            }
        }
    }

    fmt.Println("ok")
}

func runSingleScript(key string, interval int, shell string) {
    // TOOD 进行脚本执行
    // 完成，再次确定key是不是还需要监控，interval是多少，shell是什么
}

func stringInSlice(a string, list []string) bool {
    for _, b := range list {
        if b == a {
            return true
        }
    }
    return false
}

var ScriptsSo scriptsPlugin
