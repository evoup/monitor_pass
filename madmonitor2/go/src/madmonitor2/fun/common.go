/**
 *Project: madmonitor2 
 *Name: common.go
 *Auther: yinjia evoex123@gmail.com
 *Create:
 *Last Modified:
 */

package common

import (
    "madmonitor2/inc"
    "encoding/base64"
    "fmt"
    "log"
    "log/syslog"
    "os"
    "os/exec"
    "syscall"
    "time"
)

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
    hLog := LogInit()
    fd,_:=syscall.Open(pidFile,os.O_RDWR|os.O_CREATE,0644)
    err:=syscall.Flock(fd, syscall.LOCK_EX|syscall.LOCK_NB)
    if err != nil {
        Log(hLog, "common.SingleProc][err:"+err.Error())
        return false
    }
    return true
}

func Daemonize(nochdir int, noclose int) error {
    var hLog *log.Logger
    hLog = LogInit()
    var ret, ret2 uintptr
    var err error
    Log(hLog, fmt.Sprintf("common.Daemonize][current ppid:%d", syscall.Getppid()))

    //already a daemon
    if syscall.Getppid() == 1 {
        return nil
    }
    Log(hLog, "common.Daemonize][will daemonize")

    //fork off the parent process
    ret, ret2, err = syscall.RawSyscall(syscall.SYS_FORK, 0, 0, 0)
    if err != nil {
        var s string
        s = fmt.Sprintf("%T", err)
        if s != "syscall.Errno" {
            Log(hLog, "common.Daemonize][fork err:"+s)
            return err
        } else {
            Log(hLog, "common.Daemonize][fork no err:"+err.Error())
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
    Log(hLog, "common.Daemonize][forked,we in forked process")

    /* Change the file mode mask */
    _ = syscall.Umask(0)
    Log(hLog, "common.Daemonize][umask zero")

    //create a new SID for the child process
    s_ret, s_errno := syscall.Setsid()
    if s_ret < 0 || s_errno != nil {
        log.Printf("common.Daemonize][Error: syscall.Setsid errno: %d", s_errno)
    }
    Log(hLog, "common.Daemonize][sid seted")

    if nochdir == 0 {
        os.Chdir("/")
        Log(hLog, "common.Daemonize][chdir to root")
    }

    if noclose == 0 {
        f, e := os.OpenFile("/dev/null", os.O_RDWR, 0)
        if e == nil {
            fd := f.Fd()
            syscall.Dup2(int(fd), int(os.Stdin.Fd()))
            syscall.Dup2(int(fd), int(os.Stdout.Fd()))
            syscall.Dup2(int(fd), int(os.Stderr.Fd()))
            Log(hLog, "common.Daemonize][fs closed")
        }
    }

    pid_file:=inc.PROC_ROOT+"/"+inc.RUN_SUBPATH+inc.PROC_NAME+".pid"
    FilePutContent(pid_file,fmt.Sprintf("%d",os.Getpid()))
    Log(hLog, "common.Daemonize][daemonize done")
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

// syslog init
func LogInit() *log.Logger {
    Log, err := syslog.NewLogger(syslog.LOG_DEBUG, log.Lmicroseconds)
    if err != nil {
        log.Fatal(err)
    }
    return Log
}

// use syslog log item
func Log(l *log.Logger, s string) {
    l.Print("[" + s + "][" + inc.LOG_SUFFIX + "." + inc.SVN_VERSION + "]")
}

// create directory
func MakeDir(path string, mask string) {
    var hLog *log.Logger
    hLog = LogInit()
    app := "/bin/mkdir"
    arg0 := "-p"
    arg1 := path
    cmd := exec.Command(app, arg0,arg1)
    _, err := cmd.Output()
    if err != nil {
        Log(hLog, "common.MakeDir][path:"+path)
        Log(hLog, "common.MakeDir]["+fmt.Sprintf("%T", err))
        // Do exit
        return
    }
    Log(hLog, "common.MakeDir][dir:"+path+" created")
}

// exists returns whether the given file or directory exists or not
func FileExists(path string) (bool, error) {
    _, err := os.Stat(path)
    if err == nil {
        return true, nil
    }
    if os.IsNotExist(err) {
        return false, nil
    }
    return false, err
}

