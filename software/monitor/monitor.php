<?php
// raspTeamCity
// Monitor for TeamCity by raspBerry PI and two SSR to control two alarm signals
// Author: Pau Ruiz - pau at fazerbcn dot net
// Managed at: https://github.com/pauruiz/raspTeamCity

require('./Alarm.php');
require('./TeamCity.php');

$config = parse_ini_file('raspTeamCity.conf', false);
print_r ($config);

# Download all projects information from TeamCity
echo 'Using TeamCity Url: ' . teamCityUrl($config) . PHP_EOL;
$teamCityXML = file_get_contents(teamCityUrl($config));

# We will convert the XML file to JSON
$inputProjects = TeamCity::convertXMLProjectsToArray($teamCityXML);

#echo 'Input projects: ';
#print_r($inputProjects);

# Filtering the projects to monitor
$monitoredProjects = filterMonitoredProjects($config, $inputProjects);

#echo 'Monitored projects: ';
#print_r($monitoredProjects);

# We will check the state of the build server with the projects we are monitoring
$buildFailure = TeamCity::isBuildFailure($monitoredProjects);

# We will control the gpio according to the state of the project we are monitoring
if($monitoredProjects){
	if(shouldAlarm($monitoredProjects)){
		Alarm::activate(1); 
	}else{
		Alarm::deactivate(1); 
	}
	echo('Monitoring ' . count($monitoredProjects) . ' projects' . PHP_EOL);
}else{
	trigger_error('No monitored projects in this response from server', E_USER_WARNING);	
}

// ----------------------------------------------------------------
// ----------------------------------------------- Start of Methods
// ----------------------------------------------------------------
// ---- Config methods
function teamCityUrl($config){
	@$url = $config['demo']?'teamcity-demo.xml':'http://' . $config['username'] . ':' . $config['password'] . '@' . $config['teamcityIP'] . ':' . $config['teamcityPort'] . '/httpAuth/app/rest/cctray/projects.xml';
	return $url;
}

// --- Project related methods

function filterMonitoredProjects($config, $projects){
	$monitoredProjects = array();
	for($i=0;$i<count($projects);$i++){
		$project = $projects[$i];
		if(isProjectMonitored($config, $project) == TRUE){
			$monitoredProjects[] = $project;
		}
	}
	return $monitoredProjects;
}

function isProjectMonitored($config, $project){
	$retVal = FALSE;
	for($i=0;$i<count($config['projects']);$i++){
		if(strcmp($project['name'], $config['projects'][$i]) == 0){
			$retVal = TRUE;
		}
	}
	return $retVal;
}

function shouldAlarm($monitoredProjects){
	return TeamCity::isBuildFailure($monitoredProjects);
}

// -- Protection methods
function __set($sKey, $sValue){
	// Any setter data should be *returned* here
	// NOTE: this function will only be called if the property is not publicly accessible

	trigger_error('Non existing property name' . $sKey, E_USER_WARNING);
}
