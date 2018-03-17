package main

import (
	"bufio"
	"fmt"
	"madmonitor2/inc"
	"madmonitor2/utils"
	"os"
	"regexp"
	"strings"
	"time"
)

var FIELDS = []string{"bytes", "packets", "errs", "dropped",
	"fifo.errs", "frame.errs", "compressed", "multicast",
	"bytes", "packets", "errs", "dropped",
	"fifo.errs", "collisions", "carrier.errs", "compressed"}

var IFSTAT_DEFAULT_COLLECTION_INTERVAL = 15

func main() {
	ifstat()
}

func ifstat() {
	collectionInterval := IFSTAT_DEFAULT_COLLECTION_INTERVAL
	f_netdev, err := os.Open("/proc/net/dev")
	if err != nil {
		utils.Log(utils.GetLogger(), "ifstat][err:"+err.Error(), 2, 1)
	}
	for {
		ts := time.Now().Unix()
		f_netdev.Seek(0, 0)
		scanner := bufio.NewScanner(f_netdev)
		for scanner.Scan() {
			line := scanner.Text()
			reg := regexp.MustCompile(`\s*(eth?\d+ |em\d+_\d+/\d+ | em\d+_\d+ | em\d+ |p\d+p\d+_\d+/\d+ | p\d+p\d+_\d+ | p\d+p\d+ |(?:(?:en|sl|wl|ww)(?:b\d+ |c[0-9a-f]+ |o\d+(?:d\d+)? |s\d+(?:f\d+)?(?:d\d+)? |x[0-9a-f]+ |p\d+s\d+(?:f\d+)?(?:d\d+)? |p\d+s\d+(?:f\d+)?(?:u\d+)*(?:c\d+)?(?:i\d+)?))):(.*)`)
			data := reg.FindAllStringSubmatch(line, -1)
			if data == nil || len(data[0]) < 3 {
				continue
			}
			fmt.Println(data[0][1])
			intf := data[0][1]
			stats_fields := data[0][2]
			stats := strings.Fields(stats_fields)
			for i := 0; i < 16; i++ {
				//print("proc.net.%s %d %s iface=%s direction=%s"
				//% (FIELDS[i], ts, stats[i], intf, direction(i)))
				fmt.Print("ifstat proc.net.%v %v %v iface=%v direction=%v", FIELDS[i], ts, stats[i], intf, direction(i))
				inc.MsgQueue <- fmt.Sprintf("ifstat proc.net.%v %v %v iface=%v direction=%v", FIELDS[i], ts, stats[i], intf, direction(i))
			}
			//fields := strings.Fields(data[0][2])

			//# We just care about ethN and emN interfaces.  We specifically
			//# want to avoid bond interfaces, because interface
			//# stats are still kept on the child interfaces when
			//# you bond.  By skipping bond we avoid double counting.
			time.Sleep(time.Second * time.Duration(collectionInterval))
		}
	}
}

func direction(i int) string {
	if i >= 8 {
		return "out"
	} else {
		return "in"
	}

}
