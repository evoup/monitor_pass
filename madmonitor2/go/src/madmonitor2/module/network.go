/*
 * This file is part of madmonitor2.
 * Copyright (c) 2018. Author: yinjia evoex123@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  This program is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Lesser
 * General Public License for more details.  You should have received a copy
 * of the GNU Lesser General Public License along with this program.  If not,
 * see <http://www.gnu.org/licenses/>.
 */
package module

import (
	"net"
	"time"
	"madmonitor2/inc"
	"fmt"
	"os"
	"strings"
)

type ServerConn struct {
	conn    *net.TCPConn
	host    string
	timeout time.Duration
}


func MyDial() (string,bool, *ServerConn) {
	sendHost, _ := inc.ConfObject.GetString("SendHosts")
	sendHosts := strings.Split(sendHost, ",")
	sendPort, _ := inc.ConfObject.GetString("SendPort")
	cName, _ := inc.ConfObject.GetString("ServerName")
	foundServer := false
	sc := &ServerConn{conn:nil,host:"",timeout:0}
	for i := range sendHosts {
		addr, err := net.ResolveTCPAddr("tcp", sendHosts[i]+":"+sendPort)
		conn, err := net.DialTCP("tcp", nil, addr)
		if err != nil {
			fmt.Println(err.Error())
			if i == len(sendHosts)-1 {
				fmt.Println("no collector server found! good bye.")
				os.Exit(0)
			}
			fmt.Println("switch to another collector server")
			continue
		} else {
			foundServer = true
			sc.conn=conn
			sc.host=addr.IP.String()
			break
		}
	}
	return cName, foundServer, sc
}

//func DialTimeout(addr string, timeout time.Duration) (*ServerConn, error) {
//	tconn, err := net.DialTimeout("tcp", addr, timeout)
//	if err != nil {
//		return nil, err
//	}
//
//	// Use the resolved IP address in case addr contains a domain name
//	// If we use the domain name, we might not resolve to the same IP.
//	remoteAddr := tconn.RemoteAddr().(*net.TCPAddr)
//
//	conn := textproto.NewConn(tconn)
//
//	c := &ServerConn{
//		conn:    conn,
//		host:    remoteAddr.IP.String(),
//		timeout: timeout,
//	}
//
//	return c, nil
//}

func (c *ServerConn) Quit() error {
	return c.conn.Close()
}
