#!/bin/bash

[ "$#" -ne 1 ] && echo "usage : $0 <start|stop|test>" && exit 0

_ret=0
_action_=$1

echo "$0 $_action_ : $(date)"
if [ "${_action_}" = "start" ]
then
	echo "start log: $0.log (pid:$$)"
	echo $$ >$0.pid
	exec 1>$0.log 2>&1

	_adresse=$(hostname -I | awk '{print $1}')
	echo "start .... serveur php '${_adresse}:8080' documentRoot='$PWD'"
	php -t $PWD -S ${_adresse}:8080

elif [ "${_action_}" = "stop" ]
then
	pkill -P "$(cat $0.pid)"
	
elif [ "${_action_}" = "test" ]
then
	if [ ! -f $0.pid ]
	then
		echo "Process arreté...."
		_ret=254
	else
		ps -auxx | grep "$(cat $0.pid)"
		_ret=$?
	fi

else
	echo "Action inconnu...."
fi
exit $_ret
