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
	"madmonitor2/inc"
	"os"
	"fmt"
)

func Register_collector(name string, interval int, filename string, generation int) inc.Collector {
	file, err := os.Stat(filename)
	if err != nil {
		fmt.Println(err)
		os.Exit(1)
	}
	mtime := int(file.ModTime().Unix())
	lastspawn := 0
	collector := inc.Collector{name, interval, filename, mtime, lastspawn, false, generation}
	return collector
}
