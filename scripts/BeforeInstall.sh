#!/bin/bash
#################### PLS DO NOT MODIFY THIS FILE  #########  tech@integratech.ae ############################
##Description: Deploys huzaifa repository code to AWS STG & PROD envs.
##Author: Piyush Khandelwal <piyush@integratech.ae> - Integra DevOps
#################### PLS DO NOT MODIFY THIS FILE  #########  tech@integratech.ae ############################


sudo service apache2 stop
sudo rm -r /var/www/html/
sudo mkdir /var/www/html