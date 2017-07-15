%%%-------------------------------------------------------------------
%%% @author evoup <yinjia@madhouse-inc.com> 
%%%  [http://www.madhouse-inc.com]
%%% @copyright 2012 madhouse 
%%% @desc 仿memcache协议的hbase简单查询telnet服务器
%%% @doc RPC over TCP server. This module defines a server process that
%%%      listens for incoming TCP connections and allows the user to
%%%      execute RPC commands via that TCP stream.
%%% @end
%%%-------------------------------------------------------------------

-module(shell_server).

-behaviour(gen_server).

-include_lib("eunit/include/eunit.hrl").
-include("hbase_types.hrl"). %如何在shell下使用rr(hbase_types).参见http://www.erlang.org/doc/man/shell.html 
-define(_WELCOME, "Welcome to Hbase Telnet Shell！\n>>"). % 欢迎标题 
-define(_SHELL_ARROW, ">").
-define(_KEY_TABLE, "table").
-define(_KEY_COL,   "col").

%% API
-export([
        start_link/1,
        start_link/0,
        get_count/0,
        stop/0
    ]).

%% gen_server callbacks
-export([init/1, handle_call/3, handle_cast/2, handle_info/2,
        terminate/2, code_change/3]).

-define(SERVER, ?MODULE).
-define(DEFAULT_PORT, 1055).
-define(WELCOME, "welcome hbase telnet shell！\n>>").
-define(SHELL_ARROW, ">").

-record(state, {port, lsock, request_count = 0}).


%%%===================================================================
%%% API
%%%===================================================================


%%--------------------------------------------------------------------
%% @doc Starts the server.
%%
%% @spec start_link(Port::integer()) -> {ok, Pid}
%% where
%%  Pid = pid()
%% @end
%%--------------------------------------------------------------------
start_link(Port) -> %第一调用的函数 
    put(?_KEY_TABLE, ""),
    put(?_KEY_COL, ""),
    gen_server:start_link({local, ?SERVER}, ?MODULE, [Port], []). %ServerName,Module,Args,Options 

%% @spec start_link() -> {ok, Pid}
%% @doc Calls `start_link(Port)' using the default port.
start_link() -> %入口函数 
    start_link(?DEFAULT_PORT).

%%--------------------------------------------------------------------
%% @doc Fetches the number of requests made to this server.
%% @spec get_count() -> {ok, Count}
%% where
%%  Count = integer()
%% @end
%%--------------------------------------------------------------------
get_count() ->
    gen_server:call(?SERVER, get_count).

%%--------------------------------------------------------------------
%% @doc Stops the server.
%% @spec stop() -> ok
%% @end
%%--------------------------------------------------------------------
stop() ->
    gen_server:cast(?SERVER, stop).


%%%===================================================================
%%% gen_server callbacks
%%%===================================================================

init([Port]) ->
    {ok, LSock} = gen_tcp:listen(Port, [{active, true}]),
    {ok, #state{port = Port, lsock = LSock}, 0}.

handle_call(get_count, _From, State) ->
    {reply, {ok, State#state.request_count}, State}.

handle_cast(stop, State) ->
    {stop, normal, State}.

handle_info({tcp, Socket, RawData}, State) ->
    do_shell(Socket, RawData),
    RequestCount = State#state.request_count,
    {noreply, State#state{request_count = RequestCount + 1}};
handle_info(timeout, #state{lsock = LSock} = State) ->
    {ok, _Sock} = gen_tcp:accept(LSock),
    gen_tcp:send(_Sock, ?_WELCOME),
    {noreply, State}.

terminate(_Reason, _State) ->
    ok.

code_change(_OldVsn, State, _Extra) ->
    {ok, State}.

%%%===================================================================
%%%  shell交互
%%%===================================================================
do_shell(Socket, RawData) ->
    try
        %Response = "do something\r\n", 
        Response = shell_request_parse(RawData), %处理请求数据 
        gen_tcp:send(Socket, Response)
    catch
        _Class:Err ->
            gen_tcp:send(Socket, io_lib:fwrite("~p~n", [Err]))
    end.

%%%===================================================================
%%% 处理GET/SET的数据
%%%===================================================================
shell_request_parse(RawData) ->
    SetTableData = string:sub_string(RawData, 1, 11),
    if % 先判断是否是set table 
        (SetTableData == "set $table=") or (SetTableData == "SET $TABLE=") ->
            TmpTableName = string:substr(RawData, 12, string:len(RawData)-11),
            TableName =  re:replace(TmpTableName, "\r\n", "", [{return,list}]),
            put(?_KEY_TABLE, TableName), % 保存选择的table 
            string:join(["use table: ", TableName, "\nEND\n", ?_SHELL_ARROW, " "], "");
        true -> % 当作else分支处理 
            SetColumnData = string:sub_string(RawData, 1, 12),
            if % 再判断是否是set column 
                (SetColumnData == "set $column=") or (SetColumnData == "SET $COLUMN=") ->
                    TmpColName = string:substr(RawData, 13, string:len(RawData)-11),
                    ColName = re:replace(TmpColName, "\r\n", "", [{return,list}]),
                    put(?_KEY_COL, ColName), % 保存选择的column 
                    string:join(["use column: ", ColName, "\nEND\n", ?_SHELL_ARROW, " "], "");
                true ->
                    SelectedTable = get(?_KEY_TABLE),
                    SelectedCol = get(?_KEY_COL),
                    if
                        (SelectedTable == undefined) or (SelectedCol == undefined) ->
                            string:join(["error\n", ?_SHELL_ARROW, " "], ""); % 输出错误提示符 
                        true ->
                             GetRow = string:sub_string(RawData, 1, 4),
                             if
                                 (GetRow == "get ") or (GetRow == "GET ") ->
                                     TmpRow = string:sub_string(RawData, 5),
                                     Row = re:replace(TmpRow, "\r\n", "", [{return,list}]),
                                     Port=9090,
                                     {ok, Client0} = thrift_client_util:new(
                                         "127.0.0.1", Port, hbase_thrift, []),
                                     % get rowing
                                     error_logger:error_msg(string:join(["[", get(?_KEY_TABLE), "]"], "")),
                                     error_logger:error_msg(string:join(["[", get(?_KEY_COL), "]"], "")),
                                     error_logger:error_msg(string:join(["[", Row, "]"], "")),
                                     getData(get(?_KEY_TABLE), Row, get(?_KEY_COL));
                                     true ->
                                         string:join(["error\n", ?_SHELL_ARROW, " "], "") % 输出错误提示符 
                             end
                    end
            end
    end.

getData(T, R ,C) ->
    Port=9090,
    {ok, Client0} = thrift_client_util:new(
        "127.0.0.1", Port, hbase_thrift, []),
    {X1, X2} = thrift_client:call(Client0, get, [T, R, C]),
    {Ok, L} = X2,
    [{Tcell, Data, Ts}] =L,
    string:join([binary_to_list(Data), "\n", ?_SHELL_ARROW, " "], "").

%% test
start_test() ->
    {ok, _} = shell_server:start_link(1055).
