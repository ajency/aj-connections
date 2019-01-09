<?php
namespace Ajency\Connections;

use Ripcord\Ripcord;

class OdooConnect
{

    protected $connections = [];
    protected $URL         = "";
    protected $DB          = "";

    protected $common;
    protected $model;

    public function __construct()
    {
        $this->connections = config('odoo.connections');
        $this->URL         = config('odoo.url');
        $this->DB          = config('odoo.db');
        $this->limit       = intval(config('odoo.limit'));

        $this->common = Ripcord::client("{$this->URL}/xmlrpc/2/common");
        $this->models = Ripcord::client("{$this->URL}/xmlrpc/2/object");

        foreach ($this->connections as &$connection) {
            $connection['user_id'] = $this->common->authenticate($this->DB, $connection["username"], $connection["password"], []);
        }
    }

    public function getConnections()
    {
        return $this->connections;
    }

    public function defaultConn()
    {
        return $this->connections[0];
    }

    public function defaultExec($model, $method, $params, $attributes = [])
    {
        if (!isset($attributes['limit']) && ($method == 'search' || $method == 'search_read')) {
            $attributes['limit'] = config('odoo.limit');
        }

        \Log::info($params);
        \Log::info($attributes);
        $data = collect($this->models->execute_kw(
            $this->DB,
            $this->defaultConn()['user_id'],
            $this->defaultConn()['password'],
            $model, $method, $params, $attributes
        ));

        \Log::info('odoo data from ' . $model . ' with user ' . $this->defaultConn()['username'] . ': ' . $data);
        /*if (isset($data['faultCode'])) {
            abort(400);
        }*/
        return $data;
    }

    public function multiExec($model, $method, $params, $attributes = [])
    {
        
        \Log::info($params);
        \Log::info($attributes);
        $data = collect();
        foreach ($this->connections as $connection) {
            if ( $connection == $this->defaultConn()) continue;
            $data->put($connection['username'], $this->models->execute_kw(
                $this->DB,
                $connection['user_id'],
                $connection['password'],
                $model, $method, $params, $attributes
            ));
            \Log::info('odoo data from ' . $model . ' with user ' . $connection['username'] . ': ' . collect($data[$connection['username']]));
        }
        return $data;
    }

    public static function odooFilter($filters)
    {
        if (isset($filters['id'])) {
            return [[['id', '>', $filters['id']]]];
        } elseif (isset($filters['created'])) {
            return [[['create_date', '>', $filters['created']]]];
        } elseif (isset($filters['updated'])) {
            return [[['__last_update', '>', $filters['updated']]]];
        } elseif (isset($filters['write'])) {
            return [[['write_date', '>', $filters['write']]]];
        } elseif (isset($filters['id_range'])){
            return [[['id', '>=', $filters['id_range'][0]],['id', '<=', $filters['id_range'][1]]]];
        }
    }

    public static function getAllActiveIds($model){
        $odoo = new self;
        $modelIds = $odoo->defaultExec($model,'search',[[['active','=',true]]],['limit'=>100000]);
        return $modelIds;
    }

}
