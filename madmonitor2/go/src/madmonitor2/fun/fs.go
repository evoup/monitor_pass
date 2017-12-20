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
	bool_existed, _ := FileExists(filePath)
	if bool_existed {
		Log(hLog, "common.fs.FilePutContent][pid file existed",1,4)
	} else {
		Log(hLog, "common.fs.FilePutContent][pid file doesn`t existed,maybe first run",1,4)
		return ""
	}
	fin, err := os.Open(filePath)
	defer fin.Close()
	if err != nil {
		Log(hLog, "common.fs.FilePutContent][file path:"+filePath+"][open err",1,4)
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
	Log(hLog, "common.fs.FilePutContent][pid number is:"+retStr,1,4)
	return retStr
}

func FilePutContent(fileName string, data string) bool {
	hLog := LogInit()
	Log(hLog, "common.fs.FilePutContent][fileName:"+fileName,1,4)
	fout, err := os.Create(fileName)
	defer fout.Close()
	if err != nil {
		Log(hLog, "common.fs.FilePutContent][err:"+err.Error(),1,4)
		return false
	}
	fout.WriteString(data)
	return true
}
