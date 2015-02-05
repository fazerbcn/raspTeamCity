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

static private function getAttachmentsForBuild($build){
	$attachments = array();
	foreach($build['artifacts'] as $artifacturl){
		$attachment = array();
		$attachment['name'] = basename($artifacturl);
		$attachment['content'] = file_get_contents($artifacturl);
		if($attachment['content'] != ''){
			$attachments[] = $attachment;
		}
	}
	return $attachments
}

}
