-module(mm_util).
-export([
        implode/2,
        is_integer/1,
        receive_data_tcp/2,
        expect/3,
        get_ip_address_string/0,
        ip2long/1,
        integer_to_hex/1,
        hex_to_integer/1,
        string_to_hex/1,
        hex_to_string/1
    ]).

implode(List, Sep) ->
    implode(List, Sep, []).

implode([], _Sep, Acc) ->
    lists:flatten(lists:reverse(Acc));
implode([H], Sep, Acc) ->
    implode([], Sep, [H|Acc]);
implode([H|T], Sep, Acc) ->
    implode(T, Sep, [Sep,H|Acc]).

is_integer(S) ->
    case re:run(S, "^[0-9]*$") of
        nomatch ->
            Ret=false;
        _ ->
            Ret=true
    end,
    Ret.

receive_data_tcp(Socket, SoFar) ->
    receive
        {tcp,Socket,Bin} ->
            receive_data_tcp(Socket, [Bin|SoFar]);
        {tcp_closed,Socket} ->
            list_to_binary(lists:reverse(SoFar))
    end.

expect(ServiceNam,ExpectStr,DataStr) ->
    case string:substr(DataStr,1,3) of
        ExpectStr -> %check wether return code 220
            fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceNam,"][recv:~p][ok]"]),ExpectStr,info),
            ok;
        _ ->
            fun_common:log(lists:concat(["[mm_check_tcp][service:",ServiceNam,"][recv:not ~p][fail]"]),ExpectStr,warn),
            error
    end.

ip_triple_to_string(Tup) ->
    io_lib:format("~b.~b.~b.~b", tuple_to_list(Tup)).

%%from egeoip
get_ip_address_string() ->
    {ok, IPTriples} = inet:getif(),
    Strings = [ip_triple_to_string(IP) || {IP, _, _} <- IPTriples,
        IP =/= {127,0,0,1}],
    %io:format("[get_ip_address_string][Strings:~p]~n",[Strings]),
    [Str|_]=Strings,
    %io:format("[Str:~p]~n",[Str]),
    Res=string:join([Str], " "),
    %io:format("[Res:~p]~n",[Res]),
    Res.

address_fast([N2, N1, N0, $. | Rest], Num, Shift) when Shift >= 8 ->
    case list_to_integer([N2, N1, N0]) of
        N when N =< 255 ->
            address_fast(Rest, Num bor (N bsl Shift), Shift - 8)
    end;
address_fast([N1, N0, $. | Rest], Num, Shift) when Shift >= 8 ->
    case list_to_integer([N1, N0]) of
        N when N =< 255 ->
            address_fast(Rest, Num bor (N bsl Shift), Shift - 8)
    end;
address_fast([N0, $. | Rest], Num, Shift) when Shift >= 8 ->
    case N0 - $0 of
        N when N =< 255 ->
            address_fast(Rest, Num bor (N bsl Shift), Shift - 8)
    end;
address_fast(L=[_N2, _N1, _N0], Num, 0) ->
    case list_to_integer(L) of
        N when N =< 255 ->
            Num bor N
    end;
address_fast(L=[_N1, _N0], Num, 0) ->
    case list_to_integer(L) of
        N when N =< 255 ->
            Num bor N
    end;
address_fast([N0], Num, 0) ->
    case N0 - $0 of
        N when N =< 255 ->
            Num bor N
    end.

%% @spec ip2long(Address) -> {ok, integer()}
%% @doc Convert an IP address from a string, IPv4 tuple or IPv6 tuple to the
%%      big endian integer representation.
ip2long(Address) when erlang:is_integer(Address) ->
    {ok, Address};
ip2long(Address) when erlang:is_list(Address) ->
    case catch address_fast(Address, 0, 24) of
        N when erlang:is_integer(N) ->
            {ok, N};
        _ ->
            case inet_parse:address(Address) of
                {ok, Tuple} ->
                    ip2long(Tuple);
                Error ->
                    Error
            end
    end;
ip2long({B3, B2, B1, B0}) ->
    {ok, (B3 bsl 24) bor (B2 bsl 16) bor (B1 bsl 8) bor B0};
ip2long({W7, W6, W5, W4, W3, W2, W1, W0}) ->
    {ok, (W7 bsl 112) bor (W6 bsl 96) bor (W5 bsl 80) bor (W4 bsl 64) bor
         (W3 bsl 48) bor (W2 bsl 32) bor (W1 bsl 16) bor W0};
ip2long(<<Addr:32>>) ->
    {ok, Addr};
ip2long(<<Addr:128>>) ->
    {ok, Addr};
ip2long(_) ->
    {error, badmatch}.

integer_to_hex(I) -> 
      case catch erlang:integer_to_list(I, 16) of 
          {'EXIT', _} -> 
               old_integer_to_hex(I); 
          Int -> 
               Int 
      end.

old_integer_to_hex(I) when I<10 -> 
      integer_to_list(I); 
old_integer_to_hex(I) when I<16 -> 
      [I-10+$A]; 
old_integer_to_hex(I) when I>=16 -> 
      N = trunc(I/16), 
      old_integer_to_hex(N) ++ old_integer_to_hex(I rem 16).


%% hex_to_integer

hex_to_integer(Hex) -> 
      erlang:list_to_integer(Hex, 16).

%% string_to_hex

string_to_hex(String) -> 
      HEXC = fun (D) when D > 9 -> $a + D - 10; 
                 (D) -> $0 + D 
             end, 
      lists:foldr(fun (E, Acc) -> 
              [HEXC(E div 16),HEXC(E rem 16)|Acc] end, [], String).


%% hex_to_string

hex_to_string(Hex) -> 
      DEHEX = fun (H) when H >= $a -> H - $a + 10; 
                  (H) when H >= $A -> H - $A + 10; 
                  (H) -> H - $0 
              end, 
  {String, _} =lists:foldr(fun

           (E, {Acc, nolow}) -> {Acc, DEHEX(E)}; 
           (E, {Acc, LO}) -> {[DEHEX(E)*16+LO|Acc], nolow} 
      end, {[], nolow}, Hex), 
      String.
