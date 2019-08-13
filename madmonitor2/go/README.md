# monitor agent2.0 

监控代理2.0说明README.md文档

## 安装

```bash
golang version go1.9.2
go get github.com/kless/goconfig/config
cd bin
go build madmonitor2
```



## 编译环境准备

建议为程序运行创建一个专门的用户，如下：

```bash
mkdir ~/go/src
cd !$
ln -s /path/to/monitor_pass/madmonitor2/go/src/madmonitor2/ madmonitor2
adduser monitoruser
sudo chown -R monitoruser:monitoruser /usr/local/lib/madmonitor2
```



## 编译(debug版)

```bash
cd madmonitor2
./build_debug.sh
```



## 编译(release版)

```bash
cd madmonitor2
./build.sh
```



## 安装

复制/usr/local/lib/madmonitor2下对应的扩展库，以及madmonitor2主程序到目标机器。

```bash
sudo cp madmonitor2 /usr/local/bin/
sudo chown -R monitoruser:monitoruser /usr/local/bin/madmonitor2
```



#### 第一次运行

```bash
madmonitor2 -d 4
```



会生成/services/monitor2_deal/conf/madmonitor2.ini文件

```json
{
    "ServerName": "xiaomi-laptop",
    "ProcLife": "3600",
    "Sleep": "10",
    "SendPort": "6001",
    "SendHosts": "47.111.175.126",
    "EvictInterval": "6000",
    "DedupInterval": "300"
}
```

其中SeverName为主机名，需要配置的还有SendHost和SendPort，分别为监控客户端要发送数据到的数据收集器的主机和对应的端口。






## 版本说明

*Copyright 2019  The "madmonitor2" Authors*. See file AUTHORS and CONTRIBUTORS.

* * *
*Developed by [evoex123@gmail.com]*



