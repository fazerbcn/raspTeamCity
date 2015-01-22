#! /bin/bash -xv
#DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
DIR=dirname "{BASH_SOURCE[0]}"
cd $DIR
php monitor.php
