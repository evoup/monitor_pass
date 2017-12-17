#!/bin/bash
#This script is used to display menus on screen
#Store menu options selected by the user
INPUT=/tmp/menu_madmonitor2.sh.$$
#Store file for displaying cal and date command output
OUTPUT=/tmp/output_madmonitor2.sh.$$
#Get text editor or fall back to vi editor
vi_editor=${EDITOR-vi}
#Display output using msgbox
function display_output(){
local h=${1-10} #box height default 10
local w=${2-41} #box width default 41
local t=${3-Output} #box title
dialog --backtitle"Madhouse monitor2.0 远程终端控制台" --title "${t}"
--clear --msgbox "$($OUTPUT)" ${h} ${w}
}
#Display current system date and time
function show_date(){
echo "Today is $(date) @ $(hostname -f)." >$OUTPUT
display_output 6 60 "Date and Time"
}
#Display a calendar
function show_calendar(){
cal > $OUTPUT
display_output 13 25 "Calendar"
}
#set infinit loop
while true
do
    dialog --clear --help-button --backtitle "亿动广告传媒有限公司 系统架构部" \
        --title "[亿动服务器监控终端1.0]" \
        --menu "" 15 50 5 \
        1 "[服务器列表]" \
        2 "[Erlang节点远程控制台]" \
        3 "[操作日志]" \
        4 "[用户管理]" \
        5 "[退出]" 2>"${INPUT}"
    menuitem=$(<"${INPUT}")
    #make decsion
    PWDNOW=`pwd`
    case $menuitem in
        1) show_date;;
        2) bash $PWDNOW/nodeconn.bash;;
        3) show_calendar;;
        4) $vi_editor;;
        5) echo "Bye"; break;;
    esac
done
#if temp files found ,delete them
[ -f $OUTPUT ] && rm $OUTPUT
[ -f $INPUT ] && rm $INPUT
