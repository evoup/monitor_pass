#!/bin/bash
#BASEDIR="$( cd "$(dirname "$0")" ; pwd -P )"
#echo $BASEDIR
#python3=$BASEDIR/venv/bin/python3

#python3 manage.py sqlflush | python3 manage.py dbshell
python3 manage.py makemigrations monitor_web
python3 manage.py migrate
python3 manage.py syncdb --noinput
echo "from django.contrib.auth.models import User; User.objects.create_superuser('admin', 'admin@example.com', 'password')" | python3 manage.py shell
python3 manage.py loaddata initial_monitor_web.json
