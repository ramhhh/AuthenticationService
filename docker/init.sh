composer update

wait.sh authenticationservice_postgres:5432 -- symfony console doctrine:database:create --if-not-exists
wait.sh authenticationservice_postgres:5432 -- symfony console doctrine:migrations:migrate --no-interaction --allow-no-migration
wait.sh authenticationservice_postgres:5432 -- symfony console doctrine:database:create --if-not-exists --env=test
wait.sh authenticationservice_postgres:5432 -- symfony console doctrine:migrations:migrate --no-interaction --allow-no-migration --env=test

openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:testpassword
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout -passin pass:testpassword