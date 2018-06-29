datasource of trigger.csv is:
    use zabbix;
    select t.*, i.hostid 
    from triggers t,items i,functions f 
    where t.triggerid=f.triggerid and f.itemid=i.itemid;
