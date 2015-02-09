<?php
// raspTeamCity
// Monitor for TeamCity by raspBerry PI and two SSR to control two alarm signals
// Author: Pau Ruiz - pau at fazerbcn dot net
// Managed at: https://github.com/pauruiz/raspTeamCity

class Alarm{

const Off  = 0;
const Same = 2;
const Next = 4;
const On   = 8;


static public function activate($alarm_pos){
	$alarm_pos = floor($alarm_pos);
	#echo 'Cambiado alarm pos ' . $alarm_pos . PHP_EOL;
	if(self::isValidAlarmPos($alarm_pos)){
		exec('../gpio/gpio1' . (6+$alarm_pos) . '.sh enable');
		trigger_error('Alarma activada', E_USER_WARNING);
	}else{
		trigger_error('Invalid alarm ' . $alarm_pos .  ' in activate', E_USER_WARNING);
	}
}

static public function deactivate($alarm_pos){
	$alarm_pos = floor($alarm_pos);
	if(self::isValidAlarmPos($alarm_pos)){
		exec('../gpio/gpio1' . (6+$alarm_pos) . '.sh enable');
		trigger_error('Alarma desactivada', E_USER_WARNING);
	}else{
		trigger_error('Invalid alarm in deactivate', E_USER_WARNING);
	}
}

static private function isValidAlarmPos($alarm_pos){
	return ($alarm_pos >0 && $alarm_pos <3);
}

}
