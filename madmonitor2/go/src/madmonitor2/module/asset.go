package module
//package main

import (
    "fmt"
    "github.com/carlescere/scheduler"
    "os"
    "madmonitor2/utils"
    "bufio"
    "strings"
    "os/exec"
    "bytes"
)

func main() {
    ScheduleGrabAndPostAssetData()
}
func shellOut(command string) (error, string, string) {
    var stdout bytes.Buffer
    var stderr bytes.Buffer
    cmd := exec.Command("sh", "-c", command)
    cmd.Stdout = &stdout
    cmd.Stderr = &stderr
    err := cmd.Run()
    return err, stdout.String(), stderr.String()
}
// 资产管理定时收集服务器信息的模块
func ScheduleGrabAndPostAssetData() {
    job := func() {
        // 采集CPU
        f, err := os.Open("/proc/cpuinfo")
        if err != nil {
            utils.Log(utils.GetLogger(), "dfstat][err:"+err.Error(), 2, 1)
        }
        scanner := bufio.NewScanner(f)
        cpuCount := 0
        cpuPhysicalCount := 0
        cpuModelName := ""
        for scanner.Scan() {
            line := scanner.Text()
            arr := strings.Split(line, "\n")
            kv := strings.Split(arr[0], ":")
            key := strings.TrimSpace(kv[0])
            if key == "processor" {
                cpuCount++
            } else if key == "physical id" {
                cpuPhysicalCount++
            } else if key == "model name" && cpuModelName == "" {
                cpuModelName = kv[1]
            }
        }

        // 采集内存
        var shell = "sudo dmidecode -q -t 17 2>/dev/null"
        err, out, stderr := shellOut(shell)
        fmt.Println(err)
        fmt.Println(out)
        fmt.Println(stderr)


        // 采集主板

        // 采集硬盘

        // 采集网络接口

        fmt.Printf("cpu_count:%v cpu_physical:%v cpu_model_name:%v \n", cpuCount, cpuPhysicalCount, cpuModelName)
    }
    job()
    _, err := scheduler.Every(20).Minutes().Run(job)
    if err != nil {
        fmt.Printf("asset schedule err:%s", err)
    }
}
