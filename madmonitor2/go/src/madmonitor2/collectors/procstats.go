package main

import (
	//"os"
	//"madmonitor2/utils"
	"path/filepath"
	"regexp"
	//"fmt"
	"fmt"
	"os"
)

var NUMADIR = "/sys/devices/system/node"

func main() {
	procstats()
}

func procstats() {
	//f_uptime = open("/proc/uptime", "r")
	//f_meminfo = open("/proc/meminfo", "r")
	//f_vmstat = open("/proc/vmstat", "r")
	//f_stat = open("/proc/stat", "r")
	//f_loadavg = open("/proc/loadavg", "r")
	//f_entropy_avail = open("/proc/sys/kernel/random/entropy_avail", "r")
	//f_interrupts = open("/proc/interrupts", "r")
	//
	//f_scaling = "/sys/devices/system/cpu/cpu%s/cpufreq/%s_freq"
	//f_uptime, err := os.Open("/proc/uptime")
	//if err != nil {
	//	utils.Log(utils.GetLogger(), "procstats][err:"+err.Error(), 2, 1)
	//}
	//f_meminfo, err := os.Open("/proc/meminfo")
	//if err != nil {
	//	utils.Log(utils.GetLogger(), "procstats][err:"+err.Error(), 2, 1)
	//}
	//f_vmstat, err := os.Open("/proc/vmstat")
	//if err != nil {
	//	utils.Log(utils.GetLogger(), "procstats][err:"+err.Error(), 2, 1)
	//}
	//f_stat, err := os.Open("/proc/stat")
	//if err != nil {
	//	utils.Log(utils.GetLogger(), "procstats][err:"+err.Error(), 2, 1)
	//}
	//f_loadavg, err := os.Open("/proc/loadavg")
	//if err != nil {
	//	utils.Log(utils.GetLogger(), "procstats][err:"+err.Error(), 2, 1)
	//}
	//f_entropy_avail, err := os.Open("/proc/sys/kernel/random/entropy_avail")
	//if err != nil {
	//	utils.Log(utils.GetLogger(), "procstats][err:"+err.Error(), 2, 1)
	//}
	//f_interrupts, err := os.Open("/proc/interrupts")
	//if err != nil {
	//	utils.Log(utils.GetLogger(), "procstats][err:"+err.Error(), 2, 1)
	//}
	f_scaling := "/sys/devices/system/cpu/cpu%s/cpufreq/%s_freq"
	files, _ := filepath.Glob("/sys/devices/system/cpu/cpu[0-9]*/cpufreq/scaling_cur_freq")
	f_scaling_min := map[string]*os.File{}
	f_scaling_max := map[string]*os.File{}
	f_scaling_cur := map[string]*os.File{}
	for i := range files {
		cpu := files[i]
		reg := regexp.MustCompile("/sys/devices/system/cpu/cpu([0-9]*)/cpufreq/scaling_cur_freq")
		data := reg.FindStringSubmatch(cpu)
		if len(data) > 1 {
			cpuNo := data[1]
			file, e := os.Open(fmt.Sprintf(f_scaling, cpuNo, "cpuinfo_min"))
			if e != nil {
				continue
			}
			f_scaling_min[cpuNo] = file
			file, e = os.Open(fmt.Sprintf(f_scaling, cpuNo, "cpuinfo_max"))
			if e != nil {
				continue
			}
			f_scaling_max[cpuNo] = file
			file, e = os.Open(fmt.Sprintf(f_scaling, cpuNo, "scaling_cur"))
			if e != nil {
				continue
			}
			f_scaling_cur[cpuNo] = file
		} else {
			continue
		}
	}

}
