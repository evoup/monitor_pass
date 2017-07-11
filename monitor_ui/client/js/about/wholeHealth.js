 var hostChart,eventChart;
 function getHostSuccessCallback(json,textStatus,jqXHR){
   if(json==null){
      console.log("json null");
    }else{
      //console.log(json);
			var down=0,online=0,unmonitored=0,num,perDown,perUp;
      $.each(json,function(key,value){
				$.each(value,function(i){
					 if(i==0){
					   switch(value[i]){
					     case "0" : ++down;break; 
					     case "1" : ++online;break;
						   case "2" : ++online;break;
					     case "3" : ++online;break;
						   case "4" : ++online;break;
						   case "5" : ++unmonitored;break;
					  }
					}
				 });
			});
			//console.log(down+","+online);
      $(".data p:first cite:first").text(online);
			$(".data p:first cite:last").text(down);
			
			num=down+online;
      perDown=parseFloat(down/num);
      perUp=parseFloat(online/num);
      
      //console.log(perDown+","+perUp);
      hostChart = new Highcharts.Chart({
          chart: {
            renderTo: 'container',
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false
          },
          credits: {
            enabled:false
          },
          title: {
            text: '主机'
          },
          tooltip: {
            formatter: function() {
              return '<b>'+ this.point.name +'</b>: '+ (this.y).toFixed(2) +' %';
            }
          },
          plotOptions: {
            pie: {
							allowPointSelect: true,
              cursor: 'pointer',
							dataLabels: {
								enabled: false,
								/*connectorWidth:1,
								color: '#000000',
								connectorColor: '#000000',
								formatter: function() {
                  return '<b>'+ this.point.name +'</b>: '+ this.y +' %';
                }*/
              },
							showInLegend:true
            }
          },
          series: [{
            type: 'pie',
            name: 'Browser share',
            data: [
                   { 
                    name:'down',
                    y:(perDown*100).toFixed(2)*1,
                    color:'#C35F5C'

                   },
                  // ['up', (online/num).toFixed(2)*100]
                   {
                     name: 'up',
                     y: (perUp*100).toFixed(2)*1,
                    // sliced: true,
                    // selected: true,
                     color: '#89A54E'
                   }
                  ] 
          }]
        });
  
		}

  }

 function getEventSucessCallback(json,textStatus,jqXHR){
   if(json==null){
      console.log("json null");
    }else{
      //console.log(json);
      var perWarning=0,perOk=0,perCaution=0,num;
     
      $(".data p:last cite:first").text(json.ok);
			$(".data p:last :nth-child(2)").text(json.caution);
      $(".data p:last cite:last").text(json.warning);

			num=json.warning+json.ok+json.caution;
      perWarning=parseFloat(json.warning/num);
      perOk=parseFloat(json.ok/num);
      perCaution=parseFloat(json.caution/num);

      eventChart = new Highcharts.Chart({
          chart: {
            renderTo: 'container2',
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false
          },
          credits: {
            enabled:false
          },
          title: {
            text: '事件'
          },
          tooltip: {
            formatter: function() {
              return '<b>'+ this.point.name +'</b>: '+ (this.y).toFixed(2) +' %';
            }
          },
          plotOptions: {
            pie: {
              allowPointSelect: true,
              cursor: 'pointer',
              dataLabels: {
                enabled: false,
                /*connectorWidth:1,
								color: '#000000',
                connectorColor: '#000000',
                formatter: function() {
                  return '<b>'+ this.point.name +'</b>: '+ this.y +' %';
                },*/
								style:'margin-bottom:10px;'
              },
							showInLegend:true //颜色对应的内容
            }
          },
           series: [{
           type: 'pie',
           name: 'Browser share',
           data: [
               {
                name: 'warning',
                y: (perWarning*100).toFixed(2)*1,
                color:'#C35F5C'
              },
              {
                name: 'ok',
                y: (perOk*100).toFixed(2)*1, //number
               // sliced: true,
               // selected: true,
                color:'#89A54E'
              },
              {
                name: 'caution',
                y: (perCaution*100).toFixed(2)*1,
                color:'#F6F826'
              }
            ]
          }]
        });
 

   }


 }


 $(document).ready(function() {
	  var ver=url_params("version");
		var domain=domainURI(document.location.href);
    get_data_notLoad("http://"+domain+"/mmsapi"+ver+"/get/server/@all",getHostSuccessCallback);

    get_data_notLoad("http://"+domain+"/mmsapi"+ver+"/get/status/@eventsummary",getEventSucessCallback);	
});
				
