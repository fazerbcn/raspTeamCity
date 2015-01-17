<?php
class TeamCity{
const ProjectStatusUnknown = 0;
const ProjectStatusFailure = 1;
const ProjectStatusSuccess = 2;

static public function convertXMLProjectsToArray($teamCityXML){
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

static public function isBuildFailure($projects){
	$retVal = NO;
	for($i=0;$i<count($projects);$i++){
		if(self::projectStatus($projects[$i]) == ProjectStatusFailure){
			$retVal = YES;
			break;
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
