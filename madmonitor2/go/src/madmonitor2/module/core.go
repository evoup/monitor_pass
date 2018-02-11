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
	"log"
	"os"
	"madmonitor2/utils"
	"madmonitor2/inc"
	"fmt"
	"time"
	"flag"
	"madmonitor2/config"
	"github.com/antonholmquist/jason"
	"github.com/takama/daemon"
	"os/signal"
	"syscall"
	"strconv"
	"regexp"
	"strings"
	"net"
)

// start unix timestamp
var StartTime = time.Now().Unix()

var errlog *log.Logger

// flag define
var version = flag.Bool("version", false, "")
var Debug_level = flag.Int("d", 4, "-d=4")
var Pidfile = flag.String("pidfile", "/var/run/madmonitor2.pid", "Write our pidfile")
var daemonize = flag.Bool("daemonize", false, "Run as a background daemon.")
var daemonizeShort = flag.Bool("D", false, "Run as a background daemon.")
var Service_install = flag.Bool("service_install", false, "")
var Service_remove = flag.Bool("service_remove", false, "")
var Service_start = flag.Bool("service_start", false, "")
var Service_stop = flag.Bool("service_stop", false, "")
var Service_status = flag.Bool("service_status", false, "")

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

	utils.Debug_level = *Debug_level

	logger := utils.GetLogger()
	utils.Log(logger, "core.Init][Initiating server........................", 4, *Debug_level)
	/** make sure only one process running **/
	var pid_file = *Pidfile
	pid := utils.FileGetContent(pid_file)
	if pid == "" {
		utils.FilePutContent(pid_file, fmt.Sprintf("%d", os.Getpid()))
	}
	/*if false == utils.SingleProc(pid_file) {
		utils.Log(logger, "core.Init][last upload process exists", 4, *Debug_level)
		os.Exit(0)
	}*/
	/** if first run, we make config folder **/
	buildConf(logger)

	var optionDaedmon = (*daemonize || *daemonizeShort)
	if (optionDaedmon) {
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

	dependencies := []string{}
	srv, err := daemon.New(inc.SERVICE_NAME, inc.SERVICE_DESC, dependencies...)
	if err != nil {
		utils.Log(logger, "core.Init][err:"+err.Error(), 1, *Debug_level)
		errlog.Println("Error: ", err)
		os.Exit(1)
	}
	service := &Service{srv}
	status, err := service.Manage(*readChannel)
	if err != nil {
		utils.Log(logger, "core.Init][status:"+status+"][err:"+err.Error(), 1, *Debug_level)
		errlog.Println(status, "\nError: ", err)
		os.Exit(1)
	}

	fmt.Println(status)
	loadCollectors()
	return logger, object
}

func buildConf(logger *log.Logger) {
	confExists := utils.FileExists(inc.PROC_ROOT + "/" + inc.CONF_SUBPATH + inc.CONF_FILE)
	if confExists {
		utils.Log(logger, "core.Init][conf and work dir existed", 4, *Debug_level)
	} else {
		utils.Log(logger, "core.Init][conf and work dir not existed", 4, *Debug_level)
		wd, _ := os.Getwd()
		utils.Log(logger, "core.Init][current dir:"+wd, 4, *Debug_level)
		utils.MakeDir(inc.PROC_ROOT, "0755")
		utils.MakeDir(inc.PROC_ROOT+"/"+inc.CONF_SUBPATH, "0755")
		utils.MakeDir(inc.PROC_ROOT+"/"+inc.WORK_SUBPATH, "0755")
		config.WriteDefaultsJson(inc.PROC_ROOT + "/" + inc.CONF_SUBPATH + inc.CONF_FILE)
		utils.Log(logger, "core.Init][build configuration file,done. run again", 4, *Debug_level)
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
func (service *Service) Manage(readChannel inc.ReaderChannel) (string, error) {
	interrupt := make(chan os.Signal, 1)
	signal.Notify(interrupt, os.Interrupt, os.Kill, syscall.SIGTERM)

	// if received any kind of command, do it
	if *Service_install {
		return service.Install()
	}
	if *Service_remove {
		return service.Remove()
	}
	if *Service_start {
		return service.Start()
	}
	if *Service_stop {
		fmt.Println("stop")
		return service.Stop()
	}
	if *Service_status {
		return service.Status()
	}
	go run_read(readChannel)
	// we must open connection to server before send data
	// sendHosts := strings.Split(inc.SEND_HOSTS, ",")
	sendHost, _ := inc.ConfObject.GetString("SendHosts")
	sendHosts := strings.Split(sendHost, ",")
	sendPort, _ := inc.ConfObject.GetString("SendPort")
	cName, _ := inc.ConfObject.GetString("ServerName")
	foundServer := false
	for i := range sendHosts {
		//c, e := DialTimeout(sendHosts[i] + ":" + inc.SEND_PORT, 5*time.Second)
		addr, err := net.ResolveTCPAddr("tcp", sendHosts[i] + ":" + sendPort)
		conn, err := net.DialTCP("tcp", nil, addr)
		///////// scram sha-1安全认证 ////////
		clientFirstMsg,cNonce := scramSha1FirstMessage(cName)
		conn.Write([]byte(clientFirstMsg))
		data := make([]byte, 1024)
		conn.Read(data)
		fmt.Println(string(data))
		finalMessage := scramSha1FinalMessage(string(data), string(cNonce))
		conn.Write(finalMessage)
		//conn.Write([]byte("test"))
		data2 := make([]byte, 1024)
		conn.Read(data2)
		fmt.Println(string(data2))
		//id, err := c.conn.Cmd(string(clientFirstMsg))
		//if err != nil {
		//	continue
		//}
		//c.conn.StartResponse(id)
		//defer c.conn.EndResponse(id)
		//text, err1 := c.conn.ReadContinuedLineBytes()
		//if err1 != nil {
		//	continue
		//}
		//fmt.Println(text)
		////////////////////////////////////
		if err != nil {
			utils.Log(utils.GetLogger(), "core.Init][error:" + err.Error(), 1, *Debug_level)
		} else {
			foundServer = true
			//go run_send(readChannel, c)
			break
		}
	}
	if !foundServer {
		utils.Log(utils.GetLogger(), "core.Init][all data collector servers down!", 1, *Debug_level)
		os.Exit(1)
	}


	go main_loop()

	for {
		select {
		case killSignal := <-interrupt:
			fmt.Println("Got signal:", killSignal)
			utils.Log(HLog, "core.Init][last upload process exists", 1, *Debug_level)
			if killSignal == os.Interrupt {
				return "Daemon was interrupted by system signal", nil
			}
			return "Daemon was killed", nil
		}
	}
}

// run_send like tcollector`s sender_thread
func run_send(readChannel inc.ReaderChannel, c *ServerConn) {
	for {
		select {
		case msg := <- readChannel.Readerq:
			conn := c.conn
			_, err1 := conn.Cmd(msg)
			if err1 != nil {
				fmt.Printf(err1.Error())
			}
			fmt.Println("message sent:" + msg)
		}
	}
}

// read channel maintain
func run_read(readChannel inc.ReaderChannel) {
	for {
		select {
		case msg := <-inc.MsgQueue:
			fmt.Println(">>>>" + msg)

			if len(msg) > 1024 {
				// todo check which collector produce this msg
				utils.Log(HLog, "line to long", 1, *Debug_level)
				continue
			}
			process_line(readChannel, msg)
		}
	}
}

func process_line(readChannel inc.ReaderChannel, line string) {
	// msg的第一位是sysload是模块名
	// fmt.Println("process_line: " + msg)
	readChannel.AddLinesCollected()
	// 解析消息
	r := regexp.MustCompile(`^([a-zA-Z0-9]+)\s+([.a-zA-Z0-9]+)\s+(\d+\.?\d+)\s+(\S+?)((?:\s+[-_./a-zA-Z0-9]+=[-_./a-zA-Z0-9]+)*)\n$`)
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
		if COLLECTORS[collectorName + ".so"].CollectorValues[metricName].Value==value &&
			timestamp - COLLECTORS[collectorName + ".so"].CollectorValues[metricName].Timestamp < dedupInteval {
			collectorValue := inc.CollectorValue{value, true, line, timestamp}
			COLLECTORS[collectorName + ".so"].CollectorValues[metricName] = collectorValue
			return
		}
		collectorValue := inc.CollectorValue{value, false, line, timestamp}
		COLLECTORS[collectorName + ".so"].CollectorValues[metricName] = collectorValue
		c := COLLECTORS[collectorName + ".so"] // 解决大坑map的index操作获得的变量无法取其指针
		c.LinesSent += 1
		COLLECTORS[collectorName + ".so"] = c
		readChannel.Readerq <- line
	}
}

// 执行我们模块的收集方法
func main_loop() {
	// 检查collector的心跳，每10分钟一次
	next_heartbeat := int(time.Now().Unix() + 600)
	for {
		populate_collectors()
		spawn_children()
		time.Sleep(time.Second * 15)
		utils.Log(utils.GetLogger(), "core.Init][main loop next iter", 2, *Debug_level)
		now := int(time.Now().Unix())
		if now > next_heartbeat {
			next_heartbeat = now + 600
		}
	}
}

// load implemented collectors key name of collector,value interval
func loadCollectors() {
	inc.VALID_COLLECTORS["sysload"] = 0

}
