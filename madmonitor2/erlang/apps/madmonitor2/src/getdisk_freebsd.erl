-module(getdisk_freebsd).

-export([init/0, getdisk_freebsd_ex/0]).

-on_load(init/0).

init() ->
    case file:get_cwd() of
        {ok, CwdPath} ->
            erlang:load_nif(lists:concat([CwdPath,"/priv/getdisk_freebsd"]), 0);
        {error, Reason_1} ->
            fun_common:log("[fun_common.get_cwd][got error:~p]",Reason_1,warn),
            madmonitorError
    end.

%%--------------------------------------------------------------------
%% @doc NIF get disk info from c_src 
%% @end
%%--------------------------------------------------------------------
getdisk_freebsd_ex() ->
    "NIF library not loaded".

