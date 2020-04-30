#!/bin/bash

. ../phpXleAlarm/xle_alarmConfig.env

echo "select * from ta_devices where type='prise';" | sqlite3 ${PRJ_DIR}/${SQLITE_BASE}

[ "$#" -ne 2 ] && echo "usage ./$0 <device> <ON|OFF>" && exit 254

echo "$2" | egrep -e 'ON|OFF' 1>/dev/null
[ "$?" -ne 0 ] && echo "usage ./$0 <device> <true|false>" && exit 254

mosquitto_pub -t "zigbee2mqtt/$1/set" -m "{\"state\":\"$2\"}"

echo "send 'zigbee2mqtt/$1/set' => $0 => $2"

