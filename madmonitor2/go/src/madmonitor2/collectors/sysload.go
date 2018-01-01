package main

import "fmt"

import (
	s "strings"
	"strconv"
	"madmonitor2/collectors/etc"
	"os/exec"
	"bufio"
	"regexp"
)

var DEFAULT_COLLECTION_INTERVAL = 5

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
	//convert_to_bytes("1234K");
	collection_interval := DEFAULT_COLLECTION_INTERVAL
	collect_every_cpu := true
	config := etc.Get_config()
	if config != nil {
		collection_interval0, err := strconv.Atoi(config["collection_interval"])
		if err == nil {
			collection_interval = collection_interval0
		}
		//collect_every_cpu0, err := config["collect_every_cpu"]
		//if err == nil {
		//	if s.EqualFold(collect_every_cpu0, "1") {
		//		collect_every_cpu = true
		//	} else {
		//		collect_every_cpu = false
		//	}
		//}
		collect_every_cpu = true
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
			fmt.Printf(" > Read %d characters\n", len(line))
			// Process the line here.
			fmt.Println(line)
			if err != nil {
				break
			}
		}

	}
}
