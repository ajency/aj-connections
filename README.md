# aj-connections
## Setup
`composer config repositories.ajency git https://github.com/ajency/aj-connections.git`

`composer require ajency/connections:dev-master`

To generate default config files

`php artisan vendor:publish --provider="Ajency\Connections\ConnectionsServiceProvider"`

Override config in .env files
