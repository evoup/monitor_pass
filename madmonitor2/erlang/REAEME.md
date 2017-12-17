madmonitor2 erlang
=======

监控2.0client明README.md文档

## 安装

    [前提]erlang version 5.9.1 R15B和git
    chmod +x rebar make.sh
    make clean
    make depends
    编辑./deps/log4erl/src/log4erl.app.src文件，把{vsn, "0.9.0"}修改为{vsn, ""}
    make
    make release

## 发布
    完成之后，可以把rel/madmonitor2文件复制到目标监控机器就可以了。

## 操作指南
    [基本]放到crontab
    */5 * * * * root  程序所在文件夹/madmonitor2/bin/madmonitor2 start


    [高级]进入shell
    sudo madmonitor2/bin/madmonitor2 attach
    madmonitor:run().


## 版本说明

*Copyright 2013  The "madmonitor2" Authors*. See file AUTHORS and CONTRIBUTORS.


* * *
*Developed by [Madhouse architech]*


~

