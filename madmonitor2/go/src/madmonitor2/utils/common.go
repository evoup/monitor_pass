/*
 * This file is part of madmonitor2.
 * Copyright (c) 2017. Author: yinjia evoex123@gmail.com
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

package utils

import (
    "encoding/base64"
    "fmt"
    "log"
    "os"
    "os/exec"
    "syscall"
    "time"
)
var Debug_level int
func Substr(str string, start, length int) string {
    rs := []rune(str)
    rl := len(rs)
    end := 0

    if start < 0 {
        start = rl - 1 + start
    }
    end = start + length

    if start > end {
        start, end = end, start
    }

    if start < 0 {
        start = 0
    }
    if start > rl {
        start = rl
    }
    if end < 0 {
        end = 0
    }
    if end > rl {
        end = rl
    }

    return string(rs[start:end])
}

func UnixMicro() int64 {
    return time.Now().UnixNano()/1000
}
// Check if no other proc
func SingleProc(pidFile string) bool{
    hLog := GetLogger()
    fd,_:=syscall.Open(pidFile,os.O_RDWR|os.O_CREATE,0644)
    err:=syscall.Flock(fd, syscall.LOCK_EX|syscall.LOCK_NB)
    if err != nil {
        Log(hLog, "common.SingleProc][err:"+err.Error(), 1, Debug_level)
        return false
    }
    return true
}

func Daemonize(nochdir int, noclose int, pid_file string) error {
    var hLog *log.Logger
    hLog = GetLogger()
    var ret, ret2 uintptr
    var err error
    Log(hLog, fmt.Sprintf("common.Daemonize][current ppid:%d", syscall.Getppid()), 1, Debug_level)

    //already a daemon
    if syscall.Getppid() == 1 {
        return nil
    }
    Log(hLog, "common.Daemonize][will daemonize",1, Debug_level)

    //fork off the parent process
    ret, ret2, err = syscall.RawSyscall(syscall.SYS_FORK, 0, 0, 0)
    if err != nil {
        var s string
        s = fmt.Sprintf("%T", err)
        if s != "syscall.Errno" {
            Log(hLog, "common.Daemonize][fork err:"+s,1,4)
            return err
        } else {
            Log(hLog, "common.Daemonize][fork no err:"+err.Error(),1, Debug_level)
            //no problem see http://www.ibm.com/developerworks/aix/library/au-errnovariable/
        }
    }

    //failure
    if ret2 < 0 {
        os.Exit(-1)
    }

    //if we got a good PID, then we call exit the parent process.
    if ret > 0 {
        os.Exit(0)
    }
    Log(hLog, "common.Daemonize][forked,we in forked process",1, Debug_level)

    /* Change the file mode mask */
    _ = syscall.Umask(0)
    Log(hLog, "common.Daemonize][umask zero",1, Debug_level)

    //create a new SID for the child process
    s_ret, s_errno := syscall.Setsid()
    if s_ret < 0 || s_errno != nil {
        log.Printf("common.Daemonize][Error: syscall.Setsid errno: %d", s_errno)
    }
    Log(hLog, "common.Daemonize][sid seted",1, Debug_level)

    if nochdir == 0 {
        os.Chdir("/")
        Log(hLog, "common.Daemonize][chdir to root",1, Debug_level)
    }

    if noclose == 0 {
        f, e := os.OpenFile("/dev/null", os.O_RDWR, 0)
        if e == nil {
            fd := f.Fd()
            syscall.Dup2(int(fd), int(os.Stdin.Fd()))
            syscall.Dup2(int(fd), int(os.Stdout.Fd()))
            syscall.Dup2(int(fd), int(os.Stderr.Fd()))
            Log(hLog, "common.Daemonize][fs closed",1, Debug_level)
        }
    }

    if !FilePutContent(pid_file,fmt.Sprintf("%d",os.Getpid())) {
        Log(hLog, "common.Daemonize][write pid fail",1, Debug_level)
    }
    Log(hLog, "common.Daemonize][daemonize done",1, Debug_level)
    return nil
}

func base64Encode(src []byte) []byte {
    const (
        base64Table = "123QRSTUabcdVWXYZHijKLAWDCABDstEFGuvwxyzGHIJklmnopqr234560178912"
    )
    var coder = base64.NewEncoding(base64Table)
    return []byte(coder.EncodeToString(src))
}

func base64Decode(src []byte) ([]byte, error) {
    const (
        base64Table = "123QRSTUabcdVWXYZHijKLAWDCABDstEFGuvwxyzGHIJklmnopqr234560178912"
    )
    var coder = base64.NewEncoding(base64Table)
    return coder.DecodeString(string(src))
}

// create directory
func MakeDir(path string, mask string) {
    var hLog *log.Logger
    hLog = GetLogger()
    app := "/bin/mkdir"
    arg0 := "-p"
    arg1 := path
    cmd := exec.Command(app, arg0,arg1)
    _, err := cmd.Output()
    if err != nil {
        Log(hLog, "common.MakeDir][path:"+path, 2, Debug_level)
        Log(hLog, "common.MakeDir]["+fmt.Sprintf("%T", err), 2, Debug_level)
        // Do exit
        return
    }
    Log(hLog, "common.MakeDir][dir:"+path+" created", 2, Debug_level)
}

func FileExists(name string) bool {
    _, err := os.Stat(name)
    return !os.IsNotExist(err)
}

