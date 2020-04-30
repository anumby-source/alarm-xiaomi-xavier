import requests 
import time
import os
import subprocess
from datetime import datetime
import sqlite3

# ---------------------------------------------------------------------- #
# FUNCTIONS
def fldb_open(db_file):
	tt=sqlite3.connect(db_file)
	#sqlite3.busyTimeout(5000)
	return tt
	
def fldb_close(hDB):
	hDB.close()
       
def fldb_get(hDB,sql):
	if iDEBUG>=3:
		print("SELECT =>"+sql)
	c = hDB.cursor()
	
	c.execute(sql)
	rows = c.fetchall()
	return rows
#end fldb_get
	
def fldb_put(hDB,sql):
	if iDEBUG>=3:
		print("INSERT ta_actions =>"+sql)
	c = hDB.cursor()
	c.execute(sql)
	hDB.commit()
#end fldb_put

def fl_MkAction_alarmSummaryKO():
	print("\n> -------------------------------------------------------")
	print(">fl_MkAction_alarmSummaryKO : Search alarmSummaryKO");
	r = requests.get(url)
	i=0;
	for _line in r.json():  
		_zone=_line[0]
		_onoff=_line[1]
		_count=_line[2]
		
		print("\n> Prepare Action alarmSummaryKO n°",i,": [ zone="+_zone+ " / onoff="+_onoff+" / count=",_count,"]")
		
		if _onoff=="OFF":
			print("skip OFFFFFFF : _onoff="+_onoff)
		else:
			if _count==0:
				print("OK : _onoff="+_onoff+" / _count=0")
			else:
				print("ALARMMMM : onoff="+_onoff+" & _count=",_count)
				rows=fldb_get(db_handler,"SELECT count(*) from ta_actions where type='start-alarm' and etat='wait'")
				if rows[0][0]==0:
					print(" :: insert ta_actions 'start-alarm'")
					fldb_put(db_handler,"insert into ta_actions(type,name,value,etat) values ('start-alarm','mp3','assets/chien-aboie.mp3','wait')")
					fldb_put(db_handler,"insert into ta_actions(type,name,value,etat) values ('start-alarm','prise','on','wait')")
					fldb_put(db_handler,"insert into ta_actions(type,name,value,etat) values ('start-alarm','sleep',DATETIME(CURRENT_TIMESTAMP, '+20 seconds'),'wait')")
					fldb_put(db_handler,"insert into ta_actions(type,name,value,etat) values ('start-alarm','prise','off','wait')")
					fldb_put(db_handler,"insert into ta_actions(type,name,value,etat) values ('start-alarm','sleep',DATETIME(CURRENT_TIMESTAMP, '+60 seconds'),'wait')")
					fldb_put(db_handler,"insert into ta_actions(type,name,value,etat) values ('start-alarm','delete','start-alarm','wait')")
				else:
					print("skip 'insert into ta_actions' where type='start-alarm' ==> count(*)="+str(rows[0][0]))
		
		i=i+1
#end fl_MkAction_alarmSummaryKO

def fl_MkAction_button():
	print("\n> -------------------------------------------------------")
	print(">fl_MkAction_button : verif button");
	
	# recherche si bouton pressé dans le temps imparti
	#DEBUG//rows=fldb_get(db_handler,"SELECT count(*) from ta_devices where type='bouton'")
	rows=fldb_get(db_handler,"select count(*) from ta_devices where type='bouton' and dateLimit > CURRENT_TIMESTAMP;")
	if rows[0][0]==0:
		print(" :: skip no button pressed")
	else:
		print(" :: button pressed")
		# insert seulement si pas d'action en cours
		rows=fldb_get(db_handler,"SELECT count(*) from ta_actions where type='stop-alarm' and etat='wait'")
		if rows[0][0]==0:
			print(" :: insert ta_actions 'end-alarm'")
			fldb_put(db_handler,"insert into ta_actions(type,name,value,etat) values ('stop-alarm','delete','start-alarm','wait')")
			fldb_put(db_handler,"insert into ta_actions(type,name,value,etat) values ('stop-alarm','ta_configs','etat-alarme-OFF','wait')")
			fldb_put(db_handler,"insert into ta_actions(type,name,value,etat) values ('stop-alarm','prise','off','wait')")
			fldb_put(db_handler,"insert into ta_actions(type,name,value,etat) values ('stop-alarm','mp3','assets/ding-dong.mp3','wait')")
			fldb_put(db_handler,"insert into ta_actions(type,name,value,etat) values ('stop-alarm','delete','stop-alarme','wait')")
		else:
			print("skip 'insert into ta_actions' where type='end-alarm' ==> count(*)="+str(rows[0][0]))
					
	
	
#end fl_MkAction_button	
	
def fl_DoActions(ptype,pflag):
	print("\n> -------------------------------------------------------")
	print(">fl_DoActions("+ptype+","+pflag+"): Search actions groups");
	
	if pflag=="ONE":
		rows=fldb_get(db_handler,"select * from ta_actions where type='"+ptype+"' and etat='wait' group by type having min(rowid)")
	else:	#pflag==ALL
		rows=fldb_get(db_handler,"select * from ta_actions where type='"+ptype+"' and etat='wait'")
	
	for _line in rows:   
		_id=_line[0]
		_name=_line[1]
		_type=_line[2]
		_value=_line[3]
		_etat=_line[4]
		
		print("\nLine["+str(_id)+"]=",_line)	
		
		if _type=="mp3":
			print(" ::action :: mp3 =>"+_value)
			os.system("cvlc --play-and-exit "+_value)
			fldb_put(db_handler,"update ta_actions set etat='end' where id="+str(_id))
			
		elif _type=="sleep":
			print(" ::action :: "+_type+" =>"+_value)
			rows=fldb_get(db_handler,"select count(*),CURRENT_TIMESTAMP,value from ta_actions where id="+str(_id)+" and name='sleep' and value < CURRENT_TIMESTAMP")
			print(rows);
			if rows[0][0]>0:
				print(" ::  resultat : ouiiiiiiiii on passe à la suite")
				fldb_put(db_handler,"update ta_actions set etat='end' where id="+str(_id))
			else:
				print(" ::  resultat : on attend encore jusqu'a "+_value)
		
		elif _type=="prise":
			_cmd="mosquitto_pub -t 'zigbee2mqtt/0x00158d0003934c9f/set' -m '{\"state\":\""+_value+"\"}'"
			print(_cmd)
			rc=os.system(_cmd)
			fldb_put(db_handler,"update ta_actions set etat='end' where id="+str(_id))
		
		elif _type=="delete":
			print(" ::action :: delete => name='"+_value+"'")
			print("DEBUG::delete from ta_actions where type='"+_value+"';")
			fldb_put(db_handler,"delete from ta_actions where type='"+_value+"';")
		
			fldb_put(db_handler,"update ta_actions set etat='end' where id="+str(_id))

		elif _type=="ta_configs":
			print(" ::action :: update ta_configs =>'"+_value+"'")
			if _value=="etat-alarme-OFF":
				_sql_="update ta_configs set value='OFF' where type='etat-alarme';"
				print("DEBUG::"+_sql_)
				fldb_put(db_handler,_sql_)
			else:
				print(" :: ERROR :: value incorrect sur ta_configs action")
				
			fldb_put(db_handler,"update ta_actions set etat='end' where id="+str(_id))
		
		else :
			print("::action inconnnnnuuuuuuuuuuuuuuuuu ="+_type)
#end fl_DoActions:
		
# ------------------------------------------------------------------------------------
iDEBUG=0

rc,RetMyIP = subprocess.getstatusoutput("hostname -I|awk '{print $1}'")
url = "http://"+RetMyIP+":8080/phpXleAlarm/ajax-getInfo.php?action=alarmSummaryKO"
print ("url: "+url)

db_sqlite3="sqliteAlarm.db"
print("sqlite3="+db_sqlite3);

db_handler=""


while 1==1:
	db_handler=fldb_open(db_sqlite3)
	print("\n# ==================================================================== #")
	
	now = datetime.now() # current date and time
	print("\n> --------------------------------------------------------------------")
	print("> --- Creation des actions --- (",now.strftime("%m/%d/%Y, %H:%M:%S"),") -----------------");
	fl_MkAction_alarmSummaryKO()	#=> start-alarm
	fl_MkAction_button()			#=> stop-alarm
			
	now = datetime.now() # current date and time
	print("\n> --------------------------------------------------------------------")
	print("> --- Gestion des actions --- (",now.strftime("%m/%d/%Y, %H:%M:%S"),") -----------------");
	fl_DoActions('stop-alarm','ALL')
	fl_DoActions('start-alarm','ONE')
	
	
	print("...")
	fldb_close(db_handler)
	time.sleep(5)
#while true



