raspTeamCity
============

Raspberry control of power outlet by TeamCity build state

VCS
---

The code and schematics are currently hosted on GitHub: [https://github.com/pauruiz/raspTeamCity.git](https://github.com/pauruiz/raspTeamCity.git)

Directory structure
-------------------

* schematics : Hardware Schematics

* software : Code for the raspberry

	* monitor : Monitorization of Team City Server

	* gpio : GPIO scripts to modify pins

	* web : configuration website

Installation
------------

1. Install raspBian on a SD card and boot the raspBerry Pi

2. Clone the github repository on the /root directory of the raspberrypi:

* Go to a shell and validate as any user (pi/raspberry is the default user/password)

* sudo to a bash: # sudo bash

* Go to the root folder # cd /root

* Create a directory for the monitor (e.x. raspTeamCity) # mkdir raspTeamCity

* Enter that directory # cd raspTeamCity

* Clone the github repository there # clone https://github.com/fazerbcn/raspTeamCity

3. Copy the example configuration and change according to the Team City server and the needs to monitor

Configuration file
------------------

Name | Type | Description
---- | ---- | -----------
teamcityIP | IP String | IP Address of the Team City Server
teamcityPort | Number | TCP Number of the Team City Server
teamcityUsername | String | Username to validate on the Team City Server
teamcityPassword | String | Password to validate on the Team City Server
alarmBuilds[] | Array of strings | Name of all the builds to be monitored to change the state of the power outlet
alarmOnNextFailure | Boolean | Indicates if we should fail on next failed run of a yet failed one
mailBuilds[] | Array of string | NAme of all the builds to be monitored to send mail notifications
mailOnNextFailure | Boolean | Indicates if we should mail on next failed run of a yet failed one
demo | Boolean | Indicates we should use the content of 
mailFrom | String | User and mail to be used as the originator of the mail in the form of 'User <mail>'
mailSubject | String | Subject to be sent in the mail
mailTo[] | Array of strings | User and mails to be used as destinations of the mail in the form of 'User <mail>'



