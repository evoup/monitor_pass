#!/bin/bash
#This script is used to display an inputbox
OUTPUT="/tmp/inputpass_madmonitor2.txt"
IP="$1"
#create empty file
>$OUTPUT
#check ip validation
function passDone(){
local n="$1"
CPWD=`pwd`
ERTSDIR=`ls ../ | grep erts`
RES=`$CPWD/../$ERTSDIR/bin/erl -name test -noshell -pa ./ebin -s connnode run -s init stop -flagIp $IP -flagPass $n`
t=$RES
if [ $t -eq 1 ]
then
    #修改attch启动脚本中调用vm.args为vm.args.attach
    # Parse out release and erts info
    START_ERL=`cat $CPWD/../releases/start_erl.data`
    ERTS_VSN=${START_ERL% *}
    APP_VSN=${START_ERL#* }
    if [ -f $CPWD/../bin/madmonitor2.attach.orig ]
    then
        cp -f $CPWD/../bin/madmonitor2.attach.orig $CPWD/../bin/madmonitor2.attach
    fi
    sed  "s/vm.args/vm.args.attach/g" $CPWD/../bin/madmonitor2.attach > $CPWD/../bin/madmonitor2.attach.now
    chmod +x $CPWD/../bin/madmonitor2.attach.now
    cp -f $CPWD/../bin/madmonitor2.attach $CPWD/../bin/madmonitor2.attach.orig
    cp -f $CPWD/../bin/madmonitor2.attach.now $CPWD/../bin/madmonitor2.attach
    sed 's/-name madmonitor2@.*/-name madmonitor2@'"$IP"'/g' $CPWD/../releases/$APP_VSN/vm.args > $CPWD/../releases/$APP_VSN/vm.args.now
    mv $CPWD/../releases/$APP_VSN/vm.args.now $CPWD/../releases/$APP_VSN/vm.args
    sed 's/-setcookie .*/-setcookie '"$n"'/g' $CPWD/../releases/$APP_VSN/vm.args > $CPWD/../releases/$APP_VSN/vm.args.attach
    dialog --title "警告" --backtitle "亿动广告传媒有限公司 系统架构部" --clear --msgbox "认证OK，进入节点远程控制台" 10 41
    $CPWD/../bin/madmonitor2.attach remote_console
    dialog --title "警告" --backtitle "亿动广告传媒有限公司 系统架构部" --clear --msgbox "操作完毕，退出节点远程控制台" 10 41
else
    dialog --title "警告" --backtitle "亿动广告传媒有限公司 系统架构部" --clear --msgbox "认证失败，口令有误!" 10 41
fi
}
#Add a trap that will remove $OUTPUT
trap "rm $OUTPUT; exit" SIGHUP SIGINT SIGTERM
#Show an inputbox
dialog --title "输入要远程登录的erlang节点的口令,口令的算法约定可以参见文档" \
--backtitle "亿动广告传媒有限公司 系统架构部" \
--inputbox "远程节点口令" 8 80 2>$OUTPUT
#Get respose
respose=$?
#Get data stored in $OUTPUT using input redirection
name=$(<$OUTPUT)
#Make a decsion
case $respose in
0) passDone ${name} $IP;;
1) echo "Cancel pressed";;
255) echo "[ESC] key pressed";;
esac
#Remove $OUTPUT file
rm $OUTPUT
