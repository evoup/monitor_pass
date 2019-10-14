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
    "net/http"
    "io/ioutil"
    "time"
    "madmonitor2/inc"
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
        // 采集基本信息
        shell := "cat /etc/issue"
        err, out, _ := shellOut(shell)
        if err != nil {
            fmt.Println(err)
        }
        osAndVersion := strings.Trim(strings.Split(strings.Split(out, "\n")[0], "\\n")[0], " ")
        osv := strings.Split(osAndVersion, " ")
        var osVersion string
        //noinspection GoSnakeCaseUsage
        _os := osv[0]
        for i := range osv {
            if i == 0 {
                continue
            }
            osVersion = osVersion + osv[i]
        }
        baseJsonStr := fmt.Sprintf(`{"os": "%v", "osv": "%v"}`, _os, osVersion)
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
        cpuJsonStr := fmt.Sprintf(`{"cpu_count":"%v","cpu_physical": "%v", "cpu_model_name": "%v"}`, cpuCount, cpuPhysicalCount, cpuModelName)

        // 采集内存
        shell = "sudo dmidecode -q -t 17 2>/dev/null"
        err, out, _ = shellOut(shell)
        // 根据Memory Device分成两段
        var memArr []string
        arr := strings.Split(out, "Memory Device")
        for i := range arr {
            if arr[i] == "" {
                continue
            }
            line := arr[i]
            splitItems := strings.Split(line, "\n\t")
            capacity := ""
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
                    capacity = strings.Trim(kv[1], " ")
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
            memItemJson := fmt.Sprintf(`{"capacity":"%v", "slot":"%v", "model":"%v", "speed":"%v", "manufacturer":"%v", "sn":"%v"}`, capacity, slot, model, speed, manufacturer, sn)
            memArr = append(memArr, memItemJson)
        }
        if err != nil {
            fmt.Print(err)
        }
        memJsonStr := fmt.Sprintf("[%s]", strings.Join(memArr,","))


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
                manufacturer = strings.Trim(kv[1], " ")
                continue
            }
            if kv[0] == "Product Name" {
                model = strings.Trim(kv[1], " ")
                continue
            }
            if kv[0] == "Serial Number" {
                sn = strings.Trim(kv[1], " ")
                continue
            }
        }
        mbJsonStr := fmt.Sprintf(`{"manufacturer":"%v", "model":"%v", "sn":"%v"}`, manufacturer, model, sn)

        // 采集硬盘

        //获取存储信息。
        //本脚本只针对ubuntu中使用sda，且只有一块硬盘的情况。
        //具体查看硬盘信息的命令，请根据实际情况，实际调整。
        //如果需要查看Raid信息，可以尝试MegaCli工具。
        shell = "LANG=en_us_8859_1 && sudo hdparm -i /dev/sda | grep Model"
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
        diskJsonStr := "[]"
        var size string
        // todo 需要换成hdpram才能获取到序列号以及磁盘是SATA还是SAS，并且对于raid要用MegaCli
        shell = "LANG=en_us_8859_1 && sudo fdisk -l /dev/sda | grep Disk|head -1"
        err, out, _ = shellOut(shell)
        if err != nil {
            fmt.Println(err)
        }
        items := strings.Split(out, " ")
        if len(items) > 1 {
            size = items[2]
            diskJsonStr = fmt.Sprintf(`[{"model": "%s","size": "%s","sn": "%v", "type": "SATA"}]`, model, size, "")
        }

        // 采集网络接口
        shell = "LANG=en_us_8859_1 && ip addr"
        //2: wlp3s0: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc mq state UP group default qlen 1000
        //link/ether 48:a4:72:59:66:9a brd ff:ff:ff:ff:ff:ff
        //inet 192.168.31.45/24 brd 192.168.31.255 scope global dynamic wlp3s0
        //valid_lft 32574sec preferred_lft 32574sec
        //inet6 fe80::136f:f342:3125:4c88/64 scope link
        //valid_lft forever preferred_lft forever

        err, out, _ = shellOut(shell)
        if err != nil {
           fmt.Print(err)
        }
        splitItems = strings.Split(out, "\n")
        fmt.Println(splitItems)
        nextMacLine := false
        nextIpLine := false
        var lastNicNameLine string
        //var lastLinkLine string
        var nicName string
        var macAddr string
        status := "DOWN"
        var nicArr []string
        for i := range splitItems {
           line := splitItems[i]
           if nextMacLine {
               nicName = ""
               nextMacLine = false
               nicName = strings.Split(lastNicNameLine, " ")[1]
               nicName = nicName[0:len(nicName)-1]
               if strings.HasPrefix(nicName, "br-") || strings.HasPrefix(nicName, "docker") || strings.HasPrefix(nicName, "veth") {
                   continue
               }
               fmt.Println(nicName)
               if strings.Contains(lastNicNameLine, "state UP") {
                   status = "UP"
               } else {
                   status = "DOWN"
               }
           }
           if nextIpLine {
               nextIpLine = false
               //inet 192.168.31.45/24 brd 192.168.31.255 scope global dynamic wlp3s0
               ipAndMask := strings.Split(strings.Trim(line, " "), " ")
               split := strings.Split(ipAndMask[1], "/")
               network := ipAndMask[3]
               // nicName, macAddr, status, ip=>split[0], mask=>split[1]
               jsonStr := fmt.Sprintf(`{"nic_name":"%v", "mac_addr":"%v", "ip_addr":"%v", "network":"%v", "netmask":"%v", "status": "%v"}`, nicName, macAddr, split[0], network, split[1], status)
               nicArr = append(nicArr, jsonStr)
           }
           if strings.Contains(line, "mtu") {
               lastNicNameLine = line
               nextMacLine = true
           }
           if strings.Contains(line, "link/ether") {
               //lastLinkLine = line
               nextIpLine = true
               macAddr = strings.Split(strings.Trim(line, " "), " ")[1]
           } else {
               continue
           }
        }

        nicJsonStr:=""
        host, _ := inc.ConfObject.GetString("ServerName")
        lastJsonStr := fmt.Sprintf(`{"host":"%v", "base":%v, "cpu":%v, "mem":%v, "main_board":%v, "disk":%v, "nic":%v}`,
            host, baseJsonStr, cpuJsonStr, memJsonStr, mbJsonStr, diskJsonStr, nicJsonStr)
        fmt.Println(lastJsonStr)
        api, _ := inc.ConfObject.GetString("AssetApiUrl")
        jsonPost(api, []byte(lastJsonStr))
    }
    job()
    _, err := scheduler.Every(20).Minutes().Run(job)
    if err != nil {
        fmt.Printf("asset schedule err:%s", err)
    }
}

func jsonPost(url string, jsonStr []byte) {
    req, err := http.NewRequest("POST", url, bytes.NewBuffer(jsonStr))
    req.Header.Set("X-Custom-Header", "myvalue")
    req.Header.Set("Content-Type", "application/json")
    client := &http.Client{}
    client.Timeout = time.Second * 15
    resp, err := client.Do(req)
    if err != nil {
        fmt.Println(err)
    }
    // panic 会报错，打断程序运行。 但是不会阻止defer运行，所以不管是不是内部抛panic，加个defer再说
    defer func() {
        if resp != nil {
            resp.Body.Close()
            fmt.Println("response Status:", resp.Status)
            fmt.Println("response Headers:", resp.Header)
            body, _ := ioutil.ReadAll(resp.Body)
            fmt.Println("response Body:", string(body))
        }
    }()

}
