package main

import (
	//"os"
	//"madmonitor2/utils"
	"path/filepath"
	"regexp"
	//"fmt"
	"fmt"
	"os"
	"io/ioutil"
	//"strings"
	"strings"
	"madmonitor2/utils"
	"time"
	"bufio"
	"strconv"
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
	f_uptime, err := os.Open("/proc/uptime")
	if err != nil {
		utils.Log(utils.GetLogger(), "procstats][err:"+err.Error(), 2, 1)
	}
	f_meminfo, err := os.Open("/proc/meminfo")
	if err != nil {
		utils.Log(utils.GetLogger(), "procstats][err:"+err.Error(), 2, 1)
	}
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
	//numastats := find_sysfs_numa_stats()

	///////////////
	for {
		timestamp := time.Now().Unix()
		f_uptime.Seek(0, 0)
		scanner := bufio.NewScanner(f_uptime)
		for scanner.Scan() {
			line := scanner.Text()
			reg := regexp.MustCompile(`(\S+)\s+(\S+)`)
			data := reg.FindAllStringSubmatch(line, -1)
			if len(data[0]) > 0 {
				//print "proc.uptime.total %d %s" % (ts, m.group(1))
				fmt.Printf("procstats proc.uptime.total %v %v\n", timestamp, data[0][1])
				//print "proc.uptime.now %d %s" % (ts, m.group(2))
				fmt.Printf("procstats proc.uptime.now %v %v\n", timestamp, data[0][2])
			}
		}
		f_meminfo.Seek(0, 0)
		scanner = bufio.NewScanner(f_meminfo)
		for scanner.Scan() {
			line := scanner.Text()
			reg := regexp.MustCompile(`([^\s:]+):\s+(\d+)(\s+(\w+))?`)
			data := reg.FindAllStringSubmatch(line, -1)
			value := 0
			if len(data[0]) > 4 {
				if data[0][4] != "" && strings.ToLower(data[0][4]) == "kb" {
					//fmt.Print(data[0][4])
					//# convert from kB to B for easier graphing
					//value = str(int(m.group(2)) * 1024)
					i, e := strconv.Atoi(data[0][2])
					if e != nil {
						continue
					}
					value = i*1024
				} else {
					i, e := strconv.Atoi(data[0][2])
					if e != nil {
						continue
					}
					value = i
				}
				// 将空格转换为下划线，全部小写化，最后去掉头尾空格
				//name = re.sub("\W", "_", m.group(1)).lower().strip("_")
				reg := regexp.MustCompile(`\W`)
				name := strings.TrimPrefix(reg.ReplaceAllString(data[0][1], "_"), "_")
				//print ("proc.meminfo.%s %d %s"% (name, ts, value))
				fmt.Printf("procstats proc.meminfo.%v %v %v\n", name, timestamp, value)
			}
		}
	}

}

func find_sysfs_numa_stats() []string {
	nodes, err := ioutil.ReadDir(NUMADIR)
	if err != nil {
		return []string{}
	}
	var numastat []string
	for i := range nodes {

		if strings.HasPrefix(nodes[i].Name(), "node") {
			numastat = append(numastat, NUMADIR+"/"+nodes[i].Name()+"/numastat")
		}
	}
	return []string{}
}
