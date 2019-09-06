package main

import (
    "encoding/json"
    "fmt"
    "github.com/patrickmn/go-cache"
    "io/ioutil"
    "log"
    "madmonitor2/inc"
    "madmonitor2/utils"
    "os/exec"
    "regexp"
    "strconv"
    "strings"
    "time"
)

type scriptsPlugin string

var ScriptItemCache = cache.New(5*time.Minute, 10*time.Minute)

func main() {
    foreverRun()
}

func (p scriptsPlugin) Collect() {
    defer inc.Wg.Done()
    select {
    case _ = <-inc.Shutdown:
        //We're done!
        return
    default:
        foreverRun()
    }
}

func foreverRun() {
    for {
        scripts()
    }
}

func scripts() {
    host, _ := inc.ConfObject.GetString("ServerName")
    //host = s.Replace(host, ".", "", -1)
    //host = s.Replace(host, "-", "", -1)
    metricPrefix := "apps.backend." + host + "."
    // 5秒重新获取和运行一次
    time.Sleep(time.Second * 5)
    // 读取配置文件中的UserScripts上下文
    file, err := ioutil.ReadFile(inc.PROC_ROOT + "/" + inc.CONF_SUBPATH + inc.CONF_FILE)
    file1, err1 := ioutil.ReadFile(inc.PROC_ROOT + "/" + inc.WORK_SUBPATH + inc.MONITOR_ITEMS_CONF_FILE)
    // 都有才能继续执行，不然都没有下发汇报啥？
    if err == nil && err1 == nil {
        conf := inc.Conf{}
        err = json.Unmarshal([]byte(file), &conf)
        //fmt.Println(conf)
        if err == nil {
            //fmt.Println(conf)
            for i := range conf.UserScripts {
                line := conf.UserScripts[i]
                //fmt.Println(line)
                cmdArr := strings.Split(line, ",")
                userScriptKey := cmdArr[0]
                shell := cmdArr[1]
                reg := regexp.MustCompile(`(.*)\[(.*)\]`)
                m := reg.FindAllStringSubmatch(userScriptKey, -1)
                //hasParam := false
                functionName := ""
                if len(m) > 0 && len(m[0]) == 3 {
                    //fmt.Println(m)
                    //hasParam = true
                    functionName = m[0][1]
                }
                //fmt.Println(hasParam)
                //fmt.Println(functionName)
                //fmt.Println(shell)
                // 合法的形式参数
                validStyleParam := []string{"$1", "$2", "$3", "$4", "$5", "$6", "$7", "$7", "$8", "$9"}
                // 有缓存的从缓存拿
                _, found := inc.ConfigCache.Get("monitorItems")
                if found {
                    // TODO 没有参数的也需要被实现
                    //fmt.Println(foo)
                } else {
                    // 没有就直接解析，调试单个程序时应该会走到这里
                    keys := make([]inc.MonitorItem, 0)
                    json.Unmarshal([]byte(file1), &keys)
                    for i := range keys {
                        monitorItemKey := keys[i].Key
                        monitorItemDelay := 60
                        // 如果监控项里间隔为0，那就是60秒
                        if keys[i].Delay > 0 {
                            monitorItemDelay = keys[i].Delay
                        }
                        // userScriptKey为用户脚本的key
                        if monitorItemKey == userScriptKey {
                            //fmt.Printf("userScriptKey: %v", userScriptKey)
                            // key之后要发消息到channel
                            // shell是要用来执行的
                            runSingleScript(userScriptKey, monitorItemDelay, shell, metricPrefix)
                        } else {
                            // 另外一种情况，统配
                            reg := regexp.MustCompile(`(.*)\[(.*)\]`)
                            m := reg.FindAllStringSubmatch(monitorItemKey, -1)
                            if len(m) > 0 && len(m[0]) == 3 && functionName == m[0][1] {
                                // 配置里
                                monitorItemParam := m[0][2]
                                //fmt.Println(monitorItemParam)
                                var splitParam []string
                                if monitorItemParam != "" {
                                    splitParam = strings.Split(monitorItemParam, ",")
                                }
                                // 匹配参数位置
                                s := strings.Split(shell, " ")
                                //fmt.Println(s)
                                // shell后面有参数$1-$9
                                lastShell := ""
                                if len(s) > 0 {
                                    for i := range s {
                                        if i == 0 {
                                            // 就是纯命令
                                            lastShell = s[0]
                                            continue
                                        }
                                        if utils.StringInSlice(s[i], validStyleParam) {
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
                                runSingleScript(monitorItemKey, monitorItemDelay, lastShell, metricPrefix)
                            }
                        }
                    }
                    //fmt.Println(keys)
                }
            }
        }
    }
}

func runSingleScript(key string, interval int, shell string, metricPfx string) {
    // 判断是否在interval内
    foo, found := ScriptItemCache.Get(key)
    if found {
        fmt.Printf("%v cache\n", foo)
        // 在时间内，不用操作
        return
    }
    // TOOD 进行脚本执行
    fmt.Println(shell)
    sliceA := strings.Fields(shell)
    timestamp := time.Now().Unix()
    out, err := exec.Command(sliceA[0], sliceA[1:]...).Output()
    if err != nil {
        log.Fatal(err)
    }
    fmt.Printf("%s", out)
    fmt.Printf("%v %v %v\n", key, timestamp, out)
    // 第一列为具体的收集器名
    inc.MsgQueue <- fmt.Sprintf("%v %v%v %v %v\n", "scripts", metricPfx, key, timestamp, out)
    // 完成，设置一个缓存，这样进来后就不执行了
    // TODO 可能有的问题，就是一个进程没有执行完，最好还有锁的支持
    ScriptItemCache.Set(key, "hit", time.Duration(interval)*time.Second)
}

var ScriptsSo scriptsPlugin
