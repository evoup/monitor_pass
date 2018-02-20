package main

import (
	"os"
	"madmonitor2/utils"
	"bufio"
	"regexp"
	"fmt"
	"strings"
)

var FIELDS = []string{"bytes", "packets", "errs", "dropped",
	"fifo.errs", "frame.errs", "compressed", "multicast",
	"bytes", "packets", "errs", "dropped",
	"fifo.errs", "collisions", "carrier.errs", "compressed"}

func main() {
	ifstat()
}

func ifstat() {
	f_netdev, err := os.Open("/proc/net/dev")
	if err != nil {
		utils.Log(utils.GetLogger(), "ifstat][err:"+err.Error(), 2, 1)
	}
	for {
		f_netdev.Seek(0, 0)
		scanner := bufio.NewScanner(f_netdev)
		for scanner.Scan() {
			line := scanner.Text()
			reg := regexp.MustCompile(`\s*(eth?\d+ |em\d+_\d+/\d+ | em\d+_\d+ | em\d+ |p\d+p\d+_\d+/\d+ | p\d+p\d+_\d+ | p\d+p\d+ |(?:(?:en|sl|wl|ww)(?:b\d+ |c[0-9a-f]+ |o\d+(?:d\d+)? |s\d+(?:f\d+)?(?:d\d+)? |x[0-9a-f]+ |p\d+s\d+(?:f\d+)?(?:d\d+)? |p\d+s\d+(?:f\d+)?(?:u\d+)*(?:c\d+)?(?:i\d+)?))):(.*)`)
			data := reg.FindAllStringSubmatch(line, -1)
			if data==nil || len(data[0]) < 3 {
				continue
			}
			fmt.Println(data[0][1])
			//fields := strings.Fields(data[0][2])

			//# We just care about ethN and emN interfaces.  We specifically
			//# want to avoid bond interfaces, because interface
			//# stats are still kept on the child interfaces when
			//# you bond.  By skipping bond we avoid double counting.

		}
	}
}
