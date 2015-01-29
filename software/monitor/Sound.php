<?php
// raspTeamCity
// Monitor for TeamCity by raspBerry PI and two SSR to control two alarm signals
// Author: Pau Ruiz - pau at fazerbcn dot net
// Managed at: https://github.com/pauruiz/raspTeamCity

const AudioOutputDefault = 0;
const AudioOutputAnalog  = 1;
const AudioOutputHDMI    = 2;

class Sound{
static function playSound($filePath){
	if(file_exists($filePath)){
		exec('aplay ' . $filePath . '> /dev/null');
	}
}
// Ouput should be any AudioOutput constant from this file
static function selectOutput($output){
	exec('amixer cset numid=3 ' . $output);
}

// TODO -- Check this works
static function setVolume($volumePercent){
	exec('amixer cset numid=3 ' . $volumePercent . '%');
	//exec('amixer sset numid=1 -- ' . $volumePercent . '%');
}

// TODO -- Check this works
static function setVolumes($volumePercentLeft, $volumePercentRight){
	exec('amixer cset numid=3 ' . $volumePercentLeft . ', ' . $volumePercentRight);
}

}
