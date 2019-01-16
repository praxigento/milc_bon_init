#!/usr/bin/env bash
## ********************************************************************
#   Template for configuration script.
#   Copy this script as "cfg.work.sh" then set working parameters.
## ********************************************************************
# make env variables available for shell sub-scripts
set -a

# DB connection parameters (based on Doctrine DBAL)
# (see https://www.doctrine-project.org/projects/doctrine-dbal/en/2.9/reference/configuration.html)
DB_DRIVER="pdo_pgsql"
DB_HOST="localhost"
DB_NAME="db_name"
DB_PASS="password"
DB_USER="user"
