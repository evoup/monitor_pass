-module(connnode).
-export([
        run/0,
        run/2
    ]).

run()->
    {_,[[Ip]]}=init:get_argument(flagIp),
    {_,[[Pass]]}=init:get_argument(flagPass),
    %io:format("~p",[Ip]),
    %io:format("~p",[Pass]),
    PassA=list_to_atom(Pass),
    run(Ip,PassA).

run(Ip,Pass) ->
    %io:format("~p",[Ip]),
    %io:format("~p",[Pass]),
    Node=list_to_atom(lists:concat(["madmonitor2@",Ip])),
    erlang:set_cookie(Node,Pass),
    case net_adm:ping(Node) of 
        pong ->
            %Call=rpc:call(Node,os,cmd,['uname -a']),
            io:format("1"),
            ok;
        pang ->
            io:format("0"),
            fail
    end.
