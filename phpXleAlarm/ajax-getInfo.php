<?php
$DEBUG=0;
//$DEBUG=1;

$action=$_GET['action'];
if ($DEBUG >= 1) echo "=== {$_GET['action']}=$action ===";

require("xle_alarmConfig.php");

if ($DEBUG >= 1) echo "SQLite3 : open '$SQLITE_BASE'";
$db = new SQLite3($SQLITE_BASE);
$db->busyTimeout(5000);
$version = $db->querySingle('SELECT SQLITE_VERSION()');
if ($DEBUG >= 1) echo "===>",$version . "\n";

$tarray=array();


// --------------------------------------------------
// ---- REQUETES -------------------
// --------------------------------------------------
switch($action) {
	case "alarmDevicesKO" :
			// tcON =>ta_configs (etat-alarm=ON) 
			// tcListDev=>ta_configs (list des devices associés etat=ON)
			// tdevices ==> liste des infos des devices
			$_sql="select tcON.type as tc1type,tcON.value,tcON.name,tcListDev.type as tc2type, tdevices.type as tdtype ,tdevices.name as tdname,tdevices.etat,tdevices.dateUpdate, tdevices.dateLimit from ta_configs tcON, ta_configs tcListDev, ta_devices tdevices 
where tcON.type='etat-alarme' and tcON.value='ON' 
  and tcON.name=tcListDev.name and tcListDev.type='zone' 
  and tcListDev.value=tdevices.name and tdevices.etat='ko';";
		break;
		
	case "alarmSummaryKO" :
			$_sql="select tc.name,tc.value,
	( select count(*) from ta_configs tc2, ta_devices td where  tc.name=tc2.name and td.name=tc2.value and td.etat='ko' ) 
from ta_configs tc where type='etat-alarme' group by name;";
		break;
		
	case "lastEvents" :
			$_sql="SELECT * from ta_events order by id desc limit 10;";
		break;
		
	case "etatDevices" :
		$_sql="SELECT CASE  
           WHEN dateLimit >  CURRENT_TIMESTAMP
               THEN etat 
           ELSE 'lost'
       END etatcalc,
	   td.*,CURRENT_TIMESTAMP as now from ta_devices td;";
		break;
}
if ($DEBUG >= 1) echo "\n<br>SQL: $_sql";


// --------------------------------------------------
// ---- fetch data -------------------
// --------------------------------------------------
$res = $db->query($_sql);
if ($DEBUG >= 1) print_r($tarray);
switch($action) {
	case "alarmDevicesKO" :
		while ($row = $res->fetchArray()) {
			$tarray[]=array($row['tc1type'],$row['name'],$row['value'],$row['tdname'],$row['tdtype'],$row['etat'],$row['dateUpdate'],$row['dateLimit']);
		}
		break;
		
	case "alarmSummaryKO" :
		while ($row = $res->fetchArray()) {
			$tarray[]=array($row[0],$row[1],$row[2]);
		}
		break;
	case "lastEvents" :
		while ($row = $res->fetchArray()) {
			$tarray[]=array($row['id'],$row['device'],$row['message'],$row['dateUpdate']);
		}
		break;
		
	case "etatDevices" : 
		while ($row = $res->fetchArray()) {			
			$tarray[]=array($row['device'],$row['name'],$row['etatcalc'],$row['etat'],$row['dateUpdate'],$row['dateLimit']);
		}
		break;
}
if ($DEBUG >= 1) print_r($tarray);

// --------------------------------------------------
//RESULTATTTTTTTTTTTTTTTT
// --------------------------------------------------
header('Content-type: application/json');
echo json_encode($tarray);	

$db->close();

?>
