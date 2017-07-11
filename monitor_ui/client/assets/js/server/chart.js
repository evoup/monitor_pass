function lineChart(){
    //Tabs();  
    var serverId = urlParams("name"), loadAverage_data, tcp_data;
    //console.log(serverId);
    Highcharts.setOptions({
        global: {
            useUTC: false
        }
    });
    
    var chart_load, chart_tcp;
    chart_load = new Highcharts.Chart({
        chart: {
            /*backgroundColor: {//渐变背景
             linearGradient: [0, 0, 0, 200],
             stops: [
             [0, 'rgb(96, 96, 96)'],
             [1, 'rgb(16, 16, 16)']
             ]
             },*/
            renderTo: 'container_loadAverage',
            defaultSeriesType: 'spline',
            marginRight: 10,
            events: {
                load: function(){
                    // set up the updating of the chart each second
                    var series = this.series[0];
                    loadAverage();
                    setInterval(function(){
                        loadAverage();
                        var x = (new Date()).getTime(), // current time
 y = loadAverage_data;
                        //y=1;
                        //console.log(y);
                        series.addPoint([x, y], true, true);
                    }, 5000);
                }
            }
        },
        credits: { //highchart logo
            enabled: false //display:none
        },
        title: {
            text: 'System Load Average(1 minute)'
        },
        xAxis: {
            type: 'datetime',
            tickPixelInterval: 150
        },
        yAxis: {
            title: {
                text: ''
            },
            // max:4,
            /*plotLines: [{
             value: 0,
             width: 1,
             color: '#808080'
             }]*/
        },
        tooltip: {
            formatter: function(){
                return '<b>' + this.series.name + '</b><br/>' +
                Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', this.x) +
                '<br/>' +
                Highcharts.numberFormat(this.y, 2);
            }
        },
        legend: {
            enabled: false
        },
        exporting: {
            enabled: false
        },
        series: [{
            name: 'Load Average Data',
            data: (function(){
                // generate an array of random data
                var data = [], time = (new Date()).getTime(), i;
                for (i = -19; i <= 0; i++) {
                    data.push({
                        x: time + i * 5000,
                        y: 0
                    });
                }
                return data;
            })()
        }]
    });
    function loadAverage(){
        var ver = urlParams("version");
        var domain = domainURI(document.location.href);
        $.ajax({
            type: "get",
            url: "http://" + domain + "/mmsapi" + ver + "/get/server/@self_detail/" + serverId,
            async: true,
            dataType: "json",
            success: function(json, textStatus, jqXHR){//如果调用php成功
                if (json == null) {
                    console.log("json null");
                }
                else {
                    $.each(json, function(key, value){
                        if (key == "Load Average") {
                            loadAverage_data = value * 1;
                            //console.log(typeof(loadAverage_data));
                        }
                    });
                }
            },
            error: function(jqXHR, textStatus, errorThrown){
                console.log("error");
            }
        });
    }
    
    
    
    chart_load = new Highcharts.Chart({
        colors: ['#889E2D', '#AA4643', '#89A54E', '#80699B', '#3D96AE', '#DB843D', '#92A8CD', '#A47D7C', '#B5CA92'],
        chart: {
            renderTo: 'container_tcp',
            defaultSeriesType: 'spline',
            marginRight: 10,
            events: {
                load: function(){
                    // set up the updating of the chart each second
                    var series = this.series[0];
                    Tcp();
                    setInterval(function(){
                        Tcp();
                        var x = (new Date()).getTime(), // current time
 y = tcp_data;
                        //y=1;
                        // console.log(y+",tcp");
                        series.addPoint([x, y], true, true);
                    }, 5000);
                }
            }
        },
        credits: {
            enabled: false
        },
        title: {
            text: 'TCP连接数',
            style: {
                color: '#889E2D'
            }
        },
        xAxis: {
            type: 'datetime',
            tickPixelInterval: 150,
        },
        yAxis: {
            title: {
                text: '',
                style: {
                    color: '#889E2D'
                }
            },
            /*min:0,
             max:4000,*/
        },
        tooltip: {
            formatter: function(){
                return '<b>' + this.series.name + '</b><br/>' +
                Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', this.x) +
                '<br/>' +
                Highcharts.numberFormat(this.y, 2);
            }
        },
        legend: {
            enabled: false
        },
        exporting: {
            enabled: false
        },
        series: [{
            name: 'TCP Data',
            data: (function(){
                // generate an array of random data
                var data = [], time = (new Date()).getTime(), i;
                for (i = -19; i <= 0; i++) {
                    data.push({
                        x: time + i * 5000,
                        y: 0
                    });
                }
                return data;
            })()
        }]
    });
    function Tcp(ver){
        var ver = urlParams("version");
        var domain = domainURI(document.location.href);
        $.ajax({
            type: "get",
            url: "http://" + domain + "/mmsapi" + ver + "/get/server/@self_detail/" + serverId,
            async: true,
            dataType: "json",
            success: function(json, textStatus, jqXHR){//如果调用php成功
                if (json == null) {
                    console.log("json null");
                }
                else {
                    $.each(json, function(key, value){
                        if (key == "TCP连接数") {
                            tcp_data = value * 1;
                            //console.log(typeof(loadAverage_data));
                        }
                    });
                }
            },
            error: function(jqXHR, textStatus, errorThrown){
                console.log("error");
            }
        });
    }
}
