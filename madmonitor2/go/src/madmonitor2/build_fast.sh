#!/bin/bash
go build madmonitor2.go
#go build --buildmode=plugin -o /usr/local/lib/madmonitor2/sysload.so collectors/sysload.go                                    
#go build --buildmode=plugin -o /usr/local/lib/madmonitor2/ifstat.so collectors/ifstat.go                                      
#go build --buildmode=plugin -o /usr/local/lib/madmonitor2/dfstat.so collectors/dfstat.go                                      
#go build --buildmode=plugin -o /usr/local/lib/madmonitor2/procstats.so collectors/procstats.go

TEMP_BUILD_DIR=/tmp/madmonitor2_build/
mkdir ${TEMP_BUILD_DIR} || true
export GOPATH="/home/evoup/go"
export GOBIN="/usr/local/go/bin"                                                                                        
export PATH=$GOBIN:$PATH
find collectors/ -maxdepth 1 -mindepth 1 -type f -name "*.go" | cut -c12- | parallel -I% --max-args 1 go build --buildmode=plugin -o $TEMP_BUILD_DIR%.so collectors/%

MY_SO=$(find collectors/ -maxdepth 1 -mindepth 1 -type f -name "*.go" | cut -c12-)
echo ${MY_SO}

 
for word in ${MY_SO}; do
    x=$(basename ${word} .go)
    #something like dfstat
    mv ${TEMP_BUILD_DIR}${x}.go.so ${TEMP_BUILD_DIR}${x}.so || true
    cp ${TEMP_BUILD_DIR}${x}.so /usr/local/lib/madmonitor2/
done
