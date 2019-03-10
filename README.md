Monitoring System2.0 [![Build Status](https://travis-ci.org/evoup/monitor_pass.svg?branch=master)](https://travis-ci.org/evoup/monitor_pass)
-----------------------------------------------------------------------------------------------------------------------------------
### 服务器监控系统2.0

#部署方法：
   docker-compose up --build

#卸载方法：
   docker-compose down -v


#开发时：
   pycharm打开monitor_api2/项目，指定docker-compose的文件为../docker-compose.yml

#其他说明：
   为开发时docker-compose.yml修改成了逐个容器依赖的方式，实际上只要api依赖db。
