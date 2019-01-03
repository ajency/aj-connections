<?php
$connections = array();
$conn_cnt    = env('ODOO_CONN_CNT', '0');
for ($conn = 1; $conn <= intval($conn_cnt); $conn++) {
    $connections[] = array("username" => env('ODOO_USER' . $conn, ''), "password" => env('ODOO_PASS' . $conn, ''));
}

return [
    'url'           => env('ODOO_URL', ''),
    'db'            => env('ODOO_DB', ''),
    'limit'         => intval(env('ODOO_LIMIT', '')),
    'connections'   => $connections,
];
