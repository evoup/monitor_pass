package main

import (
	"madmonitor2/inc"
	"fmt"
)

var FsTypeIgnore = [6]string{
	"cgroup",
	"debugfs",
	"devtmpfs",
	"nfs",
	"rpc_pipefs",
	"rootfs"}


type dfstatPlugin string


func main() {
	dfstat()
}

func (p dfstatPlugin) Collect() {
	defer inc.Wg.Done()
	select {
	case _ = <-inc.Shutdown:
		//We're done!
		return
	default:
		dfstat()
	}
}

func dfstat() {
	fmt.Print("DFSTAT-------------＞＞＞＞＞＞＞＞＞＞＞＞")
}

var DfstatSo dfstatPlugin


//build -gcflags "-l -N" -buildmode=plugin -o plugin/dfstat.so collectors/dfstat.go


