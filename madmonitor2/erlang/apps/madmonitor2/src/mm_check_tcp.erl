-module(mm_check_tcp).
-include("include/inc.hrl").
-include_lib("include/monitor_msg_pb.hrl").
    
-export([check/3]).


%%--------------------------------------------------------------------
%% @doc check 
%% @end
%%--------------------------------------------------------------------
check(ServiceName,Ip,Port) ->
    case mm_util:is_integer(Port) of %check port format 
        false ->
            fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][param Port format error:",Port,"]"]),warn),
            ets:insert(?servicetb,#service{service_name=ServiceName,service_port=0,service_status=0,status_desc="monitor client config error!"}),
            Ret=error;
        true ->
            PortNum=list_to_integer(Port),
             case gen_tcp:connect(Ip,PortNum,
                     [binary, {send_timeout, 2000},{packet, 0}],2000) of
                 {ok, Socket} ->
                     fun_common:log("[mm_check_tcp][service:~p][connect][status:ok]",ServiceName,info),
                     WaitSec=2000,
                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][connect][=>start check][WaitSec:",WaitSec,"]"]),info),
                     timer:sleep(WaitSec),
                     %TODO add tcp specifly service check
                     case ServiceName of 
                         "ftp" ->
                             case ftp:open(Ip) of
                                 {ok, Pid} ->
                                     ftp:close(Pid);
                                 {error, ReasonFtp} ->
                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][send][status:fail][Reason:",ReasonFtp,"]"]),warn)
                             end,
                             %check by socket again
                             receive
                                 {tcp,Socket,Data} ->
                                     DataString=binary_to_list(Data),
                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][recv:~p]"]),DataString,info),
                                     mm_util:expect(ServiceName,"220",DataString);
                                 {tcp_close,Socket}->
                                     ets:insert(?servicetb,#service{service_name=ServiceName,service_port=PortNum,service_status=0,status_desc="tcp recv fail!"}),
                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][recv:fail]"]),warn)
                             end;
                         "pop" ->
                             receive
                                 {tcp,Socket,Data} ->
                                     DataString=binary_to_list(Data),
                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][recv:~p]"]),DataString,info),
                                     mm_util:expect(ServiceName,"+OK",DataString);
                                 {tcp_close,Socket}->
                                     ets:insert(?servicetb,#service{service_name=ServiceName,service_port=PortNum,service_status=0,status_desc="tcp recv fail!"}),
                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][recv:fail]"]),warn)
                             end;
                         "pop3" ->
                             receive
                                 {tcp,Socket,Data} ->
                                     DataString=binary_to_list(Data),
                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][recv:~p]"]),DataString,info),
                                     mm_util:expect(ServiceName,"+OK",DataString);
                                 {tcp_close,Socket}->
                                     ets:insert(?servicetb,#service{service_name=ServiceName,service_port=PortNum,service_status=0,status_desc="tcp recv fail!"}),
                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][recv:fail]"]),warn)
                             end;
                         "smtp" ->
                             receive
                                 {tcp,Socket,Data} ->
                                     DataString=binary_to_list(Data),
                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][recv:~p]"]),DataString,info),
                                     mm_util:expect(ServiceName,"220",DataString);
                                 {tcp_close,Socket}->
                                     ets:insert(?servicetb,#service{service_name=ServiceName,service_port=PortNum,service_status=0,status_desc="tcp recv fail!"}),
                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][recv:fail]"]),warn)
                             end;
                         "imap" ->
                             receive
                                 {tcp,Socket,Data} ->
                                     DataString=binary_to_list(Data),
                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][recv:~p]"]),DataString,info),
                                     mm_util:expect(ServiceName,"* OK",DataString);
                                 {tcp_close,Socket}->
                                     ets:insert(?servicetb,#service{service_name=ServiceName,service_port=PortNum,service_status=0,status_desc="tcp recv fail!"}),
                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][recv:fail]"]),warn)
                             end;
                         "simap" ->
                             receive
                                 {tcp,Socket,Data} ->
                                     DataString=binary_to_list(Data),
                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][recv:~p]"]),DataString,info),
                                     mm_util:expect(ServiceName,"* OK",DataString);
                                 {tcp_close,Socket}->
                                     ets:insert(?servicetb,#service{service_name=ServiceName,service_port=PortNum,service_status=0,status_desc="tcp recv fail!"}),
                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][recv:fail]"]),warn)
                             end;
                         "spop" ->
                             receive
                                 {tcp,Socket,Data} ->
                                     DataString=binary_to_list(Data),
                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][recv:~p]"]),DataString,info),
                                     mm_util:expect(ServiceName,"+OK",DataString);
                                 {tcp_close,Socket}->
                                     ets:insert(?servicetb,#service{service_name=ServiceName,service_port=PortNum,service_status=0,status_desc="tcp recv fail!"}),
                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][recv:fail]"]),warn)
                             end;
                         "ssmtp" ->
                             receive
                                 {tcp,Socket,Data} ->
                                     DataString=binary_to_list(Data),
                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][recv:~p]"]),DataString,info),
                                     mm_util:expect(ServiceName,"220",DataString);
                                 {tcp_close,Socket}->
                                     ets:insert(?servicetb,#service{service_name=ServiceName,service_port=PortNum,service_status=0,status_desc="tcp recv fail!"}),
                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][recv:fail]"]),warn)
                             end;
                         %jabber TODO
                         "nntp" ->
                             receive
                                 {tcp,Socket,Data} ->
                                     DataString=binary_to_list(Data),
                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][recv:~p]"]),DataString,info),
                                     mm_util:expect(ServiceName,"200",DataString);
                                 {tcp_close,Socket}->
                                     ets:insert(?servicetb,#service{service_name=ServiceName,service_port=PortNum,service_status=0,status_desc="tcp recv fail!"}),
                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][recv:fail]"]),warn)
                             end;
                         "nntps" ->
                             receive
                                 {tcp,Socket,Data} ->
                                     DataString=binary_to_list(Data),
                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][recv:~p]"]),DataString,info),
                                     mm_util:expect(ServiceName,"200",DataString);
                                 {tcp_close,Socket}->
                                     ets:insert(?servicetb,#service{service_name=ServiceName,service_port=PortNum,service_status=0,status_desc="tcp recv fail!"}),
                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][recv:fail]"]),warn)
                             end;
                         "memcache" ->
                             case gen_tcp:send(Socket, "stats\n") of %send some data via tcp 
                                 {error, timeout} ->
                                     ets:insert(?servicetb,#service{service_name=ServiceName,service_port=PortNum,service_status=0,status_desc="tcp send timeout closing!"}),
                                     fun_common:log("[mm_check_tcp][service:~p][send][status:fail][send timeout closing]",ServiceName,warn);
                                 ok ->
                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][send][status:ok][WaitSec:",WaitSec,"]"]),info),
                                     receive
                                         {tcp,Socket,Data} ->
                                             DataString=binary_to_list(Data),
                                             fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][recv:~p]"]),DataString,info),
                                             case re:run(DataString,"\r\nSTAT evictions ([0-9].*)\r\n") of
                                                 {match,[_,{S,L}]} ->
                                                     EvicNum=string:substr(DataString,S+1,L),
                                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][evictions:~p]"]),EvicNum,info),
                                                     case EvicNum of 
                                                         "0" -> 
                                                             fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][ok]"]),info);
                                                         _ ->
                                                             ets:insert(?servicetb,#service{service_name=ServiceName,service_port=PortNum,service_status=0,status_desc="memcached evictions warning!"}),
                                                             fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][evictions warning]"]),warn)
                                                     end;
                                                 _ ->
                                                     ets:insert(?servicetb,#service{service_name=ServiceName,service_port=PortNum,service_status=0,status_desc="memcached evictions:get fail!"}),
                                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][evictions:get fail]"]),warn)
                                             end
                                     end;
                                 {error,Reason0} ->
                                     ets:insert(?servicetb,#service{service_name=ServiceName,service_port=PortNum,service_status=0,status_desc="tcp send fail!"}),
                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][send][status:fail][Reason:",Reason0,"]"]),warn)
                             end;
                         "redis" ->
                             case gen_tcp:send(Socket, "info\r\n") of
                                 {error,timeout} ->
                                     ets:insert(?servicetb,#service{service_name=ServiceName,service_port=PortNum,service_status=0,status_desc="tcp send timeout closing!"}),
                                     fun_common:log("[mm_check_tcp][service:~p][send][status:fail][send timeout closing]",ServiceName,warn);
                                 ok ->
                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][send][status:ok][WaitSec:",WaitSec,"]"]),info),
                                     fun_common:log(lists:concat(["[mm_check_tcp][------------------]"]),info),
                                     receive
                                         {tcp,Socket,Data} ->
                                             DataString=binary_to_list(Data),
                                             fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][recv:~p]"]),DataString,info),
                                             case re:run(DataString,"\r\nrole:slave\r\n") of
                                                 {match,_} ->
                                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][role:slave]"]),info),
                                                     case re:run(DataString,"\r\nmaster_link_status:up\r\n") of
                                                         {match,_} ->
                                                             fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][master link:ok]"]),info);
                                                         _ ->
                                                             ets:insert(?servicetb,#service{service_name=ServiceName,service_port=PortNum,service_status=0,status_desc="redis master link:fail!"}),
                                                             fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][master link:fail]"]),info)
                                                     end;
                                                 _ ->
                                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][role:master need`nt check]"]),info)
                                             end
                                     end;
                                 {error,ReasonRedis} ->
                                     ets:insert(?servicetb,#service{service_name=ServiceName,service_port=PortNum,service_status=0,status_desc="tcp send other error by redis!"}),
                                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][send][status:fail][Reason:",ReasonRedis,"]"]),warn)
                             end;
                         _ ->
                             fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][is generic tcp service]"]),info)
                     end,

                     case gen_tcp:send(Socket, "Some Data") of %send some data via tcp 
                         {error, timeout} ->
                             ets:insert(?servicetb,#service{service_name=ServiceName,service_port=PortNum,service_status=0,status_desc="tcp send timeout closing!"}),
                             fun_common:log("[mm_check_tcp][service:~p][send][status:fail][send timeout closing]",ServiceName,warn),
                             Ret=error;
                         ok ->
                             ets:insert(?servicetb,#service{service_name=ServiceName,service_port=PortNum,service_status=1}),
                             fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][send][status:ok][WaitSec:",WaitSec,"]"]),info),
                             Ret=ok;
                         {error,Reason1} ->
                             ets:insert(?servicetb,#service{service_name=ServiceName,service_port=PortNum,service_status=0,status_desc="tcp send fail!"}),
                             fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][send][status:fail][Reason:",Reason1,"]"]),warn),
                             Ret=error
                     end,
                     gen_tcp:close(Socket);
                 {error,Reason2} ->
                     ets:insert(?servicetb,#service{service_name=ServiceName,service_port=PortNum,service_status=0,status_desc="tcp connect fail!"}),
                     fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceName,"][connect][status:fail][Reason:",Reason2,"]"]),warn),
                     Ret=error
             end
     end,
     Ret.

