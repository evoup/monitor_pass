//function graph(hostname) {
  //$('<iframe>', {
    //src: 'http://localhost:3000/d-solo/FEDkgTHmz/jian-kong-shu-ju-zhi-biao?panelId=2&orgId=1&var-group=apps&var-endpoint=backend&var-host=evoupzhanqi&from=1529417531489&to=1529421131489&tab=general',
    //id:  'myFrame',
    //frameborder: 0,
    //width:'80%',
    //height:'200px',
    //scrolling: 'no'
  //}).appendTo('#container_loadAverage');
//}
  function getLoad() {
  host=$("#serverName").text()
  host=host.replace('-','')
  host=host.replace(/[-]+/g,'')
  now=new Date().getTime()
  ahourago=now-3600000
  src="http://localhost:8004/grafana/d-solo/FEDkgTHmz/jian-kong-shu-ju-zhi-biao?orgId=1&panelId=2&from="+ahourago+"&to="+now+"&var-group=apps&var-endpoint=backend&var-host="+host
  $("#iframe0").attr('src',src)
  }
