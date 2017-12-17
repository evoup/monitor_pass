-module(mm_collect).
-include("include/inc.hrl").
-include_lib("include/monitor_msg_pb.hrl").
-export([
        collect_server/0
    ]).

%%--------------------------------------------------------------------
%% @doc collect server 
%% @end
%%--------------------------------------------------------------------
collect_server() ->
    fun_common:log("[mm_collect][collect_server][start fetch]",info),
    Base=fun_common:get("base"),
    F0 = fun(BaseItems,_)->
            {BaseItem,TmpBaseItemValue}=BaseItems,
            BaseItemValue=re:replace(TmpBaseItemValue,"\"","",[global,{return,list}]),
            case BaseItem of
                "server_name" ->
                    ets:insert(?basetb,{host_name,BaseItemValue}),
                    fun_common:log(lists:concat(["[mm_collect][base][BaseItem:",BaseItem,"][BaseItemValue:",BaseItemValue,"]"]),info);
                "upload_host" ->
                    ets:insert(?basetb,{upload_host,BaseItemValue}),
                    fun_common:log(lists:concat(["[mm_collect][base][BaseItem:",BaseItem,"][BaseItemValue:",BaseItemValue,"]"]),info);
                "upload_port" ->
                    ets:insert(?basetb,{upload_port,BaseItemValue}),
                    fun_common:log(lists:concat(["[mm_collect][base][BaseItem:",BaseItem,"][BaseItemValue:",BaseItemValue,"]"]),info);
                _ ->
                    fun_common:log(lists:concat(["[mm_collect][base][BaseItem:",BaseItem,"][BaseItemValue:",BaseItemValue,"]"]),info)
            end
    end,
    lists:foldl(F0, 0, Base),
    Uname=string:sub_word(rpc:call(node(),os,cmd,["uname -spr"]),1,$\n),
    fun_common:log("[mm_collect][collect_server][uname:~p]",Uname,info),
    Load=getload:getloadavg_ex(),
    fun_common:log("[mm_collect][collect_server][load:~p]",Load,info),
    %% get *MOUNTPOINT|TOTALSIZE|DISKFREE|USED|AVAIL|TOTALNODES|IUSED|IFREE
    OsType=fun_common:osType(),
    case OsType of
        freebsd ->
            Disk=getdisk_freebsd:getdisk_freebsd_ex(),
            FreeStr=free_freebsd:free_freebsd_ex(),
            Uptime=uptime:uptime_ex(),
            PstatStr=pstat_freebsd:get_pstat_ex(),
            IfstatStr=ifstat:ifstat_ex(),
            fun_common:log("[mm_collect][collect_server][freebsd][disk:~p]",Disk,info),
            fun_common:log("[mm_collect][collect_server][freebsd][uptime:~p]",Uptime,info),
            fun_common:log("[mm_collect][collect_server][freebsd][free:~p]",FreeStr,info),
            fun_common:log("[mm_collect][collect_server][freebsd][pstat:~p]",PstatStr,info),
            fun_common:log("[mm_collect][collect_server][freebsd][ifstat:~p]",IfstatStr,info),
            %流量信息
            F_flow = fun(FlowStr,_)->
                    fun_common:log("[mm_collect][collect_server][freebsd][FlowStr:~p]",FlowStr,info),
                    FlowInfo=re:split(FlowStr,"\\|",[{return,list}]),
                    fun_common:log("[FlowStr:~p]",FlowInfo,info),
                    case  erlang:size(list_to_tuple(FlowInfo)) of
                        4->
                            [Flow_interface,Flow_in,Flow_out,Flow_catch_time]=FlowInfo,
                            fun_common:log("[mm_collect][collect_server][freebsd]~p","[Flow_interface:"++
                                Flow_interface++"][Flow_in:"++Flow_in++"][Flow_out:"++Flow_out++
                                "][Flow_catch_time:"++Flow_catch_time++"]",info),
                            IfStRecord=ets:match_object(?ifstattb,'_'),
                            fun_common:log("[mm_collect][collect_server][freebsd][IfStRecord:~p]",IfStRecord,info),
                            F_ifst_record = fun(Ifstat_rec,_)->
                                    fun_common:log("[mm_collect][collect_server][freebsd][Ifstat_rec:~p]",Ifstat_rec,info),
                                    {ifStat,Orig_Flow_interface,Orig_Flow_in,Orig_Flow_out,Orig_Flow_catch_time}=Ifstat_rec,
                                    fun_common:log("[mm_collect][collect_server][freebsd]~p","[Orig_Flow_interface:"
                                        ++Orig_Flow_interface++"][Orig_Flow_in:"++Orig_Flow_in++
                                        "][Orig_Flow_out:"++Orig_Flow_out++"][Orig_Flow_catch_time:"++
                                        Orig_Flow_catch_time++"]",info),
                                    case Flow_interface of
                                        Orig_Flow_interface ->
                                            fun_common:log("[mm_collect][collect_server][freebsd][hit interface:~p]",Flow_interface,info),
                                            fun_common:log("[mm_collect][collect_server][freebsd][Orig_Flow_catch_time:~p]",Orig_Flow_catch_time,info),
                                            fun_common:log("[mm_collect][collect_server][freebsd][Flow_catch_time:~p]",Flow_catch_time,info),
                                            case Orig_Flow_catch_time=<Flow_catch_time of
                                                true ->
                                                    fun_common:log("[mm_collect][collect_server][freebsd][hit interface:~p passed time]",Flow_interface,info),
                                                    fun_common:log("[mm_collect][collect_server][freebsd][Flow_in:~p]",Flow_in,info),
                                                    fun_common:log("[mm_collect][collect_server][freebsd][Orig_Flow_in:~p]",Orig_Flow_in,info),
                                                    fun_common:log("[mm_collect][collect_server][freebsd][Flow_catch_time:~p]",Flow_catch_time,info),
                                                    fun_common:log("[mm_collect][collect_server][freebsd][Orig_Flow_catch_time:~p]",Orig_Flow_catch_time,info),
                                                    %calulate if in and if out
                                                    If_in=(list_to_integer(Flow_in)-list_to_integer(Orig_Flow_in))/(list_to_integer(Flow_catch_time)-list_to_integer(Orig_Flow_catch_time)),
                                                    If_out=(list_to_integer(Flow_out)-list_to_integer(Orig_Flow_out))/(list_to_integer(Flow_catch_time)-list_to_integer(Orig_Flow_catch_time)),
                                                    fun_common:log("[mm_collect][collect_server][freebsd][If_in:~p]",If_in,info),
                                                    fun_common:log("[mm_collect][collect_server][freebsd][If_out:~p]",If_out,info),
                                                    IfFlowInfo=#network_interface{
                                                        ifname=Flow_interface,
                                                        ifin=mochinum:digits(If_in),
                                                        ifout=mochinum:digits(If_out)
                                                    },
                                                    ets:insert(?ifflowtb,IfFlowInfo);
                                                false ->
                                                    fun_common:log("[mm_collect][collect_server][freebsd][hit interface:~p hasn`t passed time]",Flow_interface,info)
                                            end;
                                        _ ->
                                            fun_common:log("[mm_collect][collect_server][freebsd][skip interface]",info)
                                    end
                            end,
                            lists:foldl(F_ifst_record,0,IfStRecord),
                            IfStatInfo=#ifStat{
                                flow_interface=Flow_interface,
                                flow_in=Flow_in,
                                flow_out=Flow_out,
                                flow_catch_time=Flow_catch_time
                            },
                            ets:insert(?ifstattb,IfStatInfo);
                        _->
                            fun_common:log("[other]",info)
                    end
            end,
            lists:foldl(F_flow, 0, re:split(IfstatStr,"#",[{return,list}])),
            %SWAP信息
            [UsedSwap,TotalSwap]=re:split(PstatStr,"\\|",[{return,list}]),
            fun_common:log("[mm_collect][collect_server][freebsd][pstat]~p","[UsedSwap:"++UsedSwap++
                "][TotalSwap:"++TotalSwap++"]",info),
            %内存信息,C扩展导出的memtotal不正确，重新计算
            [_,MemActive,MemFree,MemInactive,MemWire,MemCached]=re:split(FreeStr, "\\|", [{return, list}]),
            case re:split(os:cmd("sysctl hw.physmem")," ",[{return,list}]) of
                ["hw.physmem:",Mem_total]->
                    MemTotal=string:strip(Mem_total,both,$\n);
                _ ->
                    fun_common:log("[mm_collect][gather mem_total error]",warn),
                    MemTotal="0"
            end,
            fun_common:log("[mm_collect][collect_server][freebsd][memstr:~p]","[MemTotal:"++MemTotal++"][MemActive:"
                ++MemActive++"][MemFree:"++MemFree++"][MemInactive:"++MemInactive++"][MemWire:"++MemWire++"][MemCached:"
                ++MemCached++"]",info),
            %组成*MOUNTPOINT|TOTALSIZE|DISKFREE|USED|AVAIL|TOTALINODE|IUSED|IFREE的字符串
            Disks=string:tokens(Disk,"*"),
            F1 = fun(DiskStr,_)->
                    fun_common:log("[mm_collect][collect_server][freebsd][diskstr:~p]",DiskStr,info),
                    [MountPt,TotalSz,DiskFr,Used,Avail,TotalINode,IUsed,IFree]=re:split(DiskStr, "\\|", [{return, list}]),
                    fun_common:log("[mm_collect][collect_server][freebsd][diskstr2:~p]","[MountPt:"++MountPt++"][TotalSz:"
                        ++TotalSz++"][DiskFr:"++DiskFr++"][Used:"++Used++"][Avail:"++Avail++"][TotalINode:"++TotalINode
                        ++"][IUsed:"++IUsed++"][IFree:"++IFree++"]",info),
                    %caculate disk usage & inode usage
                    case (list_to_integer(Used)+list_to_integer(Avail)) of 
                        0 ->
                            DiskUsage=0;
                        _ ->
                            DiskUsage=list_to_integer(Used)/(list_to_integer(Used)+list_to_integer(Avail))
                    end,
                    Usg= mochinum:digits(fun_common:float(DiskUsage*100,2))++"%",
                    fun_common:log("[mm_collect][collect_server][freebsd][disk partition capacity:~p]",Usg,info),
                    case (list_to_integer(IUsed)+list_to_integer(IFree)) of
                        0 ->
                            DiskInodeUsage=0;
                        _ ->
                            DiskInodeUsage=list_to_integer(IUsed)/(list_to_integer(IUsed)+list_to_integer(IFree))
                    end,
                    InodeUsg= mochinum:digits(fun_common:float(DiskInodeUsage*100,2))++"%",
                    fun_common:log("[mm_collect][collect_server][freebsd][disk partition inode usage:~p]",InodeUsg,info),
                    DiskPartitionX=#disk_partition{
                        mounted=MountPt,
                        capacity=round(fun_common:float(DiskUsage*100,2)),
                        iused=round(fun_common:float(DiskInodeUsage*100,2))
                    },
                    ets:insert(?disktb,DiskPartitionX)
            end,
            lists:foldl(F1, 0, Disks);
        linux ->
            [MemTotal,MemActive,MemFree,MemInactive,MemWire,MemCached]=["","","","","",""],
            [UsedSwap,TotalSwap]=["",""],
            fun_common:log("[mm_collect][collect_server][linux][disk:]",info),
            fun_common:log("[mm_collect][collect_server][linux][uptime:]",info),
            Uptime=0
    end,
    TcpConnections=netstat:netstat_ex(),
    fun_common:log("[mm_collect][collect_server][netstat tcp connections:~p]",TcpConnections,info),
    %netstat:netstat_r(),
    %{{{process%
    PsResult=rpc:call(node(),os,cmd,["/bin/ps waxco 'state command vsz rss uid user pid ppid'"]),
    case  re:run(PsResult,"STAT\s.*COMMAND\s.*VSZ\s.*RSS\s.*UID\s.*USER\s.*PID\s.*PPID",[]) of
        {match,_} ->
            PsLines=re:split(PsResult,"STAT\s.*COMMAND\s.*VSZ\s.*RSS\s.*UID\s.*USER\s.*PID\s.*PPID\n",[{return,list}]),
            PsResult2=string:join(PsLines,""),
            PsLines2=re:split(PsResult2,"\n",[{return,list}]),
            TotalProcesses=erlang:length(PsLines2),
            case re:run(PsResult2,"\c*D.*\s*\c*\s*\d*\s*\d*\s*\d*\s*\c*\s*\d*\s*\d*\n",[global]) of
                {match,TP}->
                    ProcD=erlang:length(TP);
                nomatch ->
                    ProcD=0
            end,
            case re:run(PsResult2,"\c*I.*\s*\c*\s*\d*\s*\d*\s*\d*\s*\c*\s*\d*\s*\d*\n",[global]) of
                {match,TP1}->
                    ProcI=erlang:length(TP1);
                nomatch ->
                    ProcI=0
            end,
            case re:run(PsResult2,"\c*L.*\s*\c*\s*\d*\s*\d*\s*\d*\s*\c*\s*\d*\s*\d*\n",[global]) of
                {match,TP2}->
                    ProcL=erlang:length(TP2);
                nomatch ->
                    ProcL=0
            end,
            case re:run(PsResult2,"\c*R.*\s*\c*\s*\d*\s*\d*\s*\d*\s*\c*\s*\d*\s*\d*\n",[global]) of
                {match,TP3}->
                    ProcR=erlang:length(TP3);
                nomatch ->
                    ProcR=0
            end,
            case re:run(PsResult2,"\c*S.*\s*\c*\s*\d*\s*\d*\s*\d*\s*\c*\s*\d*\s*\d*\n",[global]) of
                {match,TP4}->
                    ProcS=erlang:length(TP4);
                nomatch ->
                    ProcS=0
            end,
            case re:run(PsResult2,"\c*T.*\s*\c*\s*\d*\s*\d*\s*\d*\s*\c*\s*\d*\s*\d*\n",[global]) of
                {match,TP5}->
                    ProcT=erlang:length(TP5);
                nomatch ->
                    ProcT=0
            end,
            case re:run(PsResult2,"\c*W.*\s*\c*\s*\d*\s*\d*\s*\d*\s*\c*\s*\d*\s*\d*\n",[global]) of
                {match,TP6}->
                    ProcW=erlang:length(TP6);
                nomatch ->
                    ProcW=0
            end,
            case re:run(PsResult2,"\c*Z.*\s*\c*\s*\d*\s*\d*\s*\d*\s*\c*\s*\d*\s*\d*\n",[global]) of
                {match,TP7}->
                    ProcZ=erlang:length(TP7);
                nomatch ->
                    ProcZ=0
            end,
            ProcStats=io_lib:format("[Total:~pD:~pI:~pL:~pR:~pS:~pT:~pW:~pZ:~p]",[TotalProcesses,ProcD,ProcI,
                    ProcL,ProcR,ProcS,ProcT,ProcW,ProcZ]),
            fun_common:log("[mm_collect][collect_server][ps cmd run ok][procstats:~p]",ProcStats,info);
        nomatch ->
            fun_common:log("[mm_collect][collect_server][ps cmd run fail]",warn),
            ProcD="0",ProcI="0",ProcL="0",ProcR="0",ProcS="0",ProcT="0",ProcW="0",ProcZ="0",TotalProcesses="0",
            erlang:exit("ps cmd run fail")
    end,
    %}}}%
    %{{{iostat & cpuinfo%
    fun_common:log("[mm_collect][collect_server][start get iostat data]",info),
    case OsType of 
        freebsd ->
            IostatResult=rpc:call(node(),os,cmd,["iostat 1 2"]),
            fun_common:log("[mm_collect][collect_server][iostat freebsd result:~p]",IostatResult,info),
            case re:run(IostatResult,".*cpu\n",[]) of 
                {match,_} ->
                    fun_common:log("[mm_collect][collect_server][iostat call ok:~p]",IostatResult,info),
                    CpuLines=re:split(IostatResult,"\n",[{return,list}]),
                    [DeviceLine,CpuHLine,_,CpuLine|_]=CpuLines, %iostat在freebsd下会保持不变，需要等待1s，取下一行
                    fun_common:log("[mm_collect][collect_server][cpuline:~p]",CpuLine,info),
                    DeviceData=lists:delete(<<>>,re:split(CpuLine,"[\s]+")), %data of KB/t tps MB/s 
                    fun_common:log("[mm_collect][collect_server][device data:~p]",DeviceData,info),
                    NewDeviceData=lists:append([[erlang:list_to_binary("dummydata")],DeviceData]), %新增一个元素用来凑齐3个直接跳过 
                    fun_common:log("[mm_collect][collect_server][new device data:~p]",NewDeviceData,info),
                    fun_common:log("[mm_collect][collect_server][deviceline:~p]",DeviceLine,info),
                    DeviceList=lists:delete(<<>>,re:split(DeviceLine,"[\s]+")),
                    fun_common:log("[mm_collect][collect_server][device list:~p]",DeviceList,info),
                    DeviceNum=erlang:length(DeviceList)-2,
                    fun_common:log("[mm_collect][collect_server][device num:~p]",DeviceNum,info),
                    FAllDevice=fun(DeviceName,TpsPosition)->
                            fun_common:log("[mm_collect][collect_server][TpsPosition:~p]",TpsPosition,info),
                            NotRealDeviceList=[<<"tty">>,<<"cpu">>],
                            case (lists:member(DeviceName,NotRealDeviceList)) of 
                                true ->
                                    fun_common:log("[mm_collect][collect_server][DeviceName:~p][isn`t device]",
                                        binary_to_list(DeviceName),info);
                                false ->
                                    fun_common:log("[mm_collect][collect_server][DeviceName:~p][is a real device]",
                                        binary_to_list(DeviceName),info),
                                    Tps=binary_to_list(lists:nth(TpsPosition,NewDeviceData)),
                                    fun_common:log("[mm_collect][collect_server][Tps:~p][is a real device]",Tps,info),
                                    %{{{%
                                    DiskIoStat=#disk_iostat{
                                        device_name=DeviceName,
                                        tps=Tps
                                    },
                                    ets:insert(?diskiostattb,DiskIoStat)
                                    %}}}%
                            end,
                            TpsPosition+3
                    end,
                    lists:foldl(FAllDevice,2,DeviceList), %偏移3个数据正好是tps 
                    CpuInfo=re:run(CpuLine,".*(\s([0-9].*)\s([0-9].*)\s([0-9].*)\s([0-9].*)\s([0-9].*))",[]),
                    fun_common:log("[mm_collect][collect_server][cpuinfo:~p]",CpuInfo,info),
                    {match,[_,_,{Us,Ue},{Ns,Ne},{Ss,Se},{Its,Ite},{Ids,Ide}|_]}=CpuInfo,
                    Dinfo=lists:concat(["[Us:",integer_to_list(Us),"][Ue:",integer_to_list(Ue),"][Ns:",
                        integer_to_list(Ns),"][Ne:",integer_to_list(Ne),"][Ss:",integer_to_list(Ss),
                        "][Se:",integer_to_list(Se),"][Its:",integer_to_list(Its),"][Ite:",
                        integer_to_list(Ite),"][Ids:",integer_to_list(Ids),"][Ide:",integer_to_list(Ide),"]"]),
                    fun_common:log("[mm_collect][collect_server][Dinfo]~p",Dinfo,info),
                    FreebsdCpu=#freebsd_cpu{
                        user=string:strip(string:substr(CpuLine,Us+1,Ue)),
                        nice=string:strip(string:substr(CpuLine,Ns+1,Ne)),
                        system=string:strip(string:substr(CpuLine,Ss+1,Se)),
                        interrupt=string:strip(string:substr(CpuLine,Its+1,Ite)),
                        idle=string:strip(string:substr(CpuLine,Ids+1,Ide))
                    },
                    fun_common:log("[mm_collect][collect_server][freebsd cpu:~p]",FreebsdCpu,info),
                    GenericCpu=#generic_cpu{freebsd_cpu=FreebsdCpu},
                    CpuHinfo=re:run(CpuHLine,"KB\/t\s*tps\s*MB\/s",[global]),
                    fun_common:log("[mm_collect][collect_server][freebsd cpu header info:~p]",CpuHinfo,info),
                    {match,Devices}=CpuHinfo,
                    TotalDevice=erlang:length(Devices),
                    fun_common:log("[mm_collect][collect_server][freebsd total device:~p]",[TotalDevice],info);
                nomatch ->
                    fun_common:log("[mm_collect][collect_server][iostat call fail]",warn),
                    GenericCpu=#generic_cpu{},
                    erlang:exit("iostat call fail")
            end;
        linux ->
            fun_common:log("[mm_collect][collect_server][iostat linux use nif so]",info),
            GenericCpu=#generic_cpu{}
    end,
    %}}}%
    %TODO change check param by conf
    Services=fun_common:get("tcp_service_table"),
    F = fun(ShowService,_)->
            {ServiceName,ServiceInfo}=ShowService,
            fun_common:log(lists:concat(["[mm_collect][tcp][ServiceName:",ServiceName,"][ServiceInfo:",ServiceInfo,"]","][num:",string:words(ServiceInfo,$,),"]"]),info),
            %check serviceinfo configuration 
            case string:words(ServiceInfo,$,) of
                1 ->
                    Port=ServiceInfo,
                    TcpHost={127,0,0,1},
                    mm_check_tcp:check(ServiceName,TcpHost,Port);
                2 ->
                    Port=string:sub_word(ServiceInfo,1,$,),
                    TcpHost=string:sub_word(ServiceInfo,2,$,),
                    mm_check_tcp:check(ServiceName,TcpHost,Port);
                _ ->
                    fun_common:log("[mm_collect][tcp][service table format bad! Must use , splite from port and host!]",warn)
            end
    end,
    lists:foldl(F, 0, Services),
    %GenericSummary=#generic_summary{load=Load,systime="321",uptime="9:54",tcp_connections=TcpConnections},
    %GenericSummary,
    %Generic_Cpu=#generic_cpu{user="1",nice="2",system="3",interrupt="4",idle="5"},
    %Generic_Cpu,
    %Generic_Mem=#generic_mem{active="1",inact="2",wired="3",cache="4",buf="5",free="6"},
    %Generic_Mem,
    %Generic_Swap=#generic_swap{total="11",used="12",free="13",inuse="14"},
    %Generic_Swap,
    %Disk_Partition=#disk_partition{mounted="/",capacity="97.1%",iused="98%"},
    %Disk_Partition,
    %Generic_Disks=#generic_disks{disk_partition=[Disk_Partition]},
    %Generic_Disks,
    %Generic_Process=#generic_process{sum="89",starting="12",running="12",sleeping="44",stopped="12",zombie="1",waiting="2",lock="1"},
    %Generic_Process,
    %Network_Interface=#network_interface{ifname="epair38b",ifin="172178232",ifout="434378891"},
    %Network_Interface,
    %Generic_Networks=#generic_networks{network_interface=[Network_Interface]},
    %Generic_Networks,
    %Service=#service{service_name="http",service_port="80",service_status="1"},
    %Service,
    %Generic_Services=#generic_services{service=[Service]},
    %Generic_Services,
    %Generic=#generic{summary=GenericSummary,cpu=Generic_Cpu,mem=Generic_Mem,swap=Generic_Swap,disk=Generic_Disks,
        %process=Generic_Process,network=Generic_Networks,service=Generic_Services},
    %Generic,
    %Monitor_Msg=#monitor_msg{
        %host_name="serverMadmonitor2",
        %generic=Generic
    %},
    %Monitor_Msg,
    %%{ok, Socket}=gen_tcp:connect("127.0.0.1", 13081,
        %%[binary, {packet, 4}]),
    %monitor_msg_pb:encode_generic(Generic),
    %monitor_msg_pb:encode_monitor_msg(Monitor_Msg),
    %gen_tcp:send(Socket, Pkt), %todo add case 
    %gen_tcp:close(Socket),
    %GenericSummary=#generic_summary{load="1.73",systime="321",uptime="9:54",tcp_connections="1121"},
    Now = integer_to_list(fun_common:timestamp()),
    fun_common:log("[mm_collect][Now:~p]",Now,info),
    %%TODO uname可以定期上传
    GenericSummary=#generic_summary{load=Load,systime=Now,uptime=Uptime,tcp_connections=TcpConnections,uname=Uname},
    GenericSummary,
    %FreebsdCpu=#freebsd_cpu{user="1.2",nice="1.3",system="1.4",interrupt="1.5",idle="1.6"},
    %GenericCpu=#generic_cpu{freebsd_cpu=FreebsdCpu},
    GenericCpu,
    %GenericMem=#generic_mem{active="1102M", inact="581M", wired="4091M", cache="34M", buf="113M", free="2113M"},
    GenericMem=#generic_mem{freebsd_mem=#freebsd_mem{total=MemTotal,active=MemActive,free=MemFree,inactive=MemInactive,
        wire=MemWire,cached=MemCached}},
    GenericMem,
    GenericSwap=#generic_swap{total=UsedSwap,used=TotalSwap},
    GenericSwap,
    %DiskPartition1=#disk_partition{mounted="/",capacity=87,iused=1},
    %DiskPartition2=#disk_partition{mounted="/services",capacity=78,iused=51},
    %DiskPartition1,
    %GenericDisks=#generic_disks{disk_partition=[DiskPartition1,DiskPartition2]},
    %GenericDisks=#generic_disks{disk_partition=ets:lookup(?disktb,disk_partition)},
    GenericDisks=#generic_disks{disk_partition=ets:match_object(?disktb,'_'),disk_iostat=ets:match_object(
            ?diskiostattb,'_')},
    GenericDisks,
    %GenericProcess=#generic_process{sum="77", starting="52", running="21", sleeping="121", stopped="11", zombie="1", waiting="4", lock="1"},
    FreebsdProcess=#freebsd_process{statd=integer_to_list(ProcD),stati=integer_to_list(ProcI),
        statl=integer_to_list(ProcL),statr=integer_to_list(ProcR),stats=integer_to_list(ProcS),
        statt=integer_to_list(ProcT),statw=integer_to_list(ProcW),statz=integer_to_list(ProcZ),
        total=integer_to_list(TotalProcesses)},
    GenericProcess=#generic_process{freebsd_process=FreebsdProcess},
    GenericProcess,
    %NetworkInterface1=#network_interface{ifname="epair28b",ifin="12121222212121",ifout="68684522121111"},
    %NetworkInterface2=#network_interface{ifname="eth0",ifin="37723733333333",ifout="489524434343412"},
    GenericNetworks=#generic_networks{network_interface=ets:match_object(?ifflowtb,'_')},
    GenericNetworks,
    %Service1=#service{service_name="httpd",service_port=80,service_status=1},
    %Service2=#service{service_name="zookeeper",service_port=2181,service_status=0,status_desc="zookeeper telnet to port 2181 has no reply"},
    fun_common:log("[match obj:~p~n]",ets:match_object(?servicetb,'_'),info),
    %GenericServices=#generic_services{service=[Service1,Service2]},
    GenericServices=#generic_services{service=ets:match_object(?servicetb,'_')},
    GenericServices,
    Generic=#generic{summary=GenericSummary,cpu=GenericCpu,mem=GenericMem,swap=GenericSwap,
        disk=GenericDisks,process=GenericProcess,network=GenericNetworks,service=GenericServices},
    Generic,
    fun_common:log("[will send data]",info),
    [{_,HostNm}]=ets:lookup(?basetb,host_name),
    case HostNm of  %如果没有配置hostname，采用系统的hostname
        "" ->
            HostName=re:replace(os:cmd("hostname"), "\n", "", [global, {return, list}]);
        _ ->
            HostName=HostNm
    end,
    [{_,UploadHost}]=ets:lookup(?basetb,upload_host),
    fun_common:log("[upload host:~p]",UploadHost,info),
    [{_,UploadPort}]=ets:lookup(?basetb,upload_port),
    fun_common:log("[upload port:~p]",UploadPort,info),
    fun_common:log("[get local ip:~p]",mm_util:get_ip_address_string(),info),
    [Ipa,_,Ipb,_,Ipc,_,Ipd]=mm_util:get_ip_address_string(),
    {ok,IpLong}=mm_util:ip2long({list_to_integer(Ipa),list_to_integer(Ipb),list_to_integer(Ipc),list_to_integer(Ipd)}),
    fun_common:log("[get local iplong:~p]",IpLong,info),
    Monitor_Msg=#monitor_msg{
        %remove probably quote mark
        host_name=re:replace(HostName, "\"", "", [global, {return, list}])++"_"++string:to_lower(integer_to_list(IpLong,16)),
        client_version="2.0.20130001",
        generic=Generic
    },
    {ok, Socket}=gen_tcp:connect(UploadHost, list_to_integer(UploadPort),
        [binary, {packet, 4}]),
    Pkt=monitor_msg_pb:encode_monitor_msg(Monitor_Msg),
    gen_tcp:send(Socket, Pkt), %todo add case 
    gen_tcp:close(Socket),
    ok.
