package main

import (
    "madmonitor2/fun"
    "madmonitor2/module"
    "fmt"
    "time"
)

func main() {
    hLog := core.Init()
    common.Log(hLog, "main][init done",1, 4)
    fmt.Println(core.StartTime)
    for ;; {
        time.Sleep(5)
    }
}

