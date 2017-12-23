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

package config

import "os"
import "madmonitor2/inc"



func GetDefaults() map[string]string{
	hostname, _ := os.Hostname()
	var defaultConf = map[string]string{
		"server_name": hostname,
		"proc_life" : inc.PROC_LIFE,
		"sleep" : inc.SLEEP,
		"send_port"   : inc.SEND_PORT,
		"upload_url" : "",
		"upload_host" : "172.18.0.30",
		"upload_port"  : "80",
		"upload_version"  : "monitor_server2r1_1",
		"upload_suffix"  : "m1",
		"send_host"  : "172.18.0.1",

	}
	return defaultConf
}
