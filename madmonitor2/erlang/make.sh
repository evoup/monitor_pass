#!/bin/sh
for ERL in "/usr/bin/erl" "/usr/local/bin/erl" 
do 
    if [ -x $ERL ] 
    then
        break
    fi
done 

if [ ! -x $ERL ]
then 
    echo "Can't to find ERL. Please verify erlang install path." 
    exit 
fi
echo "[erlang dir:"$ERL"]"


for ERL_LIB in "/usr/lib/erlang/" "/usr/local/lib/erlang/" 
do 
    if [ -x $ERL_LIB ] 
    then
        break
    fi
done 

if [ ! -x $ERL_LIB ]
then 
    echo "Can't to find ERL_LIB. Please verify erlang lib path." 
    exit 
fi
echo "[erlang lib dir:$ERL_LIB]"


cat $ERL_LIB/releases/RELEASES
ERL_VER=`cat $ERL_LIB/releases/RELEASES | grep "\[{release,.*" | awk -F '"' '{print $6}'`

OS_SPEC=`uname -s | awk '{print tolower($0)}'`
OS_TYPE=`uname -m`
OS_VER=`uname -r | awk -F '-' '{print $1}'`
GCC_MAINVER=`gcc -dumpversion | cut -f1 -d.`
APP_PREFIX="./apps/madmonitor2"
#echo "[compiling erlang_src...]"
#$ERL -make
echo "[compiling addition toolkit...]"
cd ./apps/madmonitor2/files/sdc
$ERL -make
cd -
echo "[OS_SPEC:$OS_SPEC][OS_TYPE:$OS_TYPE][OS_VER:$OS_VER][GCC_MAINVER:$GCC_MAINVER]"
echo "[compiling c_src...]"
echo "building getload.so"
gcc -Wall -o $APP_PREFIX/priv/getload.so -fpic -shared -I $ERL_LIB/erts-$ERL_VER/include/ $APP_PREFIX/files/getload.c
echo "building getdisk_freebsd.so"
gcc -Wall -o $APP_PREFIX/priv/getdisk_freebsd.so -fpic -shared -I $ERL_LIB/erts-$ERL_VER/include/ $APP_PREFIX/files/getdisk_freebsd.c
echo "building uptime.so"
gcc -Wall -o $APP_PREFIX/priv/uptime.so -fpic -shared -I $ERL_LIB/erts-$ERL_VER/include/ $APP_PREFIX/files/uptime.c
echo "building free_freebsd.so"
gcc -Wall -o $APP_PREFIX/priv/free_freebsd.so -fpic -shared -I $ERL_LIB/erts-$ERL_VER/include/ $APP_PREFIX/files/free_freebsd.c
#echo "building pstat_freebsd.so"
#gcc -Wall -o $APP_PREFIX/priv/pstat_freebsd.so -fpic -shared -lkvm -lutil -I $ERL_LIB/erts-$ERL_VER/include/ $APP_PREFIX/files/pstat_freebsd.c
echo "builiding ifstat.so"
gcc -Wall -o $APP_PREFIX/priv/ifstat.so -fpic -shared -I $ERL_LIB/erts-$ERL_VER/include/ $APP_PREFIX/files/ifstat.c

if [ $OS_SPEC = "freebsd" ]
then
    case $OS_VER in
        "6.0"|"6.1"|"6.2"|"6.3"|"6.4" )
            echo "building pstat_freebsd.so for" $OS_SPEC $OS_VER
            gcc -Wall -o $APP_PREFIX/priv/pstat_freebsd.so -fpic -shared -lkvm -lutil -I $ERL_LIB/erts-$ERL_VER/include/ $APP_PREFIX/files/pstat_freebsd6.c
            ;;
        "7.0"|"7.1"|"7.2"|"7.3"|"7.4" )
            echo "building pstat_freebsd.so for" $OS_SPEC $OS_VER
            gcc -Wall -o $APP_PREFIX/priv/pstat_freebsd.so -fpic -shared -lkvm -lutil -I $ERL_LIB/erts-$ERL_VER/include/ $APP_PREFIX/files/pstat_freebsd7.c
            ;;
        "8.0"|"8.1"|"8.2"|"8.3"|"8.4"|"9.0"|"9.1" )
            echo "building pstat_freebsd.so for" $OS_SPEC $OS_VER
            gcc -Wall -o $APP_PREFIX/priv/pstat_freebsd.so -fpic -shared -lkvm -lutil -I $ERL_LIB/erts-$ERL_VER/include/ $APP_PREFIX/files/pstat_freebsd.c
            ;;
    esac
fi

if [ $OS_SPEC = "freebsd" ]
then
    case $OS_VER in
        "6.0"|"6.1"|"6.2"|"6.3"|"6.4"|"8.0"|"8.1"|"8.2"|"8.3"|"8.4"|"9.0"|"9.1" )
            echo "$OS_SPEC $OS_VER $OS_TYPE in compiling..."
            if [ $GCC_MAINVER -eq "4" ]
            then
                #build netstat.so
                echo "building netstat.so"
                gcc -O2 -pipe -fno-strict-aliasing -DIPSEC -DSCTP -DINET6 -DNETGRAPH -DIPX -std=gnu99 -fstack-protector -Wsystem-headers -Wall -Wno-format-y2k -W -Wno-unused-parameter -Wstrict-prototypes -Wmissing-prototypes -Wpointer-arith -Wno-uninitialized -Wno-pointer-sign -o $APP_PREFIX/priv/netstat.so -fpic -shared -I /usr/local/lib/erlang/erts-$ERL_VER/include/ $APP_PREFIX/files/netstat/freebsd/$OS_VER/$OS_TYPE/*.c -lkvm -lmemstat -lutil -lnetgraph -lipx
            fi
            if [ $GCC_MAINVER -eq "3" ]
            then
                gcc -O2 -fno-strict-aliasing -pipe  -DIPSEC -DINET6 -Wsystem-headers -Wall -Wno-format-y2k -Wno-uninitialized  -o $APP_PREFIX/priv/netstat.so -fpic -shared -I /usr/local/lib/erlang/erts-$ERL_VER/include/ $APP_PREFIX/files/netstat/freebsd/$OS_VER/$OS_TYPE/*.c -lkvm -lipx -lmemstat -lnetgraph -lutil
            fi
            ;;
        "7.0"|"7.1"|"7.2"|"7.3"|"7.4" )
            echo "$OS_SPEC $OS_VER $OS_TYPE in compiling..."
            gcc -O2 -fno-strict-aliasing -pipe  -DIPSEC -DSCTP -DINET6 -DIPX -Wsystem-headers -Wall -Wno-format-y2k -W -Wno-unused-parameter -Wstrict-prototypes -Wmissing-prototypes -Wpointer-arith -Wno-uninitialized -Wno-pointer-sign  -o $APP_PREFIX/priv/netstat.so  -fpic -shared -I /usr/local/lib/erlang/erts-$ERL_VER/include/ $APP_PREFIX/files/netstat/freebsd/$OS_VER/$OS_TYPE/*.c -lkvm -lmemstat -lnetgraph -lutil -lipx
            ;;
    esac
fi
echo "[all done]"
