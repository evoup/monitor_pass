# monitor api2.0 SPEC

# install

run shell install.sh

###nginx conf

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

#development
If add initial Sql, should dump them for installation:
```bash
python3 manage.py dumpdata monitor_web --indent=2 > initial_monitor_web.json
```

```bash
./manage.py dumpdata monitor_web.generalconfig > x1.json
```
