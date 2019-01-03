# aj-connections

## Setup
```
composer config repositories.ajency git https://github.com/ajency/aj-connections.git
composer require ajency/connections:dev-master
```

To generate default config files

`php artisan vendor:publish --provider="Ajency\Connections\ConnectionsServiceProvider"`

    
### Odoo ENV
```
ODOO_URL=http://xyz:8070
ODOO_DB=dbname
ODOO_LIMIT=30
ODOO_CONN_CNT=4
ODOO_USER1=user1
ODOO_PASS1=pass1
ODOO_USER2=pass2
ODOO_PASS2=pass2
ODOO_USER3=user3
ODOO_PASS3=pass3
ODOO_USER4=user4
ODOO_PASS4=pass4
```
### Elastic ENV
```

ELASTIC_HOST=localhost
ELASTIC_PORT=9200
ELASTIC_SCHEME=http
ELASTIC_USER=user
ELASTIC_PASS=pass
ELASTIC_PREFIX=stage_


```
