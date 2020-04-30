#!/bin/bash
# 2020/04/24 : reecriture & fusion start/Stop/test
# 2020/04/14 : creation

[ "$#" -ne 1 ] && echo "usage : $0 <start|stop|test>" && exit 0

_ret=0
_action_=$1

echo "1 exec.phpServer.sh 5
2 exec.xleAlarmHub.sh 5
3 exec.dockerstart.sh 5
4 exec.xlePoliceMan.sh 1" >$0.lst

sudo date

echo "$0 $_action_ : $(date)"
echo "-------------------------------------"
if [ "${_action_}" = "start" ]
then
	cat $0.lst | sort | while read _order _cmd _time NULL
	do
		echo ">$_order/ $_cmd : $_action_ (wait $_time seconds)" 
		nohup ./$_cmd $_action_ &
		sleep $_time
		echo ""
	done
elif [ "${_action_}" = "stop" ]
then
	cat $0.lst | sort -r | while read _order _cmd NULL
	do
		echo ">$_order/ $_cmd : $_action_" 
		./$_cmd $_action_
		echo ""
	done
elif [ "${_action_}" = "test" ]
then
	cat $0.lst | sort | while read _order _cmd NULL
	do
		echo ">$_order/ $_cmd : $_action_" 
		./$_cmd $_action_
		echo ""
	done
else
	echo "Action inconnu...."
fi
