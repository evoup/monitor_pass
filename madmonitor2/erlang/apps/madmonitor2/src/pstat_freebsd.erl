-module(pstat_freebsd).

-export([init/0, get_pstat_ex/0]).

-on_load(init/0).

init() ->
    case file:get_cwd() of
        {ok, CwdPath} ->
            erlang:load_nif(lists:concat([CwdPath,"/priv/pstat_freebsd"]), 0);
        {error, Reason_1} ->
            fun_common:log("[fun_common.get_cwd][got error:~p]",Reason_1,warn),
            madmonitorError
    end.

%%--------------------------------------------------------------------
%% @doc NIF get pstat_freebsd from c_src 
%% @end
%%--------------------------------------------------------------------
get_pstat_ex() ->
    "NIF library not loaded".

