#!/bin/bash
## backup each mysql db into a different file, rather than one big file
## as with --all-databases. This will make restores easier.
## To backup a single database simply add the db name as a parameter (or multiple dbs)

# mkdir -p /vv_files/backups
# useradd --home-dir /var/backups/mysql --gid backup --no-create-home mysql-backup
## Remember to make the script executable, and unreadable by others
# chown -R mysql-backup:backup /var/backups/mysql
# chmod u=rwx,g=rx,o= /var/backups/mysql/dump.sh

## crontab entry - backup every 6 hours
# sudo crontab -e
# 0 */6 * * * /backup/bkp_dbs.sh

###########################################################
## Create 'backup' mysql user (CLI >>>> mysql -u root -p)
## CREATE USER 'backup'@'localhost' IDENTIFIED BY 'MyPass@123'; 
## GRANT EVENT, LOCK TABLES, PROCESS, REFERENCES, SELECT, SHOW DATABASES, SHOW VIEW, TRIGGER ON *.* TO 'backup'@'localhost' ;
## Or optionally, run this script as root with password stored on '/root/.my.cnf' file
###########################################################

##Getting mysql cridentials from .my.cnf
#default source is /root/.my.cnf
USER=$user
PASS=$password

BkpDIR=$(dirname $0)"/backups" #vv_files/backups
SQLsDIR=$BkpDIR"/databases"
MYSQLDUMP="/usr/bin/mysqldump"
MYSQL="/usr/bin/mysql"
timestamp=$(date +%F_%H%M)

pfix="AllDbs"
sfix="DBs.tar.gz"
bkp_file=$pfix-$timestamp-$sfix #File name to be as backup done
days=+3 						#Days old files will be deleted (system modified date)
rm -r  $SQLsDIR				#remove the previous database source folder
mkdir -p $BkpDIR $SQLsDIR			#make the new database source folder

log=$(dirname $0)/logs/log_bkpdbs.log
log1=log_bkpdbs.log 			#log file name
log=$(dirname $0)/$log1			#will make new log file if not there
rm $log						 	#remove previous log file

echo All Databases backup [$timestamp ]>>$log
echo - Starting Backup Process >>$log
echo "***** This will delete older then $days days of existing bkp files ***" >>$log #will delete the compressed bkp files older than $days
find $(dirname $0) -name "*$sfix" -type f -mtime $days -print -delete >>$log #will write the logs into $log file
echo This is All Databases Bkp Script, will run as of your cron job.
##########################################################################################

if [ -z "$1" ]; then
	#databases=`$MYSQL --user=$USER --password=$PASS --batch --skip-column-names -e "SHOW DATABASES;" | grep -v 'mysql\|information_schema'`
	databases=`$MYSQL --user=$USER --batch --skip-column-names -e "SHOW DATABASES;" | grep -v 'mysql\|information_schema'`
	for database in $databases; do
		$MYSQLDUMP \
		--user=$USER \
		--force \
		--quote-names --dump-date \
		--opt --single-transaction \
		--skip-events --routines --triggers \
		--databases $database \
		--result-file="$SQLsDIR/$database.sql"
	done
else
	for database in ${@}; do
		$MYSQLDUMP \
		--user=$USER \
		--force \
		--quote-names --dump-date \
		--opt --single-transaction \
		--skip-events --routines --triggers \
		--databases $database \
		--result-file="$SQLsDIR/$database.sql"
	done
	fi
	cp $log $BkpDIR
	tar -zcvf $BkpDIR/$bkp_file $SQLsDIR |tee -a $log
	
/usr/bin/mail -s "All DBs backup [s1]" root < $log
