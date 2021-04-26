#################### PLS DO NOT MODIFY THIS FILE  #########  tech@integratech.ae ############################
##Description: Deploys huzaifa repository code to AWS STG & PROD envs.
##Author: Piyush Khandelwal <piyush@integratech.ae> - Integra DevOps
#################### PLS DO NOT MODIFY THIS FILE  #########  tech@integratech.ae ############################


#!/bin/bash
# timedatectl set-timezone "Asia/Dubai"
sudo service apache2 start
# sudo supervisorctl reread
# sudo supervisorctl update