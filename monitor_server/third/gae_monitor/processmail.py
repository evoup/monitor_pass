from google.appengine.ext import webapp
from google.appengine.ext.webapp.util import run_wsgi_app
from google.appengine.ext.webapp.mail_handlers import InboundMailHandler
from google.appengine.api import memcache
import logging


class MailHandler(InboundMailHandler):
  def receive(self, msg):
    #logging.info(msg.subject)
    #logging.info(msg.sender)
    #logging.info(msg.to)
    #logging.info(msg.cc)
    #logging.info(msg.date)
    #logging.info(msg.bodies(content_type='text/plain'))
    #logging.info(msg.bodies(content_type='text/html'))
    #logging.info(msg.attachments)
    #if msg.subject == "KeepAlive":
    #is keepalive mail, then update memcache, do not use add method!
    memcache.set(key="last_keepalive", value="1", time=900)

application = webapp.WSGIApplication([('/_ah/mail/.+', MailHandler)])

def main():
  run_wsgi_app(application)

if __name__ == '__main__':
  main()
