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
	$url = self::teamCityProjectsUrl($ip, $port, $username, $password, $demo);
	$teamCityXML = self::loadXML($url, $username, $password);
	$teamCityObjects = self::convertXMLToArray($teamCityXML);
	$teamCityObjects = self::cleanTeamCityArrayResponse($teamCityObjects);
	for($i=0;$i<count($teamCityObjects);$i++){
		$project = $teamCityObjects[$i];
		$teamCityXML = self::loadXML(self::teamCityProjectBuildTypesUrl($ip, $port, $project['id'], $demo), $username, $password);
		foreach($teamCityXML->children() as $bt){
			$buildTypeId = (string)$bt['id'];
			$teamCityXML = self::loadXML(self::teamCityProjectBuildUrl($ip, $port, $buildTypeId, $demo), $username, $password);
			//$build = $teamCityXML->children()[0];
			$build = self::convertXMLToArray($teamCityXML);
			$buildId = $build['@attributes']['id'];
			//echo 'The build is: ' . $build . PHP_EOL;
			//print_r($build);
			$status = $build['@attributes']['status'];
			$artifactsurl = $build['artifacts']['@attributes']['href'];
			$artifacts = (strlen($artifactsurl)>0)?self::loadBuildArtifactPaths(self::teamCityServerURL($ip, $port) . $artifactsurl, $username, $password):array();
      			$status = ($status == '')?'UNKNOWN2':$status;
			$teamCityObjects[$i]['lastBuild'] = array('buildTypeId' => $buildTypeId, 'id' => $buildId, 'name' => (string)$bt['name'], 'number' =>$build['@attributes']['number'],'status' => $status, 'artifacts' => $artifacts, 'artifactsurl' => $artifactsurl);
		}
	}
	return $teamCityObjects;
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
	$state = $build['lastBuild']['status'];
	switch($state){
		case 'SUCCESS':
			$retVal = BuildStatusSuccess;
			break;
		case 'FAILURE':
			$retVal = BuildStatusFailure;
			trigger_error('Build ' . $build['name'] . ' failure', E_USER_WARNING);
			break;
		default:
			trigger_error('Estado indeterminado: ' . $state, E_USER_WARNING);
		case 'UNKNOWN':
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

static public function failuredBuilds($builds){
	$retVal = array();
	for($i=0;$i<count($builds);$i++){
		if(self::buildStatus($builds[$i]) == BuildStatusFailure){
			$retVal[] = $builds[$i];
		}
	}
	return $retVal;
}

// Private methods
static private function cleanProjectsArrayOriginal($input){
	$retVal = array();
	//print_r($input);
	for($i=count($input['project'])-1;$i>=0;$i--){
		//echo 'Current clean build: ' . $i . PHP_EOL;
		//print_r($input['project'][$i]['@attributes']['name']);
		$retVal[] = $input['project'][$i]['@attributes'];
	}
	//echo 'Cleaned builds: ';
	//print_r($retVal);
	return $retVal;
}

static private function cleanTeamCityArrayResponse($input){
	$retVal = array();
	//print_r($input);
	$elements = array_pop($input);
	for($i=count($elements)-1;$i>=0;$i--){
		//echo 'Current clean build: ' . $i . PHP_EOL;
		//print_r($elements[$i]['@attributes']['name']);
		$retVal[] = $elements[$i]['@attributes'];
	}
	//echo 'Cleaned builds: ';
	//print_r($retVal);
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

static private function convertXMLToArray($teamCityXML){
	$jsonProjects = json_encode($teamCityXML);
	$arrayProjects = json_decode($jsonProjects, true);
	//echo "Projects array:\n";
	//print_r($arrayProjects);

	//echo "xml:\n";
	//print_r($xml);
	//echo 'JSON:';
	//print_r($jsonProjects);
	return $arrayProjects;
}

static private function loadBuildArtifactPaths($artifactsurl, $username, $password){
	$artifacts = array();
	$artifactsURLXML = self::loadXML($artifactsurl, $username, $password);
	$artifactsURLArray = self::convertXMLToArray($artifactsURLXML);
	foreach($artifactsURLArray as $artifact){
		echo 'Adding one element' . PHP_EOL;
		print_r($artifact);
		$artifacts[] = $artifact['content']['@attributes']['href'];
	}
	return $artifacts;
}

static private function loadCurrentProjectBuilds($ip, $port, $username, $password, $projectId, $demo){
	$url = self::teamCityProjectBuildsUrl($ip, $port, $projectId, $demo);
	$teamCityXML = self::loadXML($url, $username, $password);
	echo 'XML: ' . $teamCityXML . PHP_EOL;
	echo 'XML?: ' . $teamCityObjects . PHP_EOL;
	
	//$retVal = array('id' => (string));
	echo 'Current BuildTypes:';
	print_r($teamCityObjects);
	return self::convertXMLToArray($teamCityXML);
}

static private function loadXML($url, $username, $password){
	$context = self::context($username, $password);
	$fileContents = file_get_contents($url, false, $context);
	return ($fileContents!='')?simplexml_load_string($fileContents):false;
}

static private function teamCityProjectsUrl($ip, $port, $username, $password, $demo){
	@$url = ($config['demo']==true)?'teamcity-projects-demo.xml':self::teamCityServerURL($ip, $port) . '/httpAuth/app/rest/projects';
	return $url;
}

static private function teamCityProjectBuildsUrl($ip, $port, $projectId, $demo){
	@$url = ($config['demo']==true)?'teamcity-builds-demo.xml':self::teamCityServerURL($ip, $port) . '/httpAuth/app/rest/builds/running:false,count:1,project:' . urlencode($projectId);
	return $url;
}

static private function teamCityProjectBuildUrl($ip, $port, $buildTypeId, $demo){
	@$url = ($config['demo']==true)?'teamcity-builds-demo.xml':self::teamCityServerURL($ip, $port) . '/httpAuth/app/rest/builds/running:false,count:1,buildType:' . urlencode($buildTypeId);
	return $url;
}

static private function teamCityProjectBuildTypesUrl($ip, $port, $projectId, $demo){
	@$url = ($config['demo']==true)?'teamcity-builds-demo.xml':self::teamCityServerURL($ip, $port) . '/httpAuth/app/rest/projects/' . urlencode($projectId) . '/buildTypes';
	return $url;
}

static private function teamCityServerURL($ip, $port){
	return 'http://' . $ip . ':' . $port;
}

}
