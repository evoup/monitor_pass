%%%-------------------------------------------------------------------
%%% @author evoup <yinjia@madhouse-inc.com>
%%%  [http://monitor.mdn2.net]
%%% @copyright 2006-2013 Madhouse
%%% @doc Server Monitor client 
%%%      Used to send monitor metics infomation to monitor server 
%%% @end
%%%-------------------------------------------------------------------

-module(madmonitor2).
-include("include/inc.hrl").

%% API
-export([
     run/0,
     stop/1,
     bye/0
     ]).

stop(_State) ->
    ok.

%%--------------------------------------------------------------------
%% @doc Start the madmonitor client.
%% @spec run() -> {error} 
%% @end
%%--------------------------------------------------------------------
run() ->
    case monitor_init:run() of
        madmonitorError -> 
            bye();
        {ConfInfo,_} ->
            fun_common:log("[sleep time ~p]",ConfInfo#confInfo.sleep,info),
            mainLoop()
    end,
    {error}.

%%%===================================================================
%%% Internal functions
%%%===================================================================

%%--------------------------------------------------------------------
%% @doc main client process loop 
%% @end
%%--------------------------------------------------------------------
mainLoop() ->
    %Now=fun_common:unixMicro(),
    CF = #confInfo{},
    %fun_common:log("[Now:~p]",Now,info),
    fun_common:log("[sleep ~p secs]",CF#confInfo.sleep,info),
    %TODO status file check

    mm_collect:collect_server(),
    timer:sleep(5000), %http://erlang.2086793.n4.nabble.com/timer-sleep-bug-td2089074.html
    mainLoop().

%%--------------------------------------------------------------------
%% @doc exit proc 
%% @end
%%--------------------------------------------------------------------
bye() ->
    bye.
