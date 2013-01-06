#!/usr/bin/env bash
set -e

source ../edit_these.sh
eval $( sed -n "/^define/ { s/.*('\([^']*\)', '*\([^']*\)'*);/export \1=\"\2\"/; p }" $NEWZPATH/www/config.php )

export MYSQL_CMD="UPDATE groups set backfill_target=backfill_target+1 where active=1 and backfill_target<$MAXDAYS;"

if [ "$THREADS" == "true"  -a "$INNODB" == "true" ]; then
	while :
	 do

            #import nzb's
	    if [[ $IMPORT == "true" ]] ; then
                cd $INNODB_PATH
            	[ -f nzb-import.php ] && $PHP nzb-import.php ${NZBS} &
            fi

	    #make active groups current
            cd $INNODB_PATH
	    [ -f update_binaries_threaded.php ] && $PHP update_binaries_threaded.php &

	    #get backfill for all active groups
            if [[ $BACKFILL == "true" ]] ; then
                cd $INNODB_PATH
	        [ -f backfill_threaded.php ] && $PHP backfill_threaded.php &

                #increment backfill days
                $MYSQL -u$DB_USER -h $DB_HOST --password=$DB_PASSWORD $DB_NAME -e "${MYSQL_CMD}"
            fi

	    wait

	    echo "imports waiting $NEWZNAB_IMPORT_SLEEP_TIME seconds..."
	    sleep $NEWZNAB_IMPORT_SLEEP_TIME

	done

elif [ "$THREADS" != "true" -a "$INNODB" == "true" ]; then
	while :
	 do

	    #import nzb's
            if [[ $IMPORT == "true" ]] ; then
	        cd $INNODB_PATH
	        [ -f $INNODB_PATH/nzb-import.php ] && $PHP $INNODB_PATH/nzb-import.php ${NZBS} &
            fi

	    #make active groups current
            cd $INNODB_PATH
	    [ -f $INNODB_PATH/update_binaries.php ] && $PHP $INNODB_PATH/update_binaries.php &

	    #get backfill for all active groups
            if [[ $BACKFILL == "true" ]] ; then
	        cd $INNODB_PATH
	        [ -f $INNODB_PATH/backfill.php ] && $PHP $INNODB_PATH/backfill.php &

                #increment backfill days
                $MYSQL -u$DB_USER -h $DB_HOST --password=$DB_PASSWORD $DB_NAME -e "${MYSQL_CMD}"
            fi
	    wait

	    echo "imports waiting $NEWZNAB_IMPORT_SLEEP_TIME seconds..."
	    sleep $NEWZNAB_IMPORT_SLEEP_TIME

	done

elif [ "$THREADS" == "true" -a "$INNODB" != "true" ]; then
	while :
	 do

	    #import nzb's
            if [[ $IMPORT == "true" ]] ; then
	        cd $ADMIN_PATH
	        [ -f $ADMIN_PATH/nzb-importmodified.php ] && $PHP $ADMIN_PATH/nzb-importmodified.php ${NZBS} &
            fi

            #make active groups current
	    cd $NEWZNAB_PATH
	    [ -f $NEWZNAB_PATH/update_binaries_threaded.php ] && $PHP $NEWZNAB_PATH/update_binaries_threaded.php &

	    #get backfill for all active groups
            if [[ $BACKFILL == "true" ]] ; then
	        cd $NEWZNAB_PATH
                [ -f $NEWZNAB_PATH/backfill_threaded.php ] && $PHP $NEWZNAB_PATH/backfill_threaded.php &
                #increment backfill days
                $MYSQL -u$DB_USER -h $DB_HOST --password=$DB_PASSWORD $DB_NAME -e "${MYSQL_CMD}"
            fi

	    wait

	    echo "imports waiting $NEWZNAB_IMPORT_SLEEP_TIME seconds..."
	    sleep $NEWZNAB_IMPORT_SLEEP_TIME

	done
else
	while :
	 do

	    #import nzb's
            if [[ $IMPORT == "true" ]] ; then
	        cd $ADMIN_PATH
	        [ -f $ADMIN_PATH/nzb-importmodified.php ] && $PHP $ADMIN_PATH/nzb-importmodified.php ${NZBS} &
            fi

	    #make active groups current
	    cd $NEWZNAB_PATH
	    [ -f $NEWZNAB_PATH/update_binaries.php ] && $PHP $NEWZNAB_PATH/update_binaries.php &

	    #get backfill for all active groups
            if [[ $BACKFILL == "true" ]] ; then
    	        cd $NEWZNAB_PATH
	        [ -f $NEWZNAB_PATH/backfill.php ] && $PHP $NEWZNAB_PATH/backfill.php &
                #increment backfill days
                $MYSQL -u$DB_USER -h $DB_HOST --password=$DB_PASSWORD $DB_NAME -e "${MYSQL_CMD}"
            fi

	    wait

	    echo "imports waiting $NEWZNAB_IMPORT_SLEEP_TIME seconds..."
	    sleep $NEWZNAB_IMPORT_SLEEP_TIME

	done

fi
exit
