//package module
package main

import (
    "bufio"
    "bytes"
    "fmt"
    "github.com/carlescere/scheduler"
    "madmonitor2/utils"
    "os"
    "os/exec"
    "strings"
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
        jsonStr := fmt.Sprintf(`{"cpu_count":"%v","cpu_physical": "%v", "cpu_model_name": "%v"}`, cpuCount, cpuPhysicalCount, cpuModelName)
        fmt.Printf(jsonStr)

        // 采集内存
        var shell = "sudo dmidecode -q -t 17 2>/dev/null"
        err, out, _ := shellOut(shell)
        // 根据Memory Device分成两段
        var memArr []string
        arr := strings.Split(out, "Memory Device")
        for i := range arr {
            if arr[i] == "" {
                continue
            }
            line := arr[i]
            splitItems := strings.Split(line, "\n\t")
            capicity := ""
            slot := ""
            model := ""
            speed := ""
            manufacturer := ""
            sn := ""
            for j := range splitItems {
                if splitItems[j] == "" {
                    continue
                }
                kv := strings.Split(splitItems[j], ":")
                if kv[0] == "Size" {
                    capicity = strings.Trim(kv[1], " ")
                    continue
                }
                if kv[0] == "Locator" {
                    slot = strings.Trim(kv[1], " ")
                    continue
                }
                if kv[0] == "Type" {
                    model = strings.Trim(kv[1], " ")
                    continue
                }
                if kv[0] == "Speed" {
                    speed = strings.Trim(kv[1], " ")
                    continue
                }
                if kv[0] == "Manufacturer" {
                    manufacturer = strings.Trim(kv[1], " ")
                    continue
                }
                if kv[0] == "Serial Number" {
                    sn = strings.Trim(kv[1], " ")
                    continue
                }
            }
            memItemJson := fmt.Sprintf(`{"capicity":"%v", "slot":"%v", "model":"%v", "speed":"%v", "manufacturer":"%v", "sn":"%v"}`, capicity, slot, model, speed, manufacturer, sn)
            memArr = append(memArr, memItemJson)
        }
        if err != nil {
            fmt.Print(err)
        }
        memJsonStr := fmt.Sprintf("[%s]", strings.Join(memArr,","))
        fmt.Printf(memJsonStr)


        // 采集主板

        // 采集硬盘

        // 采集网络接口



    }
    job()
    _, err := scheduler.Every(20).Minutes().Run(job)
    if err != nil {
        fmt.Printf("asset schedule err:%s", err)
    }
}
