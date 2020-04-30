#!/bin/bash

[ "$#" -ne 1 ] && echo "usage : $0 <to_pi|to_pc>" && exit 0

_ret=0
_action_=$1

__date=$(date '+%Y-%m-%d_%H.%M.%S')

echo "$0 $_action_ : $(date)"
if [ "${_action_}" = "to_pi" ]
then
	echo "action : to pi"
	ssh pi@192.168.1.98 "tar cvf prj_alarm.${__date}.tar prj_alarm"
	rsync -rv . pi@192.168.1.98:prj_alarm/
	
elif [ "${_action_}" = "to_pc" ]
then
	echo "action : to pc"
	
	tar cvf ../prj_alarm.${__date}.tar .
	rsync -rv pi@192.168.1.98:prj_alarm/ .
	
else
	echo "Action inconnu...."
fi
exit $_ret
