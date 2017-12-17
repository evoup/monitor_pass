-define(b2l(V), binary_to_list(V)).
-record(const,
  {log_suffix="madmonitor2",
   svn_version="1783",
   proc_basedir="/services",
   proc_root="/services/monitor2_deal/",
   conf_file="madmonitor.ini",
   run_subpath="run/",
   conf_subpath="conf/",
   status_subpath="status/",
   work_subpath="work/",
   proc_life=3600,
   logtag_read="MadRead",
   logtag_deliver="MadDeliver",
   logtag_pf="pf_monitor",
   logtag_log="access_monitor"}).

-record(confInfo,
    {server_name="",
     sleep=5000,
     upload_url="",
     upload_host="",
     upload_port="",
     upload_version="",
     upload_suffix=""
    }).

-record(ifStat,
    {flow_interface="",
     flow_in="",
     flow_out="",
     flow_catch_time=""
    }).
%-record(ifFlow,
    %{flow_interface="",
     %caculated_ifin="",
     %caculated_ifout=""
    %}).

-define(basetb, base_table). %base configuration file table
-define(disktb, disk_table). %disk table
-define(diskiostattb, disk_iostat_table). %iostat table
-define(ifstattb, interface_table). %interface table
-define(ifflowtb, flow_table). %flow table
-define(servicetb, service_table). %service table
-define(colltb, collect_table). %collect monitor data table
