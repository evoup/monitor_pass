#!/bin/bash
#This script is used to display an inputbox
OUTPUT="/tmp/input_madmonitor2.txt"
#create empty file
>$OUTPUT
#check ip validation
function ipDone(){
regex="\b(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[1-9])\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[1-9])\b"
#local n=${@-"anonymous IP"}
local n=$@
ckStep2=`echo $n | egrep $regex | wc -l`
if [ $ckStep2 -eq 0 ]
then
    dialog --title "警告" --backtitle "亿动广告传媒有限公司 系统架构部" --clear --msgbox " 抱歉，您输入的$n不是一个有效的IP地址，请重新输入!" 10 41
else
    #ip ok, send pass
    PWDNOW=`pwd`
    bash $PWDNOW/nodeconn2.bash $n
fi
}
#Add a trap that will remove $OUTPUT
trap "rm $OUTPUT; exit" SIGHUP SIGINT SIGTERM
#Show an inputbox
dialog --title "输入要远程登录的erlang节点IP" \
--backtitle "亿动广告传媒有限公司 系统架构部" \
--inputbox "IP地址" 8 60 2>$OUTPUT
#Get respose
respose=$?
#Get data stored in $OUTPUT using input redirection
name=$(<$OUTPUT)
#Make a decsion
case $respose in
0) ipDone ${name};;
1) echo "Cancel pressed";;
255) echo "[ESC] key pressed";;
esac
#Remove $OUTPUT file
rm $OUTPUT
