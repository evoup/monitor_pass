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
import (
	"madmonitor2/inc"
	"encoding/json"
	"bufio"
	"fmt"
)

// Configuration values to use as defaults in the code
func GetDefaults() map[string]string {
	hostname, _ := os.Hostname()
	var defaultConf = map[string]string{
		"server_name":    hostname,
		"proc_life":      inc.PROC_LIFE,
		"sleep":          inc.SLEEP,
		"send_port":      inc.SEND_PORT,
		"upload_url":     "",
		"upload_host":    "172.18.0.30",
		"upload_port":    "80",
		"upload_version": "monitor_server2r1_1",
		"upload_suffix":  "m1",
		"send_hosts":     "172.18.0.1,172.18.0.2",
	}
	return defaultConf
}

func WriteConf(confFileIn string) error {
	conf := GetDefaults()
	file, err := os.Create(confFileIn)
	if err != nil {
		return err
	}
	defer file.Close()
	w := bufio.NewWriter(file)
	for k, v := range conf {
		fmt.Fprintln(w, k+"="+v)
	}
	return w.Flush()
}

// Configuration values to use as defaults in the code
func GetDefaultsJson() inc.DefaultConf {
	hostname, _ := os.Hostname()
	jsonBlob := []byte("{\"servername\":\"" + hostname + "\",\"proclife\":\"" + inc.PROC_LIFE + "\",\"sleep\":\"" +
		inc.SLEEP + "\",\"sendport\":\"" + inc.SEND_PORT + "\",\"sendhosts\":\"" + inc.SEND_HOSTS + "\"}")
	defaultConf := inc.DefaultConf{}
	err := json.Unmarshal(jsonBlob, &defaultConf)
	if err != nil {
		fmt.Println("err:" + err.Error())
		os.Exit(0)
	}
	return defaultConf
}

func WriteDefaultsJson(confFileIn string) error {
	defaultsJson := GetDefaultsJson()
	file, _ := os.Create(confFileIn)
	enc := json.NewEncoder(file)
	enc.SetIndent("", "    ")
	err := enc.Encode(&defaultsJson)
	if err != nil {
		return err
	}
	return nil
}