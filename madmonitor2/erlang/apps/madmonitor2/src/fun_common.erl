-module(fun_common).
-include_lib("kernel/include/file.hrl").
-include("include/inc.hrl").
-export([
        timestamp/0,
        logInit/0,
        osType/0,
        log/2,
        log/3,
        mkPrefix/0,
        parse_ini_file/1,
        get/1,
        get/2,
        get/3,
        float/2
    ]).

%%--------------------------------------------------------------------
%% @doc caculator unix microtime 
%% @end
%%--------------------------------------------------------------------
%unixMicro() ->
    %{Megasecond,Second,MicroSecond}=erlang:now(),
    %M=integer_to_list(Megasecond),
    %S=integer_to_list(Second),
    %Mi=integer_to_list(MicroSecond),
    %{Int,_}=string:to_integer(string:concat(M,string:concat(S,Mi))),
    %Int.

%%--------------------------------------------------------------------
%% @doc caculator unix timestamp 
%% @end
%%--------------------------------------------------------------------
timestamp() ->
    {M, S, _} = erlang:now(),  
    M * 1000000 + S.

%%--------------------------------------------------------------------
%% @doc init log system 
%% @end
%%--------------------------------------------------------------------
logInit() ->
    application:start(log4erl),
    %TODO 添加错误处理 
    case file:get_cwd() of
        {ok, CwdPath} ->
            log4erl:conf(lists:concat([CwdPath,"/priv/l4e.conf"]));
        {error, Reason_1} ->
            log("[fun_common.get_cwd][got error:~p]",Reason_1,warn),
            madmonitorError
    end,
    ok.

%%--------------------------------------------------------------------
%% @doc log wrapper,Lev must be in debug < info < warn < error < fatal
%% @end
%%--------------------------------------------------------------------
log(Info,Lev) ->
    %Lev必须为debug < info < warn < error < fatal其中之一
    log4erl:log(Lev,Info),
    ok.

%%--------------------------------------------------------------------
%% @doc log wrapper2,like log(Info,Lev),also with param 
%% @end
%%--------------------------------------------------------------------
log(InfoWithParam,Param,Lev) ->
    log4erl:log(Lev,InfoWithParam,[Param]),
    ok.

%%--------------------------------------------------------------------
%% @doc create work directory 
%% @end
%%--------------------------------------------------------------------
mkPrefix() ->
    case file:get_cwd() of 
        {ok, CwdPath} ->
            log("[fun_common.mkPrefix][current work Directory:~p]",CwdPath,debug),
            SrcPath = lists:concat([CwdPath,"/priv/sampleConf/"]),
            C = #const{},
            case file:read_file_info(C#const.proc_basedir) of
                {ok,Facts1} ->
                    case Facts1#file_info.type of 
                        regular   -> regular; 
                        directory -> directory; 
                        _         -> error 
                    end,
                    ok;
                {error, Reason_0} ->
                    log("[fun_common.mkPrefix][make basedir][basedir not exist]",Reason_0,debug),
                    file:make_dir(C#const.proc_basedir)
            end,
            recursive_copy(SrcPath,C#const.proc_root),
            case file:read_file_info(C#const.proc_root) of
                {ok, Facts} -> 
                    log("[fun_common.mkPrefix][type:~p]",Facts#file_info.type,debug),
                    case Facts#file_info.type of 
                        regular   -> regular; 
                        directory -> directory; 
                        _         -> error 
                    end,
                    ok;
                {error, Reason_1} ->
                    log("[fun_common.mkPrefix][got error:~p]",Reason_1,warn),
                    madmonitorError
            end;
        {error, Reason_2} ->
            log("[fun_common.mkPrefix][got error:~p]",Reason_2,warn),
            madmonitorError
    end.

%%--------------------------------------------------------------------
%% @doc copy folder from folder by recursively 
%% @end
%%--------------------------------------------------------------------
recursive_copy(Src, Dest) ->
    %% Note that this uses the 'file' module and, hence, shouldn't be
    %% run on many processes at once.
    log("[fun_common.recursive_copy]",debug),
    case filelib:is_dir(Src) of
        false ->
            log("[fun_common.recursive_copy][Src not a dir,maybe file:~p]",Src,debug),
            case file:copy(Src, Dest) of
                     {ok, _Bytes}    -> 
                         log("[fun_common.recursive_copy][file copied]",debug),
                         ok;
                     {error, enoent} -> 
                         log("[fun_common.recursive_copy][file copy fail]",error),
                         ok; %% Path doesn't exist anyway
                     {error, Err}    -> 
                         log("[fun_common.recursive_copy][copy err:~n]",[Src,Dest,Err],error),
                         {error, {Src, Dest, Err}}
                 end;
        true  -> 
            log("[fun_common.recursive_copy][Src is dir:~p]",Src,debug),
            case file:list_dir(Src) of
                     {ok, FileNames} ->
                         log("[fun_common.recursive_copy][list dir ok][Dest:~p]",Dest,debug),
                         case file:make_dir(Dest) of
                             ok ->
                                 log("[fun_common.recursive_copy][make dir ok]",debug),
                                 lists:foldl(
                                   fun (FileName, ok) ->
                                           recursive_copy(
                                             filename:join(Src, FileName),
                                             filename:join(Dest, FileName));
                                       (_FileName, Error) ->
                                           Error
                                   end, ok, FileNames);
                             {error, Err} ->
                                 case Err of 
                                     _ when Err == eexist ->
                                         log("[fun_common.recursive_copy][make dir:dir already existed]",debug),
                                         ok;
                                     _ when true ->
                                         log("[fun_common.recursive_copy][make dir error:~p]",[Src,Dest,Err],error),
                                         {error, {Src, Dest, Err}}
                                 end
                         end;
                     {error, Err} ->
                         log("[fun_common.recursive_copy][list dir err:~p]",[Src,Dest,Err],error),
                         {error, {Src, Dest, Err}}
                 end
    end.

%%--------------------------------------------------------------------
%% @doc return os type 
%% @end
%%--------------------------------------------------------------------
osType() ->
    {_,Os}=os:type(),
    case Os of
        _ when Os == freebsd ->
            freebsd;
        _ when true ->
            linux
    end.

parse_ini_file(IniFile) ->
    log("[parse_ini_file]",debug),
    IniFilename = IniFile,
    IniBin =
    case file:read_file(IniFilename) of
        {ok, IniBin0} ->
            log("[parse_ini_file:~p]",IniBin0,debug),
            IniBin0;
        {error, Reason} = Error ->
            log("Couldn't read server configuration file ~p",
                [IniFilename, file:format_error(Reason)], error),
            throw(Error)
    end,

    Lines = re:split(IniBin, "\r\n|\n|\r|\032", [{return, list}]),
    log("[parse_ini_file][line:~p]",Lines,debug),
    {_, ParsedIniValues} =
    lists:foldl(fun(Line, {AccSectionName, AccValues}) ->
            case string:strip(Line) of
            "[" ++ Rest ->
                log("[parse_ini_file][section left delimiter]",debug),
                case re:split(Rest, "\\]", [{return, list}]) of
                [NewSectionName, ""] ->
                    log("[parse_ini_file][sect hit][NewSectionName&AccValues:~p]",[NewSectionName,AccValues],debug),
                    {NewSectionName, AccValues};
                _Else -> % end bracket not at end, ignore this line
                    log("[parse_ini_file][sect miss][AccSectionName&AccValues:~p]",[AccSectionName,AccValues],debug),
                    {AccSectionName, AccValues}
                end;
            ";" ++ _Comment ->
                log("[parse_ini_file][comment:~p]",[AccSectionName,AccValues],debug),
                {AccSectionName, AccValues};
            Line2 ->
                case re:split(Line2, "\s*=\s*", [{return, list}]) of
                [Value] ->
                    log("[parse_ini_file][value:~p]",[Value],debug),
                    MultiLineValuePart = case re:run(Line, "^ \\S", []) of
                    {match, _} ->
                        true;
                    _ ->
                        false
                    end,
                    case {MultiLineValuePart, AccValues} of
                    {true, [{{_, ValueName}, PrevValue} | AccValuesRest]} ->
                        log("[parse_ini_file][MultiLineValuePart:true]",debug),
                        % remove comment
                        case re:split(Value, " ;|\t;", [{return, list}]) of
                        [[]] ->
                            % empty line
                            {AccSectionName, AccValues};
                        [LineValue | _Rest] ->
                            E = {{AccSectionName, ValueName},
                                PrevValue ++ " " ++ LineValue},
                            {AccSectionName, [E | AccValuesRest]}
                        end;
                    _ ->
                        {AccSectionName, AccValues}
                    end;
                [""|_LineValues] -> % line begins with "=", ignore
                    {AccSectionName, AccValues};
                [ValueName|LineValues] -> % yeehaw, got a line!
                    RemainingLine = mm_util:implode(LineValues, "="),
                    log("[parse_ini_file][RemainingLine:~p]",RemainingLine,debug),
                    % removes comments
                    case re:split(RemainingLine, " ;|\t;", [{return, list}]) of
                    [[]] ->
                        % empty line means delete this key
                        ets:delete(?MODULE, {AccSectionName, ValueName}),
                        {AccSectionName, AccValues};
                    [LineValue | _Rest] ->
                        log("[parse_ini_file][LineValue | _Rest][~p]",[LineValue,_Rest],debug),
                        {AccSectionName,
                            [{{AccSectionName, ValueName}, LineValue} | AccValues]}
                    end
                end
            end
        end, {"", []}, Lines),
    log("[parse_ini_file][parse ok][~p]",ParsedIniValues,debug),
    {ok, ParsedIniValues}.

get(Section) when is_binary(Section) ->
    ?MODULE:get(?b2l(Section));
get(Section) ->
    Matches = ets:match(?MODULE, {{Section, '$1'}, '$2'}),
    [{Key, Value} || [Key, Value] <- Matches].

get(Section, Key) ->
    ?MODULE:get(Section, Key, undefined).

get(Section, Key, Default) when is_binary(Section) and is_binary(Key) ->
    ?MODULE:get(?b2l(Section), ?b2l(Key), Default);
get(Section, Key, Default) ->
    case ets:lookup(?MODULE, {Section, Key}) of
        [] -> Default;
        [{_, Match}] -> Match
    end.

%% Number 需要处理的小数
%% X 要保留几位小数
%% float(8.22986, 3).
%% output: 8.230
float(Number, X) ->
    N = math:pow(10,X),
    round(Number*N)/N.
