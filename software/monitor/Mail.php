<?php
// raspTeamCity
// Monitor for TeamCity by raspBerry PI and two SSR to control two alarm signals
// Author: Pau Ruiz - pau at fazerbcn dot net
// Managed at: https://github.com/pauruiz/raspTeamCity

require_once('../../include/swiftmailer/lib/swift_required.php');

// TODO - Look at http://swiftmailer.org/ for an implementation of attachments to leverage this class
class TeamCityMail{
static $lastBuildsFile = 'lastBuilds.json';

static public function artifactForBuild($build){
	$retVal = false;
	if($url = self::artifactURLForBuild($build)){
		$retVal = file_get_contents($url);
	}
	return $retVal;
}

static public function mailBodyForBuild($build, $hasAttachment){
	$attachments = self::getAttachmentsForBuild($build);
	$strbody = 'The server found a problem on the build: ' . $build['name'] . '.<br/>';
	if($hasAttachment==true){
		$strbody .= ' The server has asked me to send to a file for you to see what happens, please review for further details.<br/>';
	}
	$strbody .= '<br/><br/>&nbsp;&nbsp;&nbsp;Sinceresly:<br/><br/>&nbsp;&nbsp;&nbsp;Your Beloved Monitor';
	return $strbody;
}

static public function sendMailForBuild($build, $config){
	$from = $config['mailFrom'];
	$attachments = self::getAttachmentsForBuild($build);
	$bodyMessage = self::mailBodyForBuild($build, count($attachments)>0);
	$additionalParameters = '-f' . $config['mailFrom'];
	return mail($to, $subject, $bodyMessage, $additional_headers, $additional_parameters);
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
	return $attachments;
}

static public function test(){
	// Create the message
	$message = Swift_Message::newInstance()

	// Give the message a subject
	->setSubject('Your subject')

	// Set the From address with an associative array
	->setFrom(array('pau@fazerbcn.org' => 'This is me'))

	// Set the To addresses with an associative array
	->setTo(array('pau@fazerbcn.org', 'pau.ruiz@piksel.com' => 'A name'))

	// Give it a body
	->setBody('Here is the message itself')

	// And optionally an alternative body
	->addPart('<q>Here is the message itself</q>', 'text/html')

	// Optionally add any attachments
	->attach(Swift_Attachment::fromPath('Mail.php'));
}

}
