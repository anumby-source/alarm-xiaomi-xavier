<?php

$server = "localhost";     // change if necessary
$port = 1883;                     // change if necessary
$username = "";                   // set your username
$password = "";                   // set your password
$client_id = "moi"; // make sure this is unique for connecting to sever - you could use uniqid()

$SQLITE_BASE="sqliteAlarm.db";

/*
$T_DEVICES=array();
//			topic mqtt					nom					type
$T_DEVICES['0x00158d0002042179']=array("bouton-flo",		"bouton");
$T_DEVICES['0x00158d0002b48b31']=array("cuisine-movedetect","detect-move");
$T_DEVICES['0x00158d0002722cea']=array("cuisine-openwindow1","detect-open");

$T_DEVICES['0x000xcuisine-openwindow2']=array("cuisine-openwindow2","detect-open");

$T_DEVICES['0x000xsalle2bain-openwindow']=array("salle2bain-openwindow","detect-open");
$T_DEVICES['0x000xsalle2bain-movedetect']=array("salle2bain-movedetect","detect-move");
$T_DEVICES['0x000xsalon-movedetect1']=array("salon-movedetect1","detect-move");
$T_DEVICES['0x000xsalon-movedetect2']=array("salon-movedetect2","detect-move");
$T_DEVICES['0x000xsalon-openwindow1']=array("salon-openwindow1","detect-open");
$T_DEVICES['0x000xsalon-openwindow2']=array("salon-openwindow2","detect-open");
$T_DEVICES['0x000xsalon-openwindow3']=array("salon-openwindow3","detect-open");
$T_DEVICES['0x000xsalon-openwdoor']=array("salon-openwdoor","detect-open");
$T_DEVICES['0x000xchambre-movedetect']=array("chambre-movedetect","detect-move");
$T_DEVICES['0x000xchambre-openwindow']=array("chambre-openwindow","detect-open");
* */
?>
