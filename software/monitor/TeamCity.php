<?php
// raspTeamCity
// Monitor for TeamCity by raspBerry PI and two SSR to control two alarm signals
// Author: Pau Ruiz - pau at fazerbcn dot net
// Managed at: https://github.com/pauruiz/raspTeamCity

const BuildStatusUnknown = 0;
const BuildStatusFailure = 1;
const BuildStatusSuccess = 2;
class TeamCity{
static $lastBuildsFile = 'lastBuilds.json';

static public function loadCurrentProjects($ip, $port, $username, $password, $demo){
	$url = self::teamCityProjectsUrl($ip, $port, $demo);
	$teamCityXML = self::loadXML($url, $username, $password);
	return self::convertXMLBuildsToArray($teamCityXML);
	#return self::cleanBuildsArray($teamCityXML);
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

static public function buildStatus($build){
	$retVal = false;
	$state = $build['lastBuildStatus'];
	switch($state){
		case 'Success':
			$retVal = BuildStatusSuccess;
			break;
		case 'Failure':
			$retVal = BuildStatusFailure;
			trigger_error('Build ' . $build['name'] . ' failure', E_USER_WARNING);
			break;
		default:
			trigger_error('Estado indeterminado: ' . $state, E_USER_WARNING);
		case 'Unknown':
			$retVal = BuildStatusUnknown;
			break;
	}
	return $retVal;
}

static public function isBuildFailure($build){
	$retVal = false;
	if(self::buildStatus($build) == BuildStatusFailure){
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
static public function isServerFailure($builds){
	$retVal = false;
	for($i=0;$i<count($builds);$i++){
		if(self::buildStatus($builds[$i]) == BuildStatusFailure){
			$retVal = true;
			break;
		}
	}
	return $retVal;
}

static public function failuredBuilds($builds){
	$retVal = array();
	for($i=0;$i<count($builds);$i++){
		if(self::buildStatus($builds[$i]) == BuildStatusFailure){
			$retVal[] = $builds[$i];
		}
	}
	return $retVal;
}

# // Private methods
static private function cleanBuildsArray($input){
	$retVal = array();
	$projects = $input['Project'];
	print_r($projects);
	// Protection against the fact we lose the mother array of builds because we only have one
	if (count($projects) == 1 && is_array($projects['@attributes'])) {
		$projects = array(0 => $projects);
	}
	for($i=count($projects)-1;$i>=0;$i--){
		echo 'Current clean build: ' . $i . PHP_EOL;
		print_r($projects[$i]['@attributes']['name']);
		$retVal[] = $projects[$i]['@attributes'];
	}
	echo 'Cleaned builds: ';
	print_r($retVal);
	return $retVal;
}

static private function context($username, $password){
	$context = stream_context_create(array(
		'http' => array(
			'header'  => "Authorization: Basic " . base64_encode($username . ':' . $password),
			'timeout' => 60,
		)
	));
	return $context;
}

static private function convertXMLBuildsToArray($teamCityXML){
	$jsonProjects = json_encode($teamCityXML);
	$arrayProjects = json_decode($jsonProjects, true);
	echo "Projects array:\n";
	print_r($arrayProjects);
	$arrayProjects = self::cleanBuildsArray($arrayProjects);

	#echo "xml:\n";
	#print_r($xml);
	#echo 'JSON:';
	#print_r($jsonProjects);
	return $arrayProjects;
}

static private function loadXML($url, $username, $password){
	$context = self::context($username, $password);
	$xml = simplexml_load_string(file_get_contents($url, false, $context)); 
	return $xml;
}

static private function teamCityProjectsUrl($ip, $port, $demo){
	@$url = ($demo==true)?'teamcity-demo.xml':'http://' . $ip . ':' . $port . '/httpAuth/app/rest/cctray/projects.xml';
	return $url;
}

}
