-module(netstat).

%-export([init/0, netstat_ex/0, netstat_r/0]).
-export([init/0, netstat_ex/0]).

-on_load(init/0).

init() ->
    case file:get_cwd() of
        {ok, CwdPath} ->
            erlang:load_nif(lists:concat([CwdPath,"/priv/netstat"]), 0);
        {error, Reason_1} ->
            fun_common:log("[fun_common.get_cwd][got error:~p]",Reason_1,warn),
            madmonitorError
    end.

%%--------------------------------------------------------------------
%% @doc NIF get network status from c_src 
%% @end
%%--------------------------------------------------------------------
netstat_ex() ->
    "NIF library not loaded".

%%--------------------------------------------------------------------
%% @doc NIF get gateway info from c_src 
%% @end
%%--------------------------------------------------------------------
%netstat_r() ->
    %"NIF library not loaded".

