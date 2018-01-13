/*
 * This file is part of madmonitor2.
 * Copyright (c) 2018. Author: yinjia evoex123@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  This program is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Lesser
 * General Public License for more details.  You should have received a copy
 * of the GNU Lesser General Public License along with this program.  If not,
 * see <http://www.gnu.org/licenses/>.
 */

package main

import "fmt"

import (
	s "strings"
	"strconv"
	"madmonitor2/collectors/etc"
	"os/exec"
	"bufio"
	"regexp"
	"time"
)
type plugin string
var DEFAULT_COLLECTION_INTERVAL = 15

// Take a string in the form 1234K, and convert to bytes
func convert_to_bytes(str string) int {
	factors := map[string]int{
		"K": 1024,
		"M": 1024 * 1024,
		"G": 1024 * 1024 * 1024,
		"T": 1024 * 1024 * 1024 * 1024,
		"P": 1024 * 1024 * 1024 * 1024 * 1024,
		"E": 1024 * 1024 * 1024 * 1024 * 1024 * 1024,
	}
	for e := range factors {
		fmt.Println(e)
		fmt.Println(factors[e])
		if s.HasSuffix(str, e) {
			number := str[0:len(str)-1]
			i, err := strconv.Atoi(number)
			if err == nil {
				numberInt := i * factors[e]
				return numberInt
			}
		}
	}
	return 0
}

func main() {
	sysload()
}

func (p plugin)Collect() {
	sysload()
}

func sysload() {
	//convert_to_bytes("1234K");
	collection_interval := DEFAULT_COLLECTION_INTERVAL
	collect_every_cpu := true
	config := etc.Get_config()
	if config != nil {
		collection_interval0, err := strconv.Atoi(config["collection_interval"])
		if err == nil {
			collection_interval = collection_interval0
		}
		collect_every_cpu_flag := config["collect_every_cpu"]
		if err == nil {
			if s.EqualFold(collect_every_cpu_flag, "1") {
				collect_every_cpu = true
			} else {
				collect_every_cpu = false
			}
		}
	}
	if collect_every_cpu {
		itoa := strconv.Itoa(collection_interval)
		cmd := exec.Command("mpstat", "-P", "ALL", itoa)
		stdout, _ := cmd.StdoutPipe()
		cmd.Start()

		r := bufio.NewReader(stdout)
		for {
			line, err := r.ReadString('\n')

			// CPU: --> CPU all:  : FreeBSD, to match the all CPU
			// %( [uni][a-z]+,?)? : FreeBSD, so that top output matches mpstat output
			// AM                 : Linux, mpstat output depending on locale
			// PM                 : Linux, mpstat output depending on locale
			// .* load            : FreeBSD, to correctly match load averages
			// ,                  : FreeBSD, to correctly match processes: Mem: ARC: and Swap:
			re := regexp.MustCompile(`%( [uni][a-z]+,?)?| AM | PM |.* load |,`)
			line = re.ReplaceAllString(line, "")
			re = regexp.MustCompile(`CPU:`)
			line = re.ReplaceAllString(line, "CPU all:")
			fields := s.Fields(line)
			if len(fields) <= 0 {
				continue
			}
			match, _ := regexp.MatchString(`[0-9][0-9]:[0-9][0-9]:[0-9][0-9]`, fields[0])
			match1, _ := regexp.MatchString(`[0-9][0-9].*[0-9][0-9].*[0-9][0-9].*`, fields[0])
			match2, _ := regexp.MatchString(`[0-9]+:?`, fields[1])
			match3, _ := regexp.MatchString(`all:?`, fields[1])
			if fields[0] == "CPU" || match || match1 && ((collect_every_cpu && match2) || (!collect_every_cpu && match3)) {
				// Process the line here.
				cpuid := s.Replace(fields[1], ":", "", -1)
				cpuuser:=fields[2]
				cpunice:=fields[3]
				cpusystem:=fields[4]
				cpuinterrupt:=fields[6]
				cpuidle := fields[len(fields)-1]
				timestamp := time.Now().Unix()
				fmt.Printf ("cpu.usr %v %v cpu=%v\n" , timestamp, cpuuser, cpuid)
				fmt.Printf ("cpu.nice %v %v cpu=%v\n", timestamp, cpunice, cpuid)
				fmt.Printf ("cpu.sys %v %v cpu=%v\n", timestamp, cpusystem, cpuid)
				fmt.Printf ("cpu.irq %v %v cpu=%v\n", timestamp, cpuinterrupt, cpuid)
				fmt.Printf ("cpu.idle %v %v cpu=%v\n", timestamp, cpuidle, cpuid)
			}
			if err != nil {
				break
			}
		}

	}
}

// exported as symbol named "SysloadSo"
var CollectorSo plugin

