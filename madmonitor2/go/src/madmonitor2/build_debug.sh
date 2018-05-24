#!/bin/sh
go build madmonitor2.go
go build -gcflags "-l -N" --buildmode=plugin -o /usr/local/lib/madmonitor2/sysload.so collectors/sysload.go
go build -gcflags "-l -N" --buildmode=plugin -o /usr/local/lib/madmonitor2/ifstat.so collectors/ifstat.go
go build -gcflags "-l -N" --buildmode=plugin -o /usr/local/lib/madmonitor2/dfstat.so collectors/dfstat.go
go build -gcflags "-l -N" --buildmode=plugin -o /usr/local/lib/madmonitor2/procstats.so collectors/procstats.go
