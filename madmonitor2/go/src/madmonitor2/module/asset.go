package module
//package main





import (
    "fmt"
    "github.com/carlescere/scheduler"
)

func main() {
    ScheduleGrabAndPostAssetData()
}
// 资产管理定时收集服务器信息的模块
func ScheduleGrabAndPostAssetData() {
    job := func() {
        fmt.Println("$$$$$$$$$$$$$$$$$$$$$$$$$$Time's up!$$$$$$$$$$$$$$$$$$$$$$$$")
        // 采集CPU

        // 采集内存

        // 采集主板


        // 采集硬盘

        // 采集网络接口
    }
    job()
    _, err := scheduler.Every(20).Minutes().Run(job)
    fmt.Printf("asset schedule err:%s", err)
}
