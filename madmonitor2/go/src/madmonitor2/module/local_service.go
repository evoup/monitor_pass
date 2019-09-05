package module

import (
    "encoding/json"
    "fmt"
    "madmonitor2/inc"
    "net"
    "net/rpc"
    "net/rpc/jsonrpc"
    "strings"
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

//
type MonitorItemConfig struct {
    monitorItemConfig string
}

// 服务端传递过来的消息，不需要的小写
type MonitorItem struct {
   Id         int
   Name       string
   dateType   int
   desc       string
   error      string
   Key        string
   Multiplier int
   hostId     int
   templateId int
   Delta      int
}

func (this *MonitorItemConfig) Update(configStr string, r *int) error {
    // 服务端传来的是对象数组
    keys := make([]MonitorItem, 0)
    err := json.Unmarshal([]byte(configStr), &keys)
    if err != nil {
        fmt.Println(err)
    }
    fmt.Printf("items: %v", &keys)
    this.monitorItemConfig = configStr
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
        fmt.Println("rpc client incoming...")
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
