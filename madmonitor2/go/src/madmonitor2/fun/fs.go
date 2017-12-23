/**
 *Project: madmonitor2 
 *Name: fs.go
 *Auther: yinjia evoex123@gmail.com
 *Create:
 *Last Modified:
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
