package module

import (
    "encoding/json"
    "fmt"
    "github.com/patrickmn/go-cache"
    "madmonitor2/config"
    "madmonitor2/inc"
    "net"
    "net/rpc"
    "net/rpc/jsonrpc"
    "strings"
)

/*能够注册的方法必须满足指定的函数签名：

    func (t *T) MethodName(argType T1, replyType *T2) error 。

    入参有且只有两个，第一个是被调用函数的输入参数，第二个是被调用函数的输出参数。 返回值只有一个error类型。这三个参数一个都不能变。*/

// 本地json rpc服务结构，必须有，不然不能注册
type MonitorItemsConfig struct {
    json string // 这里面其实是json
}

// 客户端版本，必须有，不然不能注册
type MonitorAgentVersion struct {
    version string
}

// 更新客户端配置的rpc方法
func (this *MonitorItemsConfig) Update(configStr string, r *int) error {
    // 服务端传来的是对象数组
    keys := make([]inc.MonitorItem, 0)
    err := json.Unmarshal([]byte(configStr), &keys)
    if err != nil {
        fmt.Println(err)
        *r = inc.RPC_SERVER_FAIL
    } else {
        fmt.Printf("items: %v", &keys)
        inc.ConfigCache.Set("monitorItems", configStr, cache.NoExpiration)
        // 写入本地监控项配置文件
        err = config.WriteJson(inc.PROC_ROOT+"/"+inc.WORK_SUBPATH+inc.MONITOR_ITEMS_CONF_FILE, configStr)
        if err != nil {
            fmt.Println(err)
        }
        //foo, found := inc.ConfigCache.Get("monitorItems")
        //if found {
        //    fmt.Println(foo)
        //}
        *r = inc.RPC_SERVER_OK
    }
    return nil
}

// 获取客户端版本号的rpc方法
func (this *MonitorAgentVersion) Get(_ string, r *string) error {
    *r = inc.CLIENT_VERSION
    return nil
}

// NewUpdateConfigServer jsonRpc服务，接收来自数据收集器发送的监控项配置，并写入缓存，供collector获取，按照系统的设置工作
func NewUpdateConfigServer() {
    err := rpc.Register(new(MonitorItemsConfig))
    if err != nil {
        fmt.Printf("rpc register err: %s", err)
        return
    }
    err = rpc.Register(new(MonitorAgentVersion))
    if err != nil {
        fmt.Printf("rpc register err: %s", err)
        return
    }
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
            fmt.Printf("accept connection err: %s\n", conn)
        }
        // 会阻塞直到client hang up
        go jsonrpc.ServeConn(conn)
    }

}
