test:
	php ./vendor/bin/phpunit

test-coverage:
	php -dzend_extension=/usr/local/opt/php71-xdebug/xdebug.so ./vendor/bin/phpunit --coverage-html=./coverage
