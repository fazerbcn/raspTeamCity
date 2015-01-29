<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

// raspTeamCity
// Monitor for TeamCity by raspBerry PI and two SSR to control two alarm signals
// Author: Pau Ruiz - pau at fazerbcn dot net
// Managed at: https://github.com/pauruiz/raspTeamCity

require('./Alarm.php');
require('./TeamCity.php');
require('./Sound.php');

$config = parse_ini_file('../../conf/raspTeamCity.conf', false);
print_r ($config);

# Download all builds information from TeamCity
echo 'Using TeamCity Url: ' . teamCityUrl($config) . PHP_EOL;
$currentBuilds = TeamCity::loadCurrentBuilds(teamCityUrl($config));

echo 'Current builds: ';
print_r($currentBuilds);

# Filtering the builds to monitor for alarms
$alarmMonitoredBuilds = filterBuildsByNameInArray($config['alarmBuilds'], $currentBuilds);

echo 'Alarm monitored Builds: ';
print_r($alarmMonitoredBuilds);

# We will control the gpio according to the state of the builds we are monitoring
if($alarmMonitoredBuilds){
	$lastAlarmBuilds = filterBuildsByNameInArray($config['alarmBuilds'], TeamCity::loadLastBuilds());
	
	if(shouldAlarm($alarmMonitoredBuilds, $lastAlarmBuilds, $config['alarmOnNextFailure'])){
		Alarm::activate(1); 
		if($config['alarmSound']!=false){
			Sound::playSound('../../sounds/' . $config['alarmSound']);
		}
	}else{
		Alarm::deactivate(1); 
	}
	echo(count($alarmMonitoredBuilds) . ' alarm monitored builds' . PHP_EOL);
}else{
	trigger_error('No alarm monitored builds in this response from server', E_USER_WARNING);	
}

$mailMonitoredBuilds = filterBuildsByNameInArray($config['mailBuilds'], $currentBuilds);
;echo 'Mail monitored Builds: ';
;print_r($mailMonitoredBuilds);
if($mailMonitoredBuilds){
	$lastMailBuilds = filterBuildsByNameInArray($config['mailBuilds'], TeamCity::loadLastBuilds());
	for($i=0;$i<count($mailMonitoredBuilds);$i++){
		$build = $mailMonitoredBuilds[$i];
		if(TeamCity::isBuildFailure($build)){
			$lastBuild = searchBuildByName($lastMailBuilds, $build['name']);
			if($lastBuild || !TeamCity::isSameRun($build, $lastBuild)){
				// Mail = Yes
				trigger_error('Sending mail for build ' . $build['name'] , E_USER_WARNING);	
			}
		}
		
	}
	
	echo(count($mailMonitoredBuilds) . ' mail monitored Builds' . PHP_EOL);
}else{
	trigger_error('No mail monitored builds in this response from server', E_USER_WARNING);	
}


# We will store the current builds so we will be able to retrieve it later
if (!$config['demo']){
	TeamCity::saveLastBuilds($currentBuilds);
}

// ----------------------------------------------------------------
// ----------------------------------------------- Start of Methods
// ----------------------------------------------------------------
// ---- Config methods
function teamCityUrl($config){
	@$url = ($config['demo']==true)?'teamcity-demo.xml':'http://' . $config['username'] . ':' . $config['password'] . '@' . $config['teamcityIP'] . ':' . $config['teamcityPort'] . '/httpAuth/app/rest/cctray/projects.xml';
	return $url;
}

// --- Project related methods

function filterBuildsByNameInArray($filteredBuildNames, $builds){
	$monitoredBuilds = array();
	for($i=0;$i<count($builds);$i++){
		$build = $builds[$i];
		if(isBuildInFilter($filteredBuildNames, $build) == TRUE){
			$monitoredBuilds[] = $build;
		}
	}
	return $monitoredBuilds;
}

function searchBuildByName($builds, $searchName){
	$retVal = false;
	for($i=0;$i<count($builds);$i++){
		if(strcmp($searchName, $builds[$i]['name']) == 0){
			$retVal = $builds[$i];
			break;
		}
	}
	return $retVal;
}

function isBuildInFilter($filteredBuildNames, $build){
	$retVal = false;
	for($i=0;$i<count($filteredBuildNames);$i++){
		if(strcmp($build['name'], $filteredBuildNames[$i]) == 0){
			$retVal = true;
		}
	}
	return $retVal;
}

function shouldAlarm($alarmMonitoredBuilds, $lastBuilds, $shouldAlarmOnNextFailure = false){
	$retVal = false;
	for ($i=0;$i<count($alarmMonitoredBuilds);$i++){
		$currentBuild = $alarmMonitoredBuilds[$i];
		if (TeamCity::isBuildFailure($currentBuild)){
			for($j=0;$j<count($lastBuilds);$j++){
				$lastBuild = $lastBuilds[$j];
				print_r($shouldAlarmOnNextFailure);
				if(!$shouldAlarmOnNextFailure === true){
				echo 'Tamos aqui!!!!!!' . PHP_EOL;
					if(TeamCity::isSameBuild($currentBuild, $lastBuild)){
						echo 'Tamos aqui2!!!!!!' . PHP_EOL;
						if(!TeamCity::isSameRun($currentBuild, $lastBuild)){
							echo 'Tamos aqui3!!!!!!' . PHP_EOL;
							$retVal = true;
						}else{
							trigger_error('Nos libramos de la alarma porque ya hemos avisado en Build: ' . $currentBuild['name'], E_USER_WARNING);
						}
					}else{
						$retVal = true;
					}
				}
				break;
			}
		}
	}
	#return TeamCity::isBuildFailure($alarmMonitoredBuilds);
	return $retVal;
}

// -- Protection methods
function __set($sKey, $sValue){
	// Any setter data should be *returned* here
	// NOTE: this function will only be called if the property is not publicly accessible

	trigger_error('Non existing property name' . $sKey, E_USER_WARNING);
}
