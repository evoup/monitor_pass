from google.appengine.api import memcache
from google.appengine.api import mail
import time

print 'Content-Type: text/plain'
print ''
interval_key = "interval"

m =  memcache.get("last_keepalive")
print m
iv =  memcache.get(interval_key)
print iv




# brief: decide whether a interval action will be active
def passInterval(iv_key, iv_time):
    last_time = memcache.get(iv_key)
    if last_time is None:
        memcache.set(key=iv_key, value=time.time(), time=iv_time)
        ret = True
    else:
        ret = False
    return ret



# if memcache expire, this mean keepalive mail sync fail,
#  will send warning mail!

if passInterval(interval_key, 3600) and m != "1":
    mail.send_mail(sender="someone@gmail.com",
                  to=["a@example.com","b@example.com"],
                  subject="[monitor Beta]Monitor Server Track Failed!",
                  body="""
    Dear monitor admin:

      Your monitor server keepalive with gae monitor fail.

      Please check monitor server`s DNS and mail server.

                          The madhouse architecture team
    """)

    print "Sync fail,warning mail send!"
else:
    if m != "1":
        print "Sync fail, not send alarm mail due time interval..."
    else:
        print "Sync ok."
