<?php
// raspTeamCity
// Monitor for TeamCity by raspBerry PI and two SSR to control two alarm signals
// Author: Pau Ruiz - pau at fazerbcn dot net
// Managed at: https://github.com/pauruiz/raspTeamCity

const ProjectStatusUnknown = 0;
const ProjectStatusFailure = 1;
const ProjectStatusSuccess = 2;
class TeamCity{
static $lastBuildsFile = 'lastBuilds.json';

static public function convertXMLBuildsToArray($teamCityXML){
	$xml = simplexml_load_string($teamCityXML);
	$jsonProjects = json_encode($xml);
	$arrayProjects = json_decode($jsonProjects, true);
	#echo "Projects array:\n";
	#print_r($arrayProjects);
	$arrayProjects = self::cleanProjectArray($arrayProjects);

	#echo "xml:\n";
	#print_r($xml);
	#echo 'JSON:';
	#print_r($jsonProjects);
	return $arrayProjects;
}

static public function loadCurrentBuilds($url){
	$teamCityXML = file_get_contents($url);
	return self::convertXMLBuildsToArray($teamCityXML);
	#return self::cleanProjectArray($teamCityXML);
}

static public function loadLastBuilds(){
	if(file_exists(self::$lastBuildsFile)){
		$teamCityJSON = file_get_contents(self::$lastBuildsFile);
		$retVal = json_decode($teamCityJSON, true);
	}else{
		$retVal = array();
	}
	return $retVal;
}

static public function saveLastBuilds($teamCityBuilds){
	$teamCityJSON = json_encode($teamCityBuilds);
	file_put_contents(self::$lastBuildsFile, $teamCityJSON);
}

static public function projectStatus($project){
	$retVal = false;
	$state = $project['lastBuildStatus'];
	switch($state){
		case 'Success':
			$retVal = ProjectStatusSuccess;
			break;
		case 'Failure':
			$retVal = ProjectStatusFailure;
			trigger_error('Project ' . $project['name'] . ' failure', E_USER_WARNING);
			break;
		default:
			trigger_error('Estado indeterminado: ' . $state, E_USER_WARNING);
		case 'Unknown':
			$retVal = ProjectStatusUnknown;
			break;
	}
	return $retVal;
}

static public function isBuildFailure($build){
	$retVal = false;
	if(self::projectStatus($build) == ProjectStatusFailure){
		$retVal = true;
	}
	return $retVal;
}

static public function isSameBuild($build1, $build2){
	$retVal = false;
	if($build1['name'] == $build2['name']){
		$retVal = true;
	}
	return $retVal;
}

static public function isSameRun($build1, $build2){
	$retVal = false;
	if($build1['lastBuildLabel'] == $build2['lastBuildLabel']){
		$retVal = true;
	}
	return $retVal;
}

/// Deprecated, in favour of single test per build
static public function isServerFailure($projects){
	$retVal = false;
	for($i=0;$i<count($projects);$i++){
		if(self::projectStatus($projects[$i]) == ProjectStatusFailure){
			$retVal = true;
			break;
		}
	}
	return $retVal;
}

static public function failuredBuilds($projects){
	$retVal = array();
	for($i=0;$i<count($projects);$i++){
		if(self::projectStatus($projects[$i]) == ProjectStatusFailure){
			$retVal[] = $projects[$i];
		}
	}
	return $retVal;
}

# // Private methods
static private function cleanProjectArray($input){
	$retVal = array();
	for($i=count($input['Project'])-1;$i>=0;$i--){
		#echo 'Current clean project: ' . $i . PHP_EOL;
		#print_r($input['Project'][$i]['@attributes']['name']);
		$retVal[] = $input['Project'][$i]['@attributes'];
	}
	//echo 'Cleaned projects: ';
	//print_r($retVal);
	return $retVal;
}

}
