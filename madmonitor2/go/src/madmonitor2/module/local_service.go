package module

import (
    "fmt"
    "net/rpc"
    "net"
    "net/rpc/jsonrpc"
    "strings"
)
// 本地jsonrpc服务结构
type Counter struct {
    Sum int
}
func (this *Counter) Add(i int, r *int) error {
    this.Sum += i
    *r = this.Sum
    fmt.Printf("i: %v", i)
    return nil
}

func NewUpdateConfigServer() {

    rpc.Register(new(Counter))

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
