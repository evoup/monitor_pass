package module

import (
    "fmt"
    "net/rpc"
    "net"
    "net/rpc/jsonrpc"
    "strings"
    "madmonitor2/inc"
)

// 本地jsonrpc服务结构
// sample
type Counter struct {
    Sum               int
    monitorItemConfig string
}

func (this *Counter) Add(i int, r *int) error {
    this.Sum += i
    *r = this.Sum
    fmt.Printf("i: %v", i)
    return nil
}

type MonitorItemConfig struct {
    monitorItemConfig string
}

func (this *MonitorItemConfig) Update(configStr string, r *int) error {
    this.monitorItemConfig = configStr
    fmt.Printf("configStr: %vpong", configStr)
    *r = inc.RPC_SERVER_OK
    return nil
}


//NewUpdateConfigServer jsonRpc服务，接收来自数据收集器发送的监控项配置，并写入缓存，供collector获取，按照系统的设置工作
func NewUpdateConfigServer() {
    // sample
    //rpc.Register(new(Counter))
    rpc.Register(new(MonitorItemConfig))

    l, err := net.Listen("tcp", ":8338")
    if err != nil {
        fmt.Printf("Listener tcp err: %s", err)
        return
    }
    fmt.Println("open local update config service")
    for {
        fmt.Println("wating...")
        conn, err := l.Accept()
        // TODO 需要限制只能对一个ip只允许一个连接
        //hostPort is 127.0.0.1:37302
        hostPort := conn.RemoteAddr().String()
        arr := strings.Split(hostPort, ":")
        fmt.Println(arr)
        if err != nil {
            fmt.Sprintf("accept connection err: %s\n", conn)
        }
        // 会阻塞直到client hang up
        go jsonrpc.ServeConn(conn)
    }

}
