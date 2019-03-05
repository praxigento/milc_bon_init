#!/usr/bin/env bash
## =========================================================================
#   Re-create view to overview data.
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
DB_HOST="${DB_HOST}"
DB_NAME="${DB_NAME}"
DB_PASS="${DB_PASS}"
DB_USER="${DB_USER}"
VIEWS_SQL="${DIR_ROOT}/data/sql/views.sql"

# this step env. vars
PSQL="psql -d ${DB_NAME} -U ${DB_USER} -h ${DB_HOST} -f ${VIEWS_SQL}"


## =========================================================================
#   Start backend deployment
## =========================================================================
cd ${DIR_ROOT}

echo "Create views from file '${VIEWS_SQL}'..."
${PSQL}
ERR=$?
if [ ${ERR} -ne 0 ]; then
    echo "Cannot create views from file '${VIEWS_SQL}'. Aborting..."
    exit ${ERR}
fi



echo ""
echo "************************************************************************"
echo "  Views are created."
echo "************************************************************************"
cd ${DIR_CUR}