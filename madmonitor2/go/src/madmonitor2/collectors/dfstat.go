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

import (
	"madmonitor2/inc"
	"fmt"
	"os"
	"madmonitor2/utils"
	"time"
	"bufio"
	"strings"
)

var FsTypeIgnore = map[string]bool{
	"cgroup":     true,
	"debugfs":    true,
	"devtmpfs":   true,
	"nfs":        true,
	"rpc_pipefs": true,
	"rootfs":     true,
}

type dfstatPlugin string

var DFSTAT_DEFAULT_COLLECTION_INTERVAL = 10

type fsStruct struct {
	fsSpec    string
	fsFile    string
	fsVfstype string
}

type devices []fsStruct

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
	collection_interval := DFSTAT_DEFAULT_COLLECTION_INTERVAL
	f, err := os.Open("/proc/mounts")
	if err != nil {
		utils.Log(utils.GetLogger(), "dfstat][err:"+err.Error(), 2, 1)
	}
	for {
		var devices []fsStruct
		time.Sleep(time.Second * time.Duration(collection_interval))
		scanner := bufio.NewScanner(f)
		for scanner.Scan() {
			//# Docs come from the fstab(5)
			//# fs_spec     # Mounted block special device or remote filesystem
			//# fs_file     # Mount point
			//# fs_vfstype  # File system type
			//# fs_mntops   # Mount options
			//# fs_freq     # Dump(8) utility flags
			//# fs_passno   # Order in which filesystem checks are done at reboot time
			line := scanner.Text()
			fmt.Println(line)
			fields := strings.Fields(line)
			fmt.Print(len(fields))
			//var fsSpec, fsFile, fsVfstype, fsMntops, fsFrep, fsPassno = "", "", "", "", "", ""
			var fsSpec, fsFile, fsVfstype = "", "", ""
			if len(fields) == 6 {
				fsSpec = fields[0]
				fsFile = fields[1]
				fsVfstype = fields[2]
				//fsMntops = fields[3]
				//fsFrep = fields[4]
				//fsPassno = fields[5]
			} else {
				utils.Log(utils.GetLogger(), "dfstat][error: can't parse line at /proc/mounts", 2, 1)
				continue
			}
			if fsSpec == "none" {
				continue
			}
			if FsTypeIgnore[fsVfstype] || strings.HasPrefix(fsVfstype, "fused.") {
				continue
			}
			if strings.HasPrefix(fsFile, "/dev") || strings.HasPrefix(fsFile, "/sys") ||
				strings.HasPrefix(fsFile, "/proc") || strings.HasPrefix(fsFile, "/lib") ||
				strings.HasPrefix(fsFile, "net:") {
				continue
			}
			// keep /dev/xxx device with shorter fs_file (remove mount binds)
			//if fs_spec.startswith("/dev"):
			//for device in devices:
			//if fs_spec == device[0]:
			//device_found = True
			//if len(fs_file) < len(device[1]):
			//device[1] = fs_file
			//break
			//if not device_found:
			//devices.append([fs_spec, fs_file, fs_vfstype])
			//else:
			//devices.append([fs_spec, fs_file, fs_vfstype])
			device_found := false
			if strings.HasPrefix(fsSpec, "/dev") {
				for i := range devices {
					if fsSpec == devices[i].fsSpec {
						device_found = true
					}
					if len(fsFile) < len(devices[i].fsFile) {
						devices[i].fsFile = fsFile
						break
					}
					if !device_found {
						devices = append(devices, fsStruct{fsSpec, fsFile, fsVfstype})
					}
				}
			} else {
				devices = append(devices, fsStruct{fsSpec, fsFile, fsVfstype})
			}
			for i := range devices {
			}
		}
		f.Seek(0, 0)
	}
}

var DfstatSo dfstatPlugin

//build -gcflags "-l -N" -buildmode=plugin -o plugin/dfstat.so collectors/dfstat.go
