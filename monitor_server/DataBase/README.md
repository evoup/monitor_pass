datasource of trigger.csv is:
  use zabbix;
  select t.*, i.hostid 
  from triggers t,items i,functions f 
  where t.triggerid=f.triggerid and f.itemid=i.itemid;


datasource of functions.csv is:
  use zabbix;
  select f.functionid,f.itemid,f.triggerid,f.function,f.parameter,i.key_ from functions f,items i,triggers t 
  where t.triggerid=f.triggerid and f.itemid=i.itemid and f.functionid in (select functionid from functions group by functionid);
