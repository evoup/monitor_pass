-module(ifstat).

-export([init/0, ifstat_ex/0]).

-on_load(init/0).

init() ->
    case file:get_cwd() of
        {ok, CwdPath} ->
            erlang:load_nif(lists:concat([CwdPath,"/priv/ifstat"]), 0);
        {error, Reason_1} ->
            fun_common:log("[fun_common.get_cwd][got error:~p]",Reason_1,warn),
            madmonitorError
    end.

%%--------------------------------------------------------------------
%% @doc NIF get ifstat from c_src 
%% @end
%%--------------------------------------------------------------------
ifstat_ex() ->
    "NIF library not loaded".

