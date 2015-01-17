#!/bin/bash

if [ -z $1 ]
then
    opt="toggle"
elif [ -n $1 ]
then
    opt=$1
fi

let "sleep = $RANDOM + 10000"
sleep "0.$sleep"

if [ $(pgrep gpio17.sh|wc -w) -gt "2" ]; then
    exit
fi

if [ ! -e "/sys/class/gpio/gpio17/value" ]
then
    echo "17" > /sys/class/gpio/export
    echo "out" > /sys/class/gpio/gpio17/direction
fi

case $opt in
    on)
        echo 1 > /sys/class/gpio/gpio17/value
        ;;
    off)
        echo 0 > /sys/class/gpio/gpio17/value
        ;;
    toggle)
        value=`cat /sys/class/gpio/gpio17/value`
        if [ $value -ne 0 ]
        then
            echo 0 > /sys/class/gpio/gpio17/value
        else
            echo 1 > /sys/class/gpio/gpio17/value
        fi
        ;;
    reboot)
        echo 0 > /sys/class/gpio/gpio17/value
        sleep 30
        echo 1 > /sys/class/gpio/gpio17/value
        ;;
    status)
        exit
        ;;
    *)
        echo "Invalid option - use on, off, toggle, or reboot (toggle is the default)."
        exit
        ;;
esac

sleep 3

