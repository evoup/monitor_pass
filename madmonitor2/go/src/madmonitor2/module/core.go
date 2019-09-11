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

package module

import (
    "bytes"
    "encoding/base64"
    "encoding/json"
    "flag"
    "fmt"
    "github.com/antonholmquist/jason"
    "github.com/takama/daemon"
    "log"
    "madmonitor2/config"
    "madmonitor2/inc"
    "madmonitor2/utils"
    "os"
    "os/signal"
    "regexp"
    "strconv"
    "strings"
    "syscall"
    "time"
)

var errlog *log.Logger

// flag define
var version = flag.Bool("version", false, "")
var DebugLevel = flag.Int("d", 4, "-d=4")
var Pidfile = flag.String("pidfile", "/var/run/madmonitor2.pid", "Write our pidfile")
var daemonize = flag.Bool("daemonize", false, "Run as a background daemon.")
var daemonizeShort = flag.Bool("D", false, "Run as a background daemon.")
var ServiceInstall = flag.Bool("service_install", false, "")
var ServiceRemove = flag.Bool("service_remove", false, "")
var ServiceStart = flag.Bool("service_start", false, "")
var ServiceStop = flag.Bool("service_stop", false, "")
var ServiceStatus = flag.Bool("service_status", false, "")

// Service has embedded daemon
type Service struct {
	daemon.Daemon
}

func init() {
	errlog = log.New(os.Stderr, "", 0)
}

func Init() (*log.Logger, *jason.Object) {
	flag.Parse()
	args := flag.Args()
	if len(args) > 0 {
		usage := "Usage: please see madmonitor2 -h"
		fmt.Printf(usage + "\n")
		os.Exit(0)
	}
	if *version {
		fmt.Printf("Version %s\n", inc.CLIENT_VERSION)
		os.Exit(0)
	}

	utils.Debug_level = *DebugLevel

	logger := utils.GetLogger()
	utils.Log(logger, "core.Init][Initiating server........................", 4, *DebugLevel)
	/** make sure only one process running **/
	var pidFile = *Pidfile
	pid := utils.FileGetContent(pidFile)
	if pid == "" {
		utils.FilePutContent(pidFile, fmt.Sprintf("%d", os.Getpid()))
	}
	/*if false == utils.SingleProc(pid_file) {
		utils.Log(logger, "core.Init][last upload process exists", 4, *Debug_level)
		os.Exit(0)
	}*/
	/** if first run, we make config folder **/
	buildConf(logger)

	var optionDaedmon = *daemonize || *daemonizeShort
	if optionDaedmon {
		//utils.Daemonize(0, 1, pid_file)
	} else {

	}
	object, err := parseConf()
	inc.ConfObject = object
	ev, _ := object.GetString("EvictInterval")
	dp, _ := object.GetString("DedupInterval")
	ev1, _ := strconv.Atoi(ev)
	dp1, _ := strconv.Atoi(dp)
	readChannel := NewReadChannel(ev1, dp1)
	reconnectChannel := NewConnectChannel()
	userScripts, _ := object.GetStringArray("UserScripts")
    for _, userScript := range userScripts {
        keyCmd := strings.Split(userScript, ",")
        fmt.Println(keyCmd[0])
        fmt.Println(keyCmd[1])
    }

	var dependencies []string
	srv, err := daemon.New(inc.SERVICE_NAME, inc.SERVICE_DESC, dependencies...)
	if err != nil {
		utils.Log(logger, "core.Init][err:"+err.Error(), 1, *DebugLevel)
		errlog.Println("Error: ", err)
		os.Exit(1)
	}
	service := &Service{srv}
	status, err := service.Manage(*readChannel, reconnectChannel)
	if err != nil {
		utils.Log(logger, "core.Init][status:"+status+"][err:"+err.Error(), 1, *DebugLevel)
		errlog.Println(status, "\nError: ", err)
		os.Exit(1)
	}

	fmt.Println(status)
	//loadCollectors()
	return logger, object
}

func buildConf(logger *log.Logger) {
	confExists := utils.FileExists(inc.PROC_ROOT + "/" + inc.CONF_SUBPATH + inc.CONF_FILE)
	if confExists {
		utils.Log(logger, "core.Init][conf and work dir existed", 4, *DebugLevel)
	} else {
		utils.Log(logger, "core.Init][conf and work dir not existed", 4, *DebugLevel)
		wd, _ := os.Getwd()
		utils.Log(logger, "core.Init][current dir:"+wd, 4, *DebugLevel)
		utils.MakeDir(inc.PROC_ROOT, "0755")
		utils.MakeDir(inc.PROC_ROOT+"/"+inc.CONF_SUBPATH, "0755")
		utils.MakeDir(inc.PROC_ROOT+"/"+inc.WORK_SUBPATH, "0777")
		utils.MakeDir(inc.PROC_ROOT+"/"+inc.SCRIPT_SUBPATH, "0755")
		config.WriteDefaultsJson(inc.PROC_ROOT + "/" + inc.CONF_SUBPATH + inc.CONF_FILE)
		utils.Log(logger, "core.Init][build configuration file,done. run again", 4, *DebugLevel)
		os.Exit(0)
	}
}

func parseConf() (*jason.Object, error) {
	file, err := os.Open(inc.PROC_ROOT + "/" + inc.CONF_SUBPATH + inc.CONF_FILE)
	if err == nil {
		conf, err := jason.NewObjectFromReader(file)
		if err == nil {
			return conf, nil
		}
		return nil, err
	} else {
		return nil, err
	}
}

// Manage by daemon commands or run the daemon
func (service *Service) Manage(readChannel inc.ReaderChannel, reconnectChannel *inc.ReconnectChannel) (string, error) {
	interrupt := make(chan os.Signal, 1)
	signal.Notify(interrupt, os.Interrupt, os.Kill, syscall.SIGTERM)

	// if received any kind of command, do it
	if *ServiceInstall {
		return service.Install()
	}
	if *ServiceRemove {
		return service.Remove()
	}
	if *ServiceStart {
		return service.Start()
	}
	if *ServiceStop {
		fmt.Println("stop")
		return service.Stop()
	}
	if *ServiceStatus {
		return service.Status()
	}
    go NewUpdateConfigServer()
	// because system network maybe not ready after reboot, when as a systemd service called, so wait a few seconds
	time.Sleep(time.Second*1)
	go runRead(readChannel)

	// we must open connection to server before send data
	//cName, foundServer, serverConn := ConnectToServer(true, ServerConnection, readChannel, reconnectChannel)
	ConnectToServer(true, ServerConnection, readChannel, reconnectChannel)
	//go run_reconnect(ServerConnection, readChannel, reconnectChannel)
	go runGetConfig(ServerConnection)
	//auth(serverConn, cName, foundServer, readChannel, reconnectChannel)

	go mainLoop()

	for {
		select {
		case killSignal := <-interrupt:
			fmt.Println("Got signal:", killSignal)
			utils.Log(HLog, "core.Init][last upload process exists", 1, *DebugLevel)
			if killSignal == os.Interrupt {
				return "Daemon was interrupted by system signal", nil
			}
			return "Daemon was killed", nil
		}
	}
}

func auth(serverConn *ServerConn, cName string, foundServer bool, readChannel inc.ReaderChannel, reconnectChannel *inc.ReconnectChannel) {
	conn := serverConn.conn
	///////// scram sha-1安全认证 ////////
	clientFirstMsg, cNonce := scramSha1FirstMessage(cName)
	conn.Write([]byte(clientFirstMsg))
	serverFirstMessageData := make([]byte, 1024)
	conn.Read(serverFirstMessageData)
	serverFirstMessageData = bytes.Trim(serverFirstMessageData, "\x00")
	// removing NUL characters from bytes
	fmt.Println(string(serverFirstMessageData))
	finalMessage, salt, sNonce, iter := scramSha1FinalMessage(serverFirstMessageData, cName, cNonce)
	conn.Write(finalMessage)
	//conn.Write([]byte("test"))
	serverFinalMessageData := make([]byte, 1024)
	conn.Read(serverFinalMessageData)
	serverFinalMessageData = bytes.Trim(serverFinalMessageData, "\x00")
	fmt.Println(string(serverFinalMessageData))
	// 检查server final message
	submatch := ServerFinalMessage.FindAllStringSubmatch(string(serverFinalMessageData), -1)
	if submatch != nil {
		serverSignature := submatch[0][1]
		decodeBytes, err := base64.StdEncoding.DecodeString(serverSignature)
		if err != nil {
			utils.Log(utils.GetLogger(), "core.Init][error:auth invalid", 1, *DebugLevel)
			os.Exit(1)
		}
		fmt.Println("decodeBytes:" + string(decodeBytes))
		cPass := []byte(ClientPass)
		cHeader := ClientHeader
		if !isValidServer(cName, cPass, cNonce, []byte(sNonce), salt, cHeader, decodeBytes, iter,
			string(serverFirstMessageData)) {
			utils.Log(utils.GetLogger(), "core.Init][error:auth invalid", 1, *DebugLevel)
			os.Exit(1)
		}
	} else {
		utils.Log(utils.GetLogger(), "core.Init][error:auth invalid", 1, *DebugLevel)
		os.Exit(1)
	}
	if foundServer {
		go runSend(readChannel, serverConn, reconnectChannel)
	} else {
		utils.Log(utils.GetLogger(), "core.Init][all data collector servers down!", 1, *DebugLevel)
		os.Exit(1)
	}
}

// run_send like tcollector`s sender_thread
func runSend(readChannel inc.ReaderChannel, sc *ServerConn, reconnectChannel *inc.ReconnectChannel) {
	for {
		select {
		case msg := <-readChannel.Readerq:
			_, err := sc.conn.Write([]byte(msg))
			if err != nil {
				fmt.Printf(err.Error())
				utils.Log(HLog, "send fail, exit program", 1, *DebugLevel)
                os.Exit(1)
				reconnectChannel.ReconnectQueue <- "broken pipe"
			}
			fmt.Println("message sent:" + msg)
		}
	}
}

// read channel maintain
func runRead(readChannel inc.ReaderChannel) {
	for {
		select {
		case msg := <-inc.MsgQueue:
			fmt.Println(">>>>" + msg)

			if len(msg) > 1024 {
				// todo check which collector produce this msg
				utils.Log(HLog, "line to long", 1, *DebugLevel)
				continue
			}
			processLine(readChannel, msg)
		}
	}
}

// reconnect server channel
func run_reconnect(sc *ServerConn, readChannel inc.ReaderChannel, reconnectChannel *inc.ReconnectChannel) {
	lastOnline := true
	for {
		time.Sleep(time.Second*15)
		select {
		case msg := <- reconnectChannel.ReconnectQueue:
			if msg=="broken pipe" {
				fmt.Println("detect lost connect from server, will reconnect")
				lastOnline=false
			}
		}
		if lastOnline==false {
			fmt.Println("make new conn")
			_, _, conn1 := ConnectToServer(false, sc, readChannel, reconnectChannel)
			if conn1 != nil {
				sc = conn1
				lastOnline = true
				<- reconnectChannel.ReconnectQueue
			}
		}

		if TestServerOn() {
			fmt.Println("》》》》》》》》》》》》》》》》》》》》》》》》》》》》》》》》》》")
			continue
		} else {
			lastOnline = false
		}
	}
}

// get conf from server
func runGetConfig(sc *ServerConn) {
	for {
		time.Sleep(time.Second*120)
		//cName, _ := inc.ConfObject.GetString("ServerName")
		cName := "host1"
		_, err := sc.conn.Write([]byte("getconf|" + cName))
		if err != nil {
			fmt.Println("err:" + err.Error())
		}
		//serverReturnConfig := make([]byte, 9222)
		//sc.conn.Read(serverReturnConfig)
		buf := make([]byte, 0, 40960) // big buffer
		tmp := make([]byte, 256)     // using small tmo buffer for demonstrating
		for {
			n, err := sc.conn.Read(tmp)
			if err != nil {
				//if err != io.EOF {
				//	fmt.Println("read error:", err)
				//}
				break
			}
			//fmt.Println("got", n, "bytes.")
			buf = append(buf, tmp[:n]...)

		}
		//serverReturnConfig1 := `[{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"CPU $2 time","key":"system.cpu.util[,iowait]","status":"0","lastlogsize":"0","units":"%","delay":"60","history":"7","valueType":"0","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Total swap space","key":"system.swap.size[,total]","status":"0","lastlogsize":"0","units":"B","delay":"3600","history":"7","valueType":"3","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"CPU $2 time","key":"system.cpu.util[,interrupt]","status":"0","lastlogsize":"0","units":"%","delay":"60","history":"7","valueType":"0","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Free disk space on $1","key":"vfs.fs.size[{#FSNAME},free]","status":"0","lastlogsize":"0","units":"B","delay":"60","history":"7","valueType":"3","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"CPU $2 time","key":"system.cpu.util[,system]","status":"0","lastlogsize":"0","units":"%","delay":"60","history":"7","valueType":"0","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Processor load (5 min average per core)","key":"system.cpu.load[percpu,avg5]","status":"0","lastlogsize":"0","units":"","delay":"60","history":"7","valueType":"0","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Free inodes on $1 (percentage)","key":"vfs.fs.inode[{#FSNAME},pfree]","status":"0","lastlogsize":"0","units":"%","delay":"60","history":"7","valueType":"0","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Total disk space on $1","key":"vfs.fs.size[{#FSNAME},total]","status":"0","lastlogsize":"0","units":"B","delay":"3600","history":"7","valueType":"3","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Processor load (15 min average per core)","key":"system.cpu.load[percpu,avg15]","status":"0","lastlogsize":"0","units":"","delay":"60","history":"7","valueType":"0","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Used disk space on $1","key":"vfs.fs.size[{#FSNAME},used]","status":"0","lastlogsize":"0","units":"B","delay":"60","history":"7","valueType":"3","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Checksum of $1","key":"vfs.file.cksum[/etc/passwd]","status":"0","lastlogsize":"0","units":"","delay":"3600","history":"7","valueType":"3","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Maximum number of processes","key":"kernel.maxproc","status":"0","lastlogsize":"0","units":"","delay":"3600","history":"7","valueType":"3","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Processor load (1 min average per core)","key":"system.cpu.load[percpu,avg1]","status":"0","lastlogsize":"0","units":"","delay":"60","history":"7","valueType":"0","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Host name","key":"system.hostname","status":"0","lastlogsize":"0","units":"","delay":"3600","history":"7","valueType":"1","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Number of running processes","key":"proc.num[,,run]","status":"0","lastlogsize":"0","units":"","delay":"60","history":"7","valueType":"3","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Maximum number of opened files","key":"kernel.maxfiles","status":"0","lastlogsize":"0","units":"","delay":"3600","history":"7","valueType":"3","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Version of zabbix_agent(d) running","key":"agent.version","status":"0","lastlogsize":"0","units":"","delay":"3600","history":"7","valueType":"1","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"System information","key":"system.uname","status":"0","lastlogsize":"0","units":"","delay":"3600","history":"7","valueType":"1","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Free swap space","key":"system.swap.size[,free]","status":"0","lastlogsize":"0","units":"B","delay":"60","history":"7","valueType":"3","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Number of logged in users","key":"system.users.num","status":"0","lastlogsize":"0","units":"","delay":"60","history":"7","valueType":"3","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"CPU $2 time","key":"system.cpu.util[,idle]","status":"0","lastlogsize":"0","units":"%","delay":"60","history":"7","valueType":"0","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"CPU $2 time","key":"system.cpu.util[,softirq]","status":"0","lastlogsize":"0","units":"%","delay":"60","history":"7","valueType":"0","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Outgoing network traffic on $1","key":"net.if.out[{#IFNAME}]","status":"0","lastlogsize":"0","units":"bps","delay":"60","history":"7","valueType":"3","multiplier":"1","delta":"1"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"CPU $2 time","key":"system.cpu.util[,user]","status":"0","lastlogsize":"0","units":"%","delay":"60","history":"7","valueType":"0","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Host name of zabbix_agentd running","key":"agent.hostname","status":"0","lastlogsize":"0","units":"","delay":"3600","history":"7","valueType":"1","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Free swap space in %","key":"system.swap.size[,pfree]","status":"0","lastlogsize":"0","units":"%","delay":"60","history":"7","valueType":"0","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Host local time","key":"system.localtime","status":"0","lastlogsize":"0","units":"unixtime","delay":"60","history":"7","valueType":"3","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Agent ping","key":"agent.ping","status":"0","lastlogsize":"0","units":"","delay":"60","history":"7","valueType":"3","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Interrupts per second","key":"system.cpu.intr","status":"0","lastlogsize":"0","units":"ips","delay":"60","history":"7","valueType":"3","multiplier":"0","delta":"1"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Free disk space on $1 (percentage)","key":"vfs.fs.size[{#FSNAME},pfree]","status":"0","lastlogsize":"0","units":"%","delay":"60","history":"7","valueType":"0","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"CPU $2 time","key":"system.cpu.util[,steal]","status":"0","lastlogsize":"0","units":"%","delay":"60","history":"7","valueType":"0","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Incoming network traffic on $1","key":"net.if.in[{#IFNAME}]","status":"0","lastlogsize":"0","units":"bps","delay":"60","history":"7","valueType":"3","multiplier":"1","delta":"1"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Available memory","key":"vm.memory.size[available]","status":"0","lastlogsize":"0","units":"B","delay":"60","history":"7","valueType":"3","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Number of processes","key":"proc.num[]","status":"0","lastlogsize":"0","units":"","delay":"60","history":"7","valueType":"3","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Context switches per second","key":"system.cpu.switches","status":"0","lastlogsize":"0","units":"sps","delay":"60","history":"7","valueType":"3","multiplier":"0","delta":"1"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Host boot time","key":"system.boottime","status":"0","lastlogsize":"0","units":"unixtime","delay":"600","history":"7","valueType":"3","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"CPU $2 time","key":"system.cpu.util[,nice]","status":"0","lastlogsize":"0","units":"%","delay":"60","history":"7","valueType":"0","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"Total memory","key":"vm.memory.size[total]","status":"0","lastlogsize":"0","units":"B","delay":"3600","history":"7","valueType":"3","multiplier":"0","delta":"0"},{"type":"0","dataType":"0","snmpCommunity":"","snmpOid":"","name":"System uptime","key":"system.uptime","status":"0","lastlogsize":"0","units":"uptime","delay":"600","history":"7","valueType":"3","multiplier":"0","delta":"0"}]`
		itemConf := []inc.ItemConf{}
		err = json.Unmarshal([]byte(buf), &itemConf)
		if err != nil {
			fmt.Println("err:" + err.Error())
		}
		if err != nil {
			utils.Log(HLog, "get conf error:" + err.Error(), 1, *DebugLevel)
		}
	}
}

func processLine(readChannel inc.ReaderChannel, line string) {
	// msg的第一位是sysload是模块名
	readChannel.AddLinesCollected()
	// 解析消息
	r := regexp.MustCompile(`^([a-zA-Z0-9]+)\s+([.a-zA-Z0-9,_/\-\[\]]+)\s+(\d+\.?\d+)\s+(\S+?)((?:\s+[-_./a-zA-Z0-9]+=[-_./a-zA-Z0-9]+)*)\n$`)
	submatch := r.FindAllStringSubmatch(line, -1)
	if submatch != nil {
		fmt.Println(submatch)
		collectorName := submatch[0][1]
		metricName := submatch[0][2]
		timestamp, _ := strconv.Atoi(submatch[0][3])
		value := submatch[0][4]
		tags := submatch[0][5]
		fmt.Println("collector name:" + collectorName)
		fmt.Println("metric name:" + metricName)
		fmt.Println("timestamp:" + strconv.Itoa(timestamp))
		fmt.Println("value:" + value)
		fmt.Println("tags:" + tags)
		// 去重
		// 如果数据点是重复的，保存但不发送，保存之前的timestamp，当数据发生变化，
		// 我们不发送最后一次进来的指标的值，而是第一次进来。如果达到了去重间隔，打印该数值。
		dedupInteval := 300
		if COLLECTORS[collectorName+".so"].CollectorValues[metricName].Value == value &&
			timestamp-COLLECTORS[collectorName+".so"].CollectorValues[metricName].Timestamp < dedupInteval {
			collectorValue := inc.CollectorValue{value, true, line, timestamp}
			COLLECTORS[collectorName+".so"].CollectorValues[metricName] = collectorValue
			fmt.Println("data duplicate, ignore:" + metricName)
			return
		}
		collectorValue := inc.CollectorValue{value, false, line, timestamp}
		COLLECTORS[collectorName+".so"].CollectorValues[metricName] = collectorValue
		c := COLLECTORS[collectorName+".so"] // 解决大坑map的index操作获得的变量无法取其指针
		c.LinesSent += 1
		COLLECTORS[collectorName+".so"] = c
		readChannel.Readerq <- line
	}
}

// 执行我们模块的收集方法
func mainLoop() {
	// 检查collector的心跳，每10分钟一次
	nextHeartbeat := int(time.Now().Unix() + 600)
	for {
		populateCollectors()
		spawnChildren()
		time.Sleep(time.Second * 15)
		utils.Log(utils.GetLogger(), "core.Init][main loop next iter", 2, *DebugLevel)
		now := int(time.Now().Unix())
		if now > nextHeartbeat {
			nextHeartbeat = now + 600
		}
	}
}

// load implemented collectors key name of collector,value interval
func loadCollectors() {
	inc.VALID_COLLECTORS["sysload"] = 0
	inc.VALID_COLLECTORS["dfstat"] = 0
	inc.VALID_COLLECTORS["ifstat"] = 0
	inc.VALID_COLLECTORS["procstats"] = 0
}
