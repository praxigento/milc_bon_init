#!/usr/bin/env bash
## =========================================================================
#   Binary bonus emulation.
#
#       This is friendly user script, not user friendly
#       There are no protection from mistakes.
#       Use it if you know how it works.
## =========================================================================
# pin current folder and deployment root folder
DIR_CUR="$PWD"
# root directory (is set before or is relative to the current shell script)
DIR_ROOT=${DIR_ROOT:=`cd "$( dirname "$0" )/../../" && pwd`}

## =========================================================================
#   Setup working environment
## =========================================================================
# this step env. vars


## =========================================================================
#   Perform processing.
## =========================================================================
cd ${DIR_ROOT}
echo ""
echo "************************************************************************"
echo "  Binary bonus emulation is completed."
echo "************************************************************************"
. ${DIR_ROOT}/bin/mysql/db/clean.sh
. ${DIR_ROOT}/bin/config.sh
php ${DIR_ROOT}/bin/php/db_struct.php
. ${DIR_ROOT}/bin/mysql/db/views.sh
php ${DIR_ROOT}/bin/php/init/plan/binary.php
php ${DIR_ROOT}/bin/php/init/downline/binary.php


echo ""
echo "************************************************************************"
echo "  Binary bonus emulation is completed."
echo "************************************************************************"
cd ${DIR_CUR}