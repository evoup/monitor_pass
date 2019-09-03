监控代理2.0 golang
=======

监控代理2.0说明README.md文档
本项目给予opentsdb客户端tcollector改写而成

### 安装依赖
```bash
sudo apt install sysstat
```



### 编译环境准备

##### 安装go
```bash
get https://storage.googleapis.com/golang/go1.9.2.linux-amd64.tar.gz
tar xzf go1.9.2.linux-amd64.tar.gz
sudo mv go /usr/local/
mkdir ~/go/
cd !$
git clone https://gitee.com/evoup/3rd_golib.git
tar xzf 3rd_golib/src.tgz && rm -rf 3rd_golib
```

##### 设置环境变量
```bash
export PATH=$PATH:/usr/local/go/bin
export GOPATH=~/go/
```



### 操作指南

systemd重启服务设置
http://www.voidcn.com/article/p-esmremgn-btw.html

UserScripts的配置，UserScripts是用户脚本，格式为<key>:<command>，语法和zabbix完全一样
```bash
"script.crawl.store_url.percent[*],~/shell/monitor/crawl_store_url_percent.sh $1"
```
其中key为script.crawl.store_url.percent，[*]为参数，*对应$1-$9共九个参数

### 开发说明

调试环境采用build_debug.sh编译出插件，当前首先要创建好插件目录/usr/local/lib/madmonitor2/，并且保证目录有写入权限。

开发插件的话，插件的代码里有main函数，可以直接调试， 插件模式go不能加载断点需要注意。

* * *
*Developed by evoup(evoex123@gmail.com)*

