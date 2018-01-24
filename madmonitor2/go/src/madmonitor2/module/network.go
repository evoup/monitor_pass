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
	"time"
	"net"
	"net/textproto"
)



type ServerConn struct {
	conn          *textproto.Conn
	host          string
	timeout       time.Duration
}

func DialTimeout(addr string, timeout time.Duration) (*ServerConn, error) {
	tconn, err := net.DialTimeout("tcp", addr, timeout)
	if err != nil {
		return nil, err
	}

	// Use the resolved IP address in case addr contains a domain name
	// If we use the domain name, we might not resolve to the same IP.
	remoteAddr := tconn.RemoteAddr().(*net.TCPAddr)

	conn := textproto.NewConn(tconn)

	c := &ServerConn{
		conn:     conn,
		host:     remoteAddr.IP.String(),
		timeout:  timeout,
	}

	return c, nil
}

func (c *ServerConn) Quit() error {
	return c.conn.Close()
}