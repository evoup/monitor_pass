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

package common

import (
	"log"
	"log/syslog"
	"madmonitor2/inc"
)


// syslog init
func GetLogger() *log.Logger {
	Log, err := syslog.NewLogger(syslog.LOG_DEBUG, log.Lmicroseconds)
	if err != nil {
		log.Fatal(err)
	}
	return Log
}

// use syslog log item
func Log(l *log.Logger, s string, debug_level_orig int, debug_level_input int) {
	if (debug_level_orig<=debug_level_input) {
		l.Print("[" + s + "][" + inc.LOG_SUFFIX + "." + inc.CLIENT_VERSION + "]")
	}
}
