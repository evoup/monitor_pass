# monitor web api2.0 SPEC

# enviorment
python3.7.4, should update pip to newest

# install

run shell install.sh

### nginx conf

add default to /etc/nginx/sites-available

context is:
```
        location /mmsapi2.0/ {                                                                                                                                      
            proxy_set_header X-Real-IP $remote_addr;                                                                    
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;                                                
            proxy_set_header Host  $http_host;                                                                          
            proxy_set_header X-Nginx-Proxy true;                                                                        
            proxy_set_header Connection "";                                                                             
            proxy_pass   http://localhost:8000;                                                                         
            proxy_redirect default ;                                                                                    
        }
```

change nginx max upload config, add this in http context:
```bash
client_max_body_size 500m; 
```

# deploy 

deploy celery
```bash
export DJANGO_SETTINGS_MODULE=web.deploy_settings && celery worker -A web -l debug
```
or

```bash
export DJANGO_SETTINGS_MODULE=web.deploy_settings && celery worker -A web --concurrency=4 --hostname=worker@%h -l info
```


# development
If add initial Sql, should dump them for installation:
```bash
python3 manage.py dumpdata monitor_web --indent=2 > initial_monitor_web.json
```

```bash
./manage.py dumpdata monitor_web.generalconfig > x1.json
```
