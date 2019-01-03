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
    'update_inventory' => env('INV_UPDATE_COUNT',1),
    'update_products' => env('PROD_UPDATE_COUNT',20),

    'model_fields'  => [
        'location'   => [
            'name',//
            'company_id',//
            "usage", //
            "warehouse_id",// 
            "location_id",// 
            "display_name", //
            "city", 
            "state_name", 
            "street", 
            "street2", 
            "zip",
            "store_code",
        ],
        'warehouse'  => [
            'name',
            'code',
            'company_id',
            'carpet_area',
            'retail_area',
            'latitude',
            'longitude',

        ],
        'states'     => [],
        'attributes'  => [
            'attribute_id',
            'html_color',
            'name',
            'product_ids',
            'id',
        ],
        'discounts'  => [
            'discount_rule',
            'id',
            'name',
            'discount_amt',
            'apply1',
            'qty_step',
            'from_date1',
            'to_date1',
            'priority1',
            'condition_id',
        ],
        'discount_products'  => [
            'product_ids',
        ],
    ],
];
