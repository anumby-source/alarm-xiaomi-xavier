#!/bin/bash

. ../phpXleAlarm/xle_alarmConfig.env

echo "select * from ta_devices where type='detect-open';" | sqlite3 ${PRJ_DIR}/${SQLITE_BASE}


[ "$#" -ne 2 ] && echo "usage ./$0 <topic> <true|false>" && exit 254

echo "$2" | egrep -e 'true|false' 1>/dev/null
[ "$?" -ne 0 ] && echo "usage ./$0 <topic> <true|false>" && exit 254

mosquitto_pub -t "zigbee2mqtt/$1" -m "{\"battery\":100,\"voltage\":3025,\"contact\":$2,\"linkquality\":162}"

echo "send 'zigbee2mqtt/$1' => $0 => $2"

