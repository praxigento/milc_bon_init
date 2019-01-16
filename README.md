# milc_bon_init
Initial environment to develop bonus component in MILC project.


## Prepare PostgreSQL

Create user for database deployment & usage:
```
$ sudo -u postgresq psql
postgres=# CREATE ROLE user WITH CREATEDB CREATEROLE LOGIN REPLICATION BYPASSRLS;
postgres=# ALTER ROLE user WITH PASSWORD 'password';
```

Place [authentication parameters](https://www.postgresql.org/docs/11/libpq-pgpass.html) into file `~/.pgpass` to prevent password prompt on script running.


## Configure project

Create database & user to connect to db, then setup configuration parameters:

```
DB_DRIVER="pdo_pgsql"
DB_HOST="localhost"
DB_NAME="db_name"
DB_PASS="password"
DB_USER="user"
```

and launch shell script to create JSON config for php/python scripts:
```
$ cp cfg.init.sh cfg.work.sh
$ nano cfg.work.sh
...
$ sh ./bin/config.sh
```

## Reset database

Reset database using attached dump (`./data/milc.tar.gz`):
```
$ sh ./bin/deploy/db_reset.sh 
```