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
    "bufio"
    "fmt"
    "madmonitor2/inc"
    "madmonitor2/utils"
    "os"
    "strings"
    "syscall"
    "time"
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

var DFSTAT_DEFAULT_COLLECTION_INTERVAL = 60

type fsStruct struct {
    fsSpec    string
    fsFile    string
    fsVfstype string
}

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
    host, _ := inc.ConfObject.GetString("ServerName")
    //host = s.Replace(host, ".", "", -1)
    //host = s.Replace(host, "-", "", -1)
    metricPrefix := "apps.backend." + host + "."
    collection_interval := DFSTAT_DEFAULT_COLLECTION_INTERVAL
    f, err := os.Open("/proc/mounts")
    if err != nil {
        utils.Log(utils.GetLogger(), "dfstat][err:"+err.Error(), 2, 1)
    }
    for {
        timestamp := time.Now().Unix()
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
            fields := strings.Fields(line)
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
                for _, device := range devices {
                    if fsSpec == device.fsSpec {
                        device_found = true
                        if len(fsFile) < len(device.fsFile) {
                            device.fsFile = fsFile
                            break
                        }
                    }
                }
                if !device_found {
                    devices = append(devices, fsStruct{fsSpec, fsFile, fsVfstype})
                }
            } else {
                devices = append(devices, fsStruct{fsSpec, fsFile, fsVfstype})
            }
        }
        f.Seek(0, 0)
        for i := range devices {
            fs := syscall.Statfs_t{}
            err := syscall.Statfs(devices[i].fsFile, &fs)
            if err != nil {
                utils.Log(utils.GetLogger(), "dfstat][can't get info for mount point:"+err.Error(), 2, 1)
                continue
            }
            blocks := fs.Blocks * uint64(fs.Bsize)
            avail := fs.Bavail * uint64(fs.Bsize)
            used := blocks - avail
            percentUsed := float64(0)
            if blocks == 0 {
                percentUsed = float64(100)
            } else {
                percentUsed = float64(used) * float64(100.0) / float64(blocks)
            }
            //print("df.bytes.total %d %s mount=%s fstype=%s"
            //% (ts, r.f_frsize * r.f_blocks, fs_file, fs_vfstype))
            //print("df.bytes.used %d %s mount=%s fstype=%s"
            //% (ts, r.f_frsize * used, fs_file, fs_vfstype))
            //print("df.bytes.percentused %d %s mount=%s fstype=%s"
            //% (ts, percent_used, fs_file, fs_vfstype))
            //print("df.bytes.free %d %s mount=%s fstype=%s"
            //% (ts, r.f_frsize * r.f_bfree, fs_file, fs_vfstype))
            fmt.Printf("df.bytes.total %v %v mount=%v fstype=%v\n", timestamp, uint64(fs.Frsize)*fs.Blocks, devices[i].fsFile, devices[i].fsVfstype)
            fmt.Printf("df.bytes.used %v %v mount=%v fstype=%v\n", timestamp, uint64(fs.Frsize)*fs.Blocks, devices[i].fsFile, devices[i].fsVfstype)
            fmt.Printf("df.bytes.percentused %v %v mount=%v fstype=%v\n", timestamp, percentUsed, devices[i].fsFile, devices[i].fsVfstype)
            fmt.Printf("df.bytes.free %v %v mount=%v fstype=%v\n", timestamp, uint64(fs.Frsize)*fs.Bfree, devices[i].fsFile, devices[i].fsVfstype)

            inc.MsgQueue <- fmt.Sprintf("dfstat %vdf.bytes.total %v %v mount=%v fstype=%v\n", metricPrefix, timestamp, uint64(fs.Frsize)*fs.Blocks, devices[i].fsFile, devices[i].fsVfstype)
            inc.MsgQueue <- fmt.Sprintf("dfstat %vdf.bytes.used %v %v mount=%v fstype=%v\n", metricPrefix, timestamp, uint64(fs.Frsize)*fs.Blocks, devices[i].fsFile, devices[i].fsVfstype)
            inc.MsgQueue <- fmt.Sprintf("dfstat %vdf.bytes.percentused %v %v mount=%v fstype=%v\n", metricPrefix, timestamp, percentUsed, devices[i].fsFile, devices[i].fsVfstype)
            inc.MsgQueue <- fmt.Sprintf("dfstat %vdf.bytes.free %v %v mount=%v fstype=%v\n", metricPrefix, timestamp, uint64(fs.Frsize)*fs.Bfree, devices[i].fsFile, devices[i].fsVfstype)

            used = fs.Files - fs.Ffree
            //# percent_used = 100 if r.f_files == 0 else used * 100.0 / r.f_files
            //if r.f_files == 0:
            //percent_used = 100
            //else:
            //percent_used = used * 100.0 / r.f_files
            //
            //print("df.inodes.total %d %s mount=%s fstype=%s"
            //% (ts, r.f_files, fs_file, fs_vfstype))
            //print("df.inodes.used %d %s mount=%s fstype=%s"
            //% (ts, used, fs_file, fs_vfstype))
            //print("df.inodes.percentused %d %s mount=%s fstype=%s"
            //% (ts, percent_used,  fs_file, fs_vfstype))
            //print("df.inodes.free %d %s mount=%s fstype=%s"
            //% (ts, r.f_ffree, fs_file, fs_vfstype))
            if fs.Files == 0 {
                percentUsed = float64(100)
            } else {
                percentUsed = float64(used) * float64(100.0) / float64(fs.Files)
            }
            fmt.Printf("df.inodes.total %v %v mount=%v fstype=%v\n", timestamp, fs.Files, devices[i].fsFile, devices[i].fsVfstype)
            fmt.Printf("df.inodes.used %v %v mount=%v fstype=%v\n", timestamp, used, devices[i].fsFile, devices[i].fsVfstype)
            fmt.Printf("df.inodes.percentused %v %v mount=%v fstype=%v\n", timestamp, percentUsed, devices[i].fsFile, devices[i].fsVfstype)
            fmt.Printf("df.inodes.free %v %v mount=%v fstype=%v\n", timestamp, fs.Ffree, devices[i].fsFile, devices[i].fsVfstype)

            inc.MsgQueue <- fmt.Sprintf("dfstat %vdf.inodes.total %v %v mount=%v fstype=%v\n", metricPrefix, timestamp, fs.Files, devices[i].fsFile, devices[i].fsVfstype)
            inc.MsgQueue <- fmt.Sprintf("dfstat %vdf.inodes.used %v %v mount=%v fstype=%v\n", metricPrefix, timestamp, used, devices[i].fsFile, devices[i].fsVfstype)
            inc.MsgQueue <- fmt.Sprintf("dfstat %vdf.inodes.percentused %v %v mount=%v fstype=%v\n", metricPrefix, timestamp, percentUsed, devices[i].fsFile, devices[i].fsVfstype)
            inc.MsgQueue <- fmt.Sprintf("dfstat %vdf.inodes.free %v %v mount=%v fstype=%v\n", metricPrefix, timestamp, fs.Ffree, devices[i].fsFile, devices[i].fsVfstype)
        }
    }
}

var DfstatSo dfstatPlugin

//build -gcflags "-l -N" -buildmode=plugin -o plugin/dfstat.so collectors/dfstat.go
