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
	"os"
)
//Get file content
func FileGetContent(filePath string) string {
	hLog := LogInit()
	bool_existed := FileExists(filePath)
	if !bool_existed {
		return ""
	}
	fin, err := os.Open(filePath)
	defer fin.Close()
	if err != nil {
		Log(hLog, "common.fs.FilePutContent][file path:"+filePath+"][open err",1, Debug_level)
		os.Exit(0)
	}
	buf := make([]byte, 1024)
	retStr := ""
	for {
		n, _ := fin.Read(buf)
		if 0 == n {
			break
		}
		retStr += string(buf[:n])
	}
	return retStr
}

func FilePutContent(fileName string, data string) bool {
	hLog := LogInit()
	Log(hLog, "common.fs.FilePutContent][fileName:"+fileName,1, Debug_level)
	fout, err := os.Create(fileName)
	defer fout.Close()
	if err != nil {
		Log(hLog, "common.fs.FilePutContent][err:"+err.Error(),1, Debug_level)
		return false
	}
	fout.WriteString(data)
	return true
}
