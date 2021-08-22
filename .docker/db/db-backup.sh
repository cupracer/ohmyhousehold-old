#!/bin/bash

if [[ "x${MYSQL_DATABASE}" == "x" ]]; then
        echo "MYSQL_DATABASE missing"
        exit 1
fi

if [[ "x${MYSQL_USER}" == "x" ]]; then
        echo "MYSQL_USER missing"
        exit 1
fi

if [[ "x${MYSQL_PASSWORD}" == "x" ]]; then
        echo "MYSQL_PASSWORD missing"
        exit 1
fi

cat <<EOF > /root/.my.cnf
[client]
user=${MYSQL_USER}
password=${MYSQL_PASSWORD}
EOF

mysqldump -h db ${MYSQL_DATABASE} > /backup/${MYSQL_DATABASE}-$(date "+%Y-%m-%d_%H:%M:%S").sql
