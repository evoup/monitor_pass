#docker build -t nginx-monitor-ui2 .
# docker run -itd --name nginx-monitor-ui2 -pxxxx:80 -v /home/evoup/projects/gitProjects/monitor_pass/monitor_ui2:/usr/share/nginx/html nginx-monitor-ui2
FROM nginx
RUN rm /etc/nginx/conf.d/default.conf
COPY files/default.conf /etc/nginx/conf.d/default.conf 
