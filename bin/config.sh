#!/usr/bin/env bash
## =========================================================================
#   Create JSON configuration for php/python/... scripts.
#
#       This is friendly user script, not user friendly
#       There are no protection from mistakes.
#       Use it if you know how it works.
## =========================================================================
# pin current folder and deployment root folder
DIR_CUR="$PWD"
# root directory (is set before or is relative to the current shell script)
DIR_ROOT=${DIR_ROOT:=`cd "$( dirname "$0" )/../" && pwd`}

## =========================================================================
#   Validate deployment mode and load configuration
#   (root config if script is chained or local config if is standalone)
## =========================================================================
MODE=${MODE}
IS_CHAINED="yes"       # 'yes' - this script is launched in chain with other scripts, 'no'- standalone launch;
if [ -z "$MODE" ]; then
    MODE="work"
    IS_CHAINED="no"
fi

# check configuration file exists and load deployment config (db connection, etc.).
FILE_CFG=${DIR_ROOT}/cfg.${MODE}.sh
if [ -f "${FILE_CFG}" ]; then
    if [ "${IS_CHAINED}" = "no" ]; then    # this is standalone launch, load deployment configuration;
        echo "There is deployment configuration in ${FILE_CFG}."
        . ${FILE_CFG}
    # else: deployment configuration should be loaded before
    fi
else
    echo "There is no expected configuration in ${FILE_CFG}. Aborting..."
    cd ${DIR_CUR}
    exit 255
fi


## =========================================================================
#   Setup working environment
## =========================================================================
# deployment configuration (see ${FILE_CFG})
DB_DRIVER="${DB_DRIVER}"    # DB connection params
DB_HOST="${DB_HOST}"
DB_NAME="${DB_NAME}"
DB_PASS="${DB_PASS}"
DB_USER="${DB_USER}"

# this step env. vars
FILE_CFG_LOCAL_INI="${DIR_ROOT}/cfg/local.init.json"
FILE_CFG_LOCAL="${DIR_ROOT}/cfg/local.json"



## =========================================================================
#   Start backend deployment
## =========================================================================
cd ${DIR_ROOT}

# prepare backend configuration
cp ${FILE_CFG_LOCAL_INI} ${FILE_CFG_LOCAL}
sed -e "s|\${DB_DRIVER}|${DB_DRIVER}|g" ${FILE_CFG_LOCAL} > ${FILE_CFG_LOCAL}.target
mv ${FILE_CFG_LOCAL}.target ${FILE_CFG_LOCAL}
sed -e "s|\${DB_HOST}|${DB_HOST}|g" ${FILE_CFG_LOCAL} > ${FILE_CFG_LOCAL}.target
mv ${FILE_CFG_LOCAL}.target ${FILE_CFG_LOCAL}
sed -e "s|\${DB_NAME}|${DB_NAME}|g" ${FILE_CFG_LOCAL} > ${FILE_CFG_LOCAL}.target
mv ${FILE_CFG_LOCAL}.target ${FILE_CFG_LOCAL}
sed -e "s|\${DB_USER}|${DB_USER}|g" ${FILE_CFG_LOCAL} > ${FILE_CFG_LOCAL}.target
mv ${FILE_CFG_LOCAL}.target ${FILE_CFG_LOCAL}
sed -e "s|\${DB_PASS}|${DB_PASS}|g" ${FILE_CFG_LOCAL} > ${FILE_CFG_LOCAL}.target
mv ${FILE_CFG_LOCAL}.target ${FILE_CFG_LOCAL}



echo ""
echo "************************************************************************"
echo "  Local JSON configuration is created."
echo "************************************************************************"
cd ${DIR_CUR}