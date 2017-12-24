/*
 * This file is part of madmonitor2.
 * Copyright (c) 2017. Author: yinjia evoex123@gmail.com
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

package inc

const (
    LOG_SUFFIX       = "madmonitor2"
    CLIENT_VERSION   = "2.0.0.1"
    PROC_ROOT        = "/services/monitor2_deal"
    CONF_FILE        = "madmonitor2.ini"
    CONF_SUBPATH     = "conf/"
    WORK_SUBPATH     = "work/"
    PROC_LIFE        = "3600"
    SLEEP            = "10"
    SEND_PORT        = "8090"
    SEND_HOSTS       = "172.18.0.1,172.18.9.3"
)

type DefaultConf struct {
    ServerName string
    ProcLife   string
    Sleep      string
    SendPort   string
    SendHosts  string
}

type Conf DefaultConf






