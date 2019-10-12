package module
//package main

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
        //{"cpu_count":"6","cpu_physical": "6", "cpu_model_name": " Intel(R) Core(TM) i5-8400 CPU @ 2.80GHz"}[{"capicity":"16384 MB", "slot":"ChannelA-DIMM1", "model":"DDR4", "speed":"2666 MT/s", "manufacturer":"Kingston", "sn":"EA2C9791"},{"capicity":"16384 MB", "slot":"ChannelA-DIMM2", "model":"DDR4", "speed":"2666 MT/s", "manufacturer":"Kingston", "sn":"EB2C5992"},{"capicity":"No Module Installed", "slot":"ChannelB-DIMM1", "model":"Unknown", "speed":"Unknown", "manufacturer":"Not Specified", "sn":"Not Specified"},{"capicity":"16384 MB", "slot":"ChannelB-DIMM2", "model":"DDR4", "speed":"2666 MT/s", "manufacturer":"Kingston", "sn":"EE2CA492"}]{"manufacturer":" System manufacturer", "model":" System Product Name", "sn"":" System Serial Number""}
        //[{"model": "WDC WD10EZEX-08WN4A0","size": "931.5","sn": "WD-WCC6Y1DN06AY"}]
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
        shell = "sudo dmidecode -t1"
        err, out, _ = shellOut(shell)
        if err != nil {
            fmt.Println(err)
        }
        manufacturer := ""
        model := ""
        sn := ""
        splitItems := strings.Split(out, "\n")
        for i := range splitItems {
            if splitItems[i] == "" {
                continue
            }
            splitItems[i] = strings.TrimLeft(splitItems[i], "\t")
            kv := strings.Split(splitItems[i], ":")
            if kv[0] == "Manufacturer" {
                manufacturer = kv[1]
                continue
            }
            if kv[0] == "Product Name" {
                model = kv[1]
                continue
            }
            if kv[0] == "Serial Number" {
                sn = kv[1]
                continue
            }
        }
        mbJson := fmt.Sprintf(`{"manufacturer":"%v", "model":"%v", "sn"":"%v""}`, manufacturer, model, sn)
        fmt.Println(mbJson)

        // 采集硬盘

        //获取存储信息。
        //本脚本只针对ubuntu中使用sda，且只有一块硬盘的情况。
        //具体查看硬盘信息的命令，请根据实际情况，实际调整。
        //如果需要查看Raid信息，可以尝试MegaCli工具。
        shell = "sudo hdparm -i /dev/sda | grep Model"
        err, out, _ = shellOut(shell)
        if err != nil {
            fmt.Print(err)
        }
        splitItems = strings.Split(out, "\n")
        model = ""
        sn = ""
        for i := range splitItems {
            line := splitItems[i]
            if line == "" {
                continue
            }
            lineItems := strings.Split(line, ",")
            for j := range lineItems {
                if lineItems[j] == "" {
                    continue
                }
                kv := strings.Split(lineItems[j], "=")
                if strings.Trim(kv[0], " ") == "Model" {
                    model = kv[1]
                    continue
                }
                if strings.Trim(kv[0], " ") == "SerialNo" {
                    sn = kv[1]
                    continue
                }
            }
        }
        size := ""
        shell = "sudo fdisk -l /dev/sda | grep Disk|head -1"
        err, out, _ = shellOut(shell)
        if err != nil {
            fmt.Println(err)
        }
        items := strings.Split(out, " ")
        size = items[2]
        diskJson := fmt.Sprintf(`[{"model": "%s","size": "%s","sn": "%v"}]`, model, size, sn)
        fmt.Println(diskJson)

        // 采集网络接口



    }
    job()
    _, err := scheduler.Every(20).Minutes().Run(job)
    if err != nil {
        fmt.Printf("asset schedule err:%s", err)
    }
}
