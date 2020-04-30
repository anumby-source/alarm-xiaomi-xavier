#!/bin/bash

[ "$#" -ne 1 ] && echo "usage : $0 <start|stop|test>" && exit 0

_ret=0
_action_=$1

echo "$0 $_action_ : $(date)"
if [ "${_action_}" = "start" ]
then
	rm $0.log
	echo "start log: $0.log (pid:$$)"
	echo $$ >$0.pid
	exec 1>$0.log 2>&1

	cd phpXleAlarm
	sudo dockerstart_mqtt.sh 

elif [ "${_action_}" = "stop" ]
then
	__psdocker=$(sudo docker ps | grep 'koenkk/zigbee2mqtt' | awk '{print $1}')
	sudo docker stop $__psdocker 
	
elif [ "${_action_}" = "test" ]
then
	if [ ! -f $0.pid ]
	then
		echo "Process arreté...."
		_ret=254
	else
		sudo docker ps | grep 'koenkk/zigbee2mqtt'
		_ret=$?
	fi

else
	echo "Action inconnu...."
fi
exit $_ret
