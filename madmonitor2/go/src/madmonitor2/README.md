监控代理2.0 golang
=======

监控2.0说明README.md文档

### 安装
    sudo apt install sysstat
    golang version go1.9.2
    go get github.com/crgimenes/goconfig/config
	cd bin
	go build madmonitor2
    
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

### 开发说明
调试环境采用build_debug.sh编译出插件，当前首先要创建好插件目录/usr/local/lib/madmonitor2/，并且保证目录有写入权限。


### 版本说明

*Copyright 2013  The "madmonitor2" Authors*. See file AUTHORS and CONTRIBUTORS.


* * *
*Developed by evoup(evoex123@gmail.com)*



