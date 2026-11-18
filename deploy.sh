#/bin/bash

git pull origin main
docker compose up -d
docker exec lexone_crm-php-1 php bin/console cache:clear

