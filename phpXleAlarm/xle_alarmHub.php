<?php

require("xle_alarmConfig.php");
require("lib/phpMQTT.php");

$DEBUG=1;

$db = new SQLite3($SQLITE_BASE);
$version = $db->querySingle('SELECT SQLITE_VERSION()');
echo "\nSQLite3 :: open '$SQLITE_BASE' ===>$version";
$db->close();

$mqtt = new phpMQTT($server, $port, $client_id);
if(!$mqtt->connect(true, NULL, $username, $password)) { exit(1); }
echo "\n MQTT : connected";

$topics['#'] = array("qos" => 0, "function" => "procmsg");
$mqtt->subscribe($topics, 0);
echo "\n MQTT : subscribe '#' topics";

echo "\n MAIN: started.....";
while($mqtt->proc()){		
}

$mqtt->close();

// ---------------------------------------------------------------------------
// ---------------------------------------------------------------------------
// ---------------------------------------------------------------------------

function procmsg($topic, $msg){
	global $DEBUG,$db,$SQLITE_BASE;
	$db = new SQLite3($SQLITE_BASE);
	$db->busyTimeout(5000);

	echo "\n\n# --------------------------------------------------------------------------------------- #";
	echo "\n# ---- ". date("r")." ---------------------------------------------------------- #";
	echo "\nMsg Recieved: {$topic} \n\t$msg\n";
	
	$_tabId = explode("/", $topic);
	$_id=$_tabId[1];
	
	$_sql="select * from ta_devices where device='$_id'";
	$res = $db->query($_sql);
	$row = $res->fetchArray();
	
	if(empty($row)) { $_name='name_inconnu'; $_type='type_inconnu';}
	else { $_name=$row['name'];	$_type=$row['type']; }
	echo "\nSEARCH :: ta_devices : $_id / $_name / $_type";

	// -- INSERT _TA_EVENTS
	switch ($_id) {
		case "bridge":
				echo "\nSKIP:: insert TA_EVENTS";
			break;
		default:
				echo "\n:: insert into TA_EVENTS(device,message) values('$_id','$msg');"; 
				$db->exec("insert into TA_EVENTS(device,message) values('$_id','$msg');");
			break;
	}
	
	// -- INSERT _TA_DEVICES
	$tab=json_decode($msg);
	if ($DEBUG >= 1) { 	echo "\n"; print_r($tab); }
	
	$ETAT="";
	switch ($_type) {
		case "bouton":
				$LIMITTIME="+10 seconds";
				$ETAT=$tab->click;
				//update ta_configs set value="OFF" where name="zone-move";

			break;
		case "detect-open":
				$LIMITTIME="+60 minutes";
				if ($tab->contact==1) { $ETAT="ok";}
				elseif ($tab->contact=="") { $ETAT="ko";}
				else { $ETAT="??"; }
			break;
		case "detect-move":
				$LIMITTIME="+60 minutes";
				if ($tab->occupancy==1) { $ETAT="ko";}
				elseif ($tab->occupancy=="") { $ETAT="ok";}
				else { $ETAT="??"; }
			break;
		case "prise":
				$LIMITTIME="+1 seconds";
				if ($tab->state=="ON") { $ETAT="on";}
				elseif ($tab->state=="OFF") { $ETAT="off";}
				else { $ETAT="??"; }
			break;
		default:
				$LIMITTIME="+1 seconds";
				echo "\nWARNING:: type '{$topic}' inconnu dans ta_devices et xle_alarmHub.php";
				$ETAT='??';
			break;
	}

	if ($ETAT!="") { 
			$_sql="INSERT OR REPLACE into TA_DEVICES(device,name,type,etat,dateUpdate,dateLimit) values('$_id','$_name','$_type','$ETAT',CURRENT_TIMESTAMP,DATETIME(CURRENT_TIMESTAMP, '$LIMITTIME'));";
			echo "\n:: $_sql"; 
			$db->exec($_sql);
	}
	
	$db->close();
	
}
