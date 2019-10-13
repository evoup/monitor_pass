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

package inc

import (
    "github.com/antonholmquist/jason"
    "github.com/patrickmn/go-cache"
    "log"
    "sync"
    "time"
)

const (
	LOG_SUFFIX     = "madmonitor2"
	CLIENT_VERSION = "2.0.0.1"
	PROC_ROOT      = "/services/monitor2_deal"
	CONF_FILE      = "madmonitor2.json"
	CONF_SUBPATH   = "conf/"
	WORK_SUBPATH   = "work/"
	MONITOR_ITEMS_CONF_FILE = "monitoritems.json"
	SCRIPT_SUBPATH   = "script/"
	PROC_LIFE      = "3600"
	SLEEP          = "10"
	SEND_PORT      = "8090"
	SEND_HOSTS     = "172.18.0.1,172.18.9.3"
	SERVICE_NAME   = "madmonitor2"
	SERVICE_DESC   = "monitor client by madhouse"
	EVICTINTERVAL  = "6000"
	DEDUPINTERVAL  = "300"
	MAX_SENDQ_SIZE = 10000
	MAX_READQ_SIZE = 100000
	MAX_MSGQ_SIZE  = 100000
	MAX_CONNQ_SIZE = 1
	RPC_SERVER_OK = 20000
	RPC_SERVER_FAIL = 40000
	POSITIVE_SEND_ASSET_API_URL = "http://monitor-web/mmsapi2.0/asset/agent_info"
)

type DefaultConf struct {
	ServerName    string
	ProcLife      string
	Sleep         string
	SendPort      string
	SendHosts     string
	EvictInterval string
	DedupInterval string
    UserScripts   []string
    AssetApiUrl string
}

type Conf DefaultConf

// 存放每个metric+tag对应的信息
type CollectorValue struct {
	Value     string
	Tf        bool
	Line      string
	Timestamp int
}

//func (v *CollectorValue) SetValue(value string) {
//    v.Value = value
//}
//
//func (v *CollectorValue) SetTf(tf bool) {
//    v.Tf = tf
//}
//
//func  (v *CollectorValue) SetLine(line string) {
//    v.Line = line
//}
//
//func  (v *CollectorValue) SetTimestamp(timestamp int) {
//    v.Timestamp = timestamp
//}

// 收集器类，负责管理进程和从进程中获取数据
type Collector struct {
	Name            string
	Interval        int
	Filename        string
	Mtime           int
	LastSpawn       int
	LastDataPoint   int
	NextKill        int
	KillState       int
	Dead            bool
	Generation      int
	CollectorValues map[string]CollectorValue
	LinesSent       int
}

var COLLECTORS = map[string]Collector{}
var GERERATION = 0

var VALID_COLLECTORS = map[string]int{}

var HLog *log.Logger

type ICollector interface {
	Collect()
}

var Wg sync.WaitGroup
// 脚本用的等待组
var Wg1 sync.WaitGroup
var Shutdown = make(chan int)

var MsgQueue = make(chan string, MAX_MSGQ_SIZE)
var ReaderQueue = make(chan string, MAX_READQ_SIZE)
var SenderQueue = make(chan string, MAX_SENDQ_SIZE)
var ReconnectQueue = make(chan string, MAX_CONNQ_SIZE)

type ReaderChannel struct {
	Readerq        chan string
	LinesCollected int
	LinesDropped   int
	EvictInterval  int
	DedupInterval  int
}

type ReconnectChannel struct {
	ReconnectQueue chan string
	LastConnected  int
}

func (c *ReaderChannel) SetLinesCollected(i int) {
	c.LinesCollected = i
}

func (c *ReaderChannel) GetLinesCollected() int {
	return c.LinesCollected
}

func (c *ReaderChannel) SetLinesDropped(i int) {
	c.LinesDropped = i
}

func (c *ReaderChannel) GetLinesDropped() int {
	return c.LinesDropped
}

func (c *ReaderChannel) SetEvictInterval(i int) {
	c.EvictInterval = i
}

func (c *ReaderChannel) GetEvictInterval() int {
	return c.EvictInterval
}

func (c *ReaderChannel) SetDedupInterval(i int) {
	c.DedupInterval = i
}

func (c *ReaderChannel) GetDedupInterval() int {
	return c.DedupInterval
}

func (c *ReaderChannel) AddLinesCollected() {
	c.LinesCollected += 1
}

var ConfObject = &jason.Object{}

type ItemConf struct {
	//	"type": "0",
	Type string `json:"type"`
	//	"dataType": "0",
	DataType string `json:"dataType"`
	//	"snmpCommunity": "",
	SnmpCommunity string `json:"snmpCommunity"`
	//	"snmpOid": "",
	SnmpOid string `json:"snmpOid"`
	//	"name": "CPU $2 time",
	Name string `json:"name"`
	//	"key": "system.cpu.util[,iowait]",
	Key string `json:"key"`
	//	"status": "0",
	Status string `json:"status"`
	//	"lastlogsize": "0",
	LastLogSize string `json:"lastlogsize"`
	//	"units": "%",
	Units string `json:"units"`
	//	"delay": "60",
	Delay string `json:"delay"`
	//	"history": "7",
	History string `json:"history"`
	//	"valueType": "0",
	ValueType string `json:"valueType"`
	//	"multiplier": "0",
	Multiplier string `json:"multiplier"`
	//	"delta": "0"
	Delta string `json:"multiplier"`
}


// config cache
var ConfigCache = cache.New(5*time.Minute, 10*time.Minute)

// 服务端传递过来的消息，不需要的小写
type MonitorItem struct {
    Id         int
    Name       string
    Delay      int
    dateType   int
    desc       string
    error      string
    Key        string
    Multiplier float32
    hostId     int
    templateId int
    Delta      int
}
