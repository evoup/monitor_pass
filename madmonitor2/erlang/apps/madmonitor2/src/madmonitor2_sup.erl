
-module(madmonitor2_sup).

-behaviour(supervisor).

%% API
-export([start_link/0]).

%% Supervisor callbacks
-export([init/1]).

%% Helper macro for declaring children of supervisor
%-define(CHILD(I, Type), {I, {I, start_link, []}, permanent, 5000, Type, [I]}).

%% ===================================================================
%% API functions
%% ===================================================================

start_link() ->
    supervisor:start_link({local, ?MODULE}, ?MODULE, []).

%% ===================================================================
%% Supervisor callbacks
%% ===================================================================

init([]) ->
    process_flag(trap_exit, true),
    Client = {madmonitor2, {madmonitor2, run, []},
        permanent, 2000, worker, [madmonitor2]},
    Children = [Client],
    RestartStrategy = {one_for_one, 10, 300},
    {ok, {RestartStrategy, Children}}.

