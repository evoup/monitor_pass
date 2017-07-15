<?php
/*
  +----------------------------------------------------------------------+
  | Name:往gae发送的心跳邮件
  +----------------------------------------------------------------------+
  | Comment:
  +----------------------------------------------------------------------+
  | Author:Evoup     evoex@126.com                                                     
  +----------------------------------------------------------------------+
  | Create:
  +----------------------------------------------------------------------+
  | Last-Modified: 2011-11-18 13:19:27
  +----------------------------------------------------------------------+
 */

$from = "monitoradmin@madhouse-inc.com";
//$to = $cc = (array)"yinjia@madhouse-inc.com";
$to = $cc = (array)"monitor@evoex789beta.appspotmail.com";
$subject = "KeepAlive";
$message = "a keepalive mail";
// To send HTML mail, the Content-type header must be set
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

// Additional headers
//       $headers .= 'To: ' . implode(", ", $to) . "\r\n";
$headers .= 'From: MINITOR ALARM KEEPALIVE<' . $from . ">\r\n";
$headers .= 'Cc: ' . implode(", ", $cc) . "\r\n";

if (mail(implode(", ", $to), "Mobile AD Solution Service - " . $subject, $message, $headers)) {
    echo "mail send";
} else {
    echo "mail send failed";
}

?>
