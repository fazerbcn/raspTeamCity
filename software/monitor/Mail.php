<?php
// raspTeamCity
// Monitor for TeamCity by raspBerry PI and two SSR to control two alarm signals
// Author: Pau Ruiz - pau at fazerbcn dot net
// Managed at: https://github.com/pauruiz/raspTeamCity

class TeamCityMail{
static $lastBuildsFile = 'lastBuilds.json';

static public function artifactForBuild($build){
	$retVal = false;
	if($url = self::artifactURLForBuild($build)){
		$retVal = file_get_contents($url);
	}
	return $retVal;
}

static public function mailBodyForBuild($build){
}

static public function sendMailForBuild($build, $config){
	$from = $config['mailFrom'];
	mail($to, $subject, $message, $additional_headers, $additional_parameters);º
}

static private function artifactURLForBuild($build){
	$xml = simplexml_load_string($teamCityXML);
	$jsonProjects = json_encode($xml);
	$arrayProjects = json_decode($jsonProjects, true);
	#echo "Projects array:\n";
	#print_r($arrayProjects);
	$arrayProjects = self::cleanProjectArray($arrayProjects);

	#echo "xml:\n";
}

}
