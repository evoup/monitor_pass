var confirmCheckmonitorItem=function(){
  var checkNothing=true;
  $("input[type='radio']").each(function(){
    if($(this).attr("checked")==true){
      checkNothing=false;
    }
  })
  if (checkNothing) {
    alert('请选择一个监控类别');
  } else {
    $("input[name='version']").val(url_params('version'));
    $("form[id='wizard_form_0']").submit();
  }
}

$(document).ready(function(){
  var sel=url_params("monitorCls");
  console.log('sel:'+sel);
  if (sel>=0 && sel<=12) {
    $("input[type=radio][value="+sel+"]").attr("checked",'checked'); 
  } else {
    $("input[type=radio][value=0]").attr("checked",'checked');
  }
  //$("#wizard_form_0").action='monitorWizardStep2.html';
  //{{{ 调整向导选择框大小
  var notIframeHeight=$("#header").height()+$("#top_navigation").height()+$("#footer").height()+30;
  iframeHeight=$(window).height()-notIframeHeight;
  console.log(iframeHeight);
  var setHeight=iframeHeight-200;
  console.log(setHeight);
  $(".monitorWizardList").height(setHeight);
  //}}}
});
