-module(monitor_init).
-include("include/inc.hrl").
-include("include/monitor_msg_pb.hrl").
-export([run/0]).

run() ->
    fun_common:logInit(),
    [Ipa,_,Ipb,_,Ipc,_,Ipd]=mm_util:get_ip_address_string(),
    Ip=lists:concat([Ipa,".",Ipb,".",Ipc,".",Ipd]), %TODO COMFIRM MULTI IPS 
    case mm_util:ip2long(Ip) of 
        {ok, CookiePassport} ->
            fun_common:log(lists:concat(["[monitor_init][long2ip:~p]"]),CookiePassport,info),
            HexCkp=mm_util:integer_to_hex(CookiePassport), %convert to hexcode 
            fun_common:log(lists:concat(["[monitor_init][long2ip hexcode:~p]"]),HexCkp,info),
            %cookie primitive format (madcore+hexcode of ip2long)
            CookieAtom=erlang:list_to_atom(lists:concat([madcore,HexCkp])),
            fun_common:log(lists:concat(["[monitor_init][long2ip hexcode auto:~p]"]), CookieAtom, info),
            erlang:set_cookie(node(), CookieAtom),
            fun_common:log("[monitor_init][self node:~p]",node(),info),
            fun_common:log("[monitor_init][self cookie has been set:~p]",lists:concat([madcore,HexCkp]),info);
        _ ->
            erlang:exit(getIperror)
    end,
    fun_common:log(lists:concat(["[monitor_init][localip(s):~p]"]),Ip,info),
    CF = #confInfo{},
    C = #const{},
    fun_common:log("[monitor_init][client start--------------]",info),
    %create conf and work folder if not existed
    Conf_file = filename:join([C#const.proc_root,C#const.conf_subpath,C#const.conf_file]),
    Conf_dir = filename:dirname(Conf_file),
    fun_common:log("[monitor_init][conf file: ~p]",Conf_file,info),
    fun_common:log("[monitor_init][conf dir: ~p]",Conf_dir,debug),
    case file:read_file_info(C#const.proc_root) of
        {error, _} -> %read fail,create work prefix
            case fun_common:mkPrefix() of
                madmonitorError ->
                    fun_common:log("[monitor_init][make prefix dir err,check privilege,check whether base root ~p exist]",C#const.proc_basedir,warn),
                    madmonitorError;
                _ -> fun_common:log("[monitor_init][build configuration file,done. run again.]",warn),
                    madmonitorError
            end;
        {ok, _} -> %read ok,return configure info
            OsType=fun_common:osType(),
            fun_common:log("[monitor_init][OsType:~p]",OsType,info),
            C = #const{},
            IniFile = lists:concat([C#const.proc_root,C#const.conf_subpath,C#const.conf_file]),
            fun_common:log("[monitor_init][read configure file:~p]",IniFile,info),
            ets:new(fun_common, [named_table, set, protected]),
            %%{{{%%
            AllIniFiles=[IniFile], %can`t add many configure files 
            try
                lists:map(fun(Inf) ->
                            {ok, ParsedIniValues} = fun_common:parse_ini_file(Inf),
                            ets:insert(fun_common, ParsedIniValues)
                    end, AllIniFiles)
                catch _Tag:Error ->
                    fun_common:log("[monitor_init][Error:~p]",Error),
                    throw({error,Error})
            end,
            %A=fun_common:get("madn_url","smartMad","Default Value"),
            MadnUrl=fun_common:get("madn_url"),
            F = fun(ShowUrl,_)->
                    {UrlName,UrlPath}=ShowUrl,
                    fun_common:log(lists:concat(["[monitor_init][UrlName:",UrlName,"][UrlPath:",UrlPath,"]"]),info)
            end,
            lists:foldl(F, 0, MadnUrl),
            %%}}}%%
            ets:new(?basetb, [named_table, set, protected]), %init base table
            %ets:new(?disktb, [named_table, set, protected]), %init disk table
            ets:new(?disktb,[public,set,named_table,{keypos,#disk_partition.mounted}]), %init disk table
            ets:new(?diskiostattb,[public,set,named_table,{keypos,#disk_iostat.device_name}]), %init disk_iostat table
            ets:new(?ifstattb,[public,set,named_table,{keypos,#ifStat.flow_interface}]), %init ifstat table
            ets:new(?ifflowtb,[public,set,named_table,{keypos,#network_interface.ifname}]), %init ifflow table
            ets:new(?servicetb,[public,set,named_table,{keypos,#service.service_name}]), %init service table
            %ets:new(?servicetb, [named_table, set, protected]), %init service table
            ets:new(?colltb, [named_table, set, protected]), %init collect table
            {CF,ok}
    end.
