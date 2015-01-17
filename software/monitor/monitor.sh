#!/bin/sh
# We will parse all variables of the config file
shopt -s extglob
configfile="raspTeamCity.conf" # set the actual path name of your (DOS or Unix) config file
tr -d '\r' < $configfile > $configfile.unix
while IFS='= ' read lhs rhs
do
    if [[ ! $lhs =~ ^\ *# && -n $lhs ]]; then
        rhs="${rhs%%\#*}"    # Del in line right comments
        rhs="${rhs%%*( )}"   # Del trailing spaces
        rhs="${rhs%\"*}"     # Del opening string quotes 
        rhs="${rhs#\"*}"     # Del closing string quotes 
        declare $lhs="$rhs"
    fi
done < $configfile.unix

# We will download all projects information from TeamCity
echo "HOLA$teamcityUrl"
#wget $teamcityUrl
# We will convert the XML file to JSON
# We will get the state of the project to monitor
# We will control the gpio according to the state of the project we are monitoring
