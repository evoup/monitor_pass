package main

import (
    "madmonitor2/fun"
    "madmonitor2/module"
    "fmt"
    "log"
    "time"
)

func main() {
    var hLog *log.Logger
    hLog = core.Init()
    common.Log(hLog, "main][init done")
    fmt.Println(common.UnixMicro())
    for ;; {
        time.Sleep(5)
    }
}

