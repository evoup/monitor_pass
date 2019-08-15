#!/bin/bash
BASEDIR="$( cd "$(dirname "$0")" ; pwd -P )"
python3=$BASEDIR/venv/bin/python3
source $BASEDIR/venv/bin/activate
rm -f $BASEDIR/monitor.sqlite3
export PYTHONUNBUFFERED=1
export DJANGO_SETTINGS_MODULE=web.cop_settings
python3 manage.py makemigrations monitor_web
python3 manage.py migrate
python3 manage.py syncdb --noinput
echo "from django.contrib.auth.models import User; User.objects.create_superuser('admin', 'admin@example.com', 'password')" | python3 manage.py shell
python3 manage.py loaddata initial_monitor_web.json
