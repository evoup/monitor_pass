package main

import (
	"github.com/Centny/gosigar"
	"fmt"
	"time"

)

func main() {
	x()
}
func x() {
	//query memory
	sigar := gosigar.NewSigar()
	sigar.Open()
	defer sigar.Close()
	for {

		load, e := sigar.QueryLoadAvg()
		if e != nil {
			fmt.Println(e.Error())
			break
		}
		fmt.Println(load)
		mem, err := sigar.QueryMem()
		if err != nil {
			fmt.Println(err.Error())
			break
		}
		fmt.Println(mem.String())
		time.Sleep(time.Second)
	}
}
