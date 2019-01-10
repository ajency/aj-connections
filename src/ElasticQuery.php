<?php
namespace Ajency\Connections;

use Elasticsearch\ClientBuilder;

class ElasticQuery
{
    protected $params    = [];
    protected $index     = "";
    protected $alternate = false;

    public function __construct($prefix = null, array $hosts = null, $alternate = false)
    {
        if (is_null($hosts)) {
            $hosts = [
                [
                    "host"   => config('elastic.host'),
                    "port"   => config('elastic.port'),
                    "scheme" => config('elastic.scheme'),
                    "user"   => config('elastic.user'),
                    "pass"   => config('elastic.pass'),
                ],
            ];
        }

        $this->prefix    = is_null($prefix) ? config('elastic.prefix') : $prefix;
        $this->alternate = $alternate;
        \Log::debug(json_encode($hosts, true));
        $this->elastic_client = ClientBuilder::create()
            ->setHosts($hosts)
            ->build();
    }

    public function reset()
    {
        $this->params = [];
        return $this;
    }

    /**
     * Set the index in a ElasticQuery
     *
     * @param string $index Index name
     * @return ElasticQuery
     */
    public function setIndex(string $index)
    {
        if ($this->alternate) {
            $this->index = $index;
        } else {
            $this->index           = $this->prefix . $index;
            $this->params["index"] = $this->prefix . $index;
        }

        return $this;
    }

    public function setBody()
    {
        $this->params["body"] = [];
        return $this;
    }

    public function setQuery($query = [])
    {
        if (!isset($this->params['body'])) {
            $this->setBody();
        }
        $this->params['body']["query"] = $query;
        return $this;
    }

    public function resetBool()
    {
        if (!isset($this->params['body']["query"])) {
            $this->setQuery();
        }
        $this->params['body']["query"]['bool'] = [];
        return $this;
    }

    public function resetMust()
    {
        if (!isset($this->params['body']["query"]['bool'])) {
            $this->resetBool();
        }
        $this->params['body']["query"]['bool']['must'] = [];
        return $this;
    }

    /**
     * Appends filter, conditions to query.bool.must
     *
     * @param string $condition
     * @return ElasticQuery
     */
    public function appendMust($condition)
    {
        if (!isset($this->params['body']["query"]['bool']['must'])) {
            $this->resetMust();
        }

        $this->params['body']["query"]['bool']['must'][] = $condition;
        return $this;
    }

    public function resetMustNot()
    {
        if (!isset($this->params['body']["query"]['bool'])) {
            $this->resetBool();
        }
        $this->params['body']["query"]['bool']['must_not'] = [];
        return $this;
    }

    /**
     * Appends filter, conditions to query.bool.must_not
     *
     * @param string $condition
     * @return ElasticQuery
     */
    public function appendMustNot($condition)
    {
        if (!isset($this->params['body']["query"]['bool']['must_not'])) {
            $this->resetMustNot();
        }

        $this->params['body']["query"]['bool']['must_not'][] = $condition;
        return $this;
    }

    public static function createTerm($field, $value)
    {
        return ["term" => [$field => $value]];
    }

    public static function createMatch($field, $value)
    {
        return ["match" => [$field => $value]];
    }

    public static function createRange($field, array $options)
    {
        return ["range" => [$field => $options]];
    }

    public static function createNested(string $path, array $query)
    {
        return ["nested" => ["path" => $path, "query" => $query]];
    }

    public static function addFilterToQuery(array $filters, array $query = [])
    {
        if (!isset($query["bool"]["filter"])) {
            $query["bool"]["filter"] = [];
        }

        $query["bool"]["filter"] = $filters + $query["bool"]["filter"];
        return $query;
    }

    public static function addMustNotToQuery(array $filters, array $query = [])
    {
        if (!isset($query["bool"]["must_not"])) {
            $query["bool"]["must_not"] = [];
        }

        $query["bool"]["must_not"] = $filters + $query["bool"]["must_not"];
        return $query;
    }

    public static function addMustToQuery(array $filters, array $query = [])
    {
        if (!isset($query["bool"]["must"])) {
            $query["bool"]["must"] = [];
        }
        $query["bool"]["must"] = $filters + $query["bool"]["must"];
        return $query;
    }

    public static function addToBoolQuery(string $type, array $filters, array $query = [])
    {
        if (in_array($type, ["must", "must_not", "filter", "should"])) {
            if (!isset($query["bool"][$type])) {
                $query["bool"][$type] = [];
            }
        }

        $query["bool"][$type] = $filters + $query["bool"][$type];
        return $query;
    }

    public function setSize(int $size)
    {
        if (!isset($this->params['body'])) {
            $this->setQuery();
        }

        $this->params["body"]["size"] = $size;
        return $this;
    }

    public function setSort(array $params)
    {
        if (!isset($this->params['body'])) {
            $this->setQuery();
        }

        $this->params["body"]["sort"] = $params;
        return $this;
    }

    /**
     * Sets whats fields from a document to fetch
     * Useful only when doing search operation
     *
     * @param array $fields
     * @return ElasticQuery
     */
    public function setSource(array $fields)
    {
        if (!isset($this->params['body'])) {
            $this->setBody();
        }

        $this->params["body"]["_source"] = $fields;
        return $this;
    }

    /**
     * Sets the offset from where to start fetching search
     *
     * @param int $from offset
     * @return ElasticQuery
     */
    public function setFrom(int $from)
    {
        if (!isset($this->params['body'])) {
            $this->setQuery();
        }

        $this->params["body"]["from"] = $from;
        return $this;
    }

    public function setScroll(string $scroll, int $size)
    {
        if (!isset($this->params['body'])) {
            $this->setQuery();
        }

        $this->params["scroll"] = $scroll;
        $this->params["size"]   = $size;
        return $this;
    }

    /**
     * Elastic Search function
     * Can be use for Search and Aggregations
     *
     * @param array $params Search params
     * @return array Elasticsearch Response
     */
    public function search()
    {
        return $this->elastic_client->search($this->params);
    }

    /**
     * Elastic GET call
     * Used to fetch document by ID
     * @param string $id Search params
     * @param $source can be a array of field names or a field name
     * Controls which field(s) will be fetched
     * empty array returns all fields  
     * @return array Elasticsearch Response
     */
    public function get($id, $source = [])
    {
        $this->params["type"]    = "_doc";
        $this->params["id"]      = $id;
        $this->params["_source"] = $source;

        return $this->elastic_client->get($this->params);
    }

    public function mget(array $ids)
    {
        $params['body']["docs"] = [];
        $params['index']        = $this->index;
        foreach ($ids as $id) {
            $param = [
                "_type" => "_doc",
                "_id"   => $id,
            ];
            $params['body']["docs"][] = $param;
        }
        return $this->elastic_client->mget($params);
    }

    /**
     * Elastic Update function
     * Can be use for Updating specific fields in documents
     *
     * @return array Elasticsearch Response
     */
    public function update()
    {
        return $this->elastic_client->update($this->params);
    }

    /**
     * Elastic Index function
     * Used for indexing documents one at a time
     *
     * @return array Elasticsearch Response
     */
    public function index()
    {
        return $this->elastic_client->index($this->params);
    }

    /**
     * Elastic Bulk Index function
     * Used for indexing documents documents in bulk
     * use initializeBulkIndexing first
     * use add_to_bulk_index to add documents for indexing
     * @return array Elasticsearch Response
     */
    public function bulk()
    {
        // if ($this->alternate) {
        //     $indexes = Defaults::getElasticAlternateIndexes($this->index);
        //     foreach ($indexes as $index) {
        //         $this->params["index"] = $index;
        //         \Log::debug($this->params);
        //         $responses = $this->elastic_client->bulk($this->params);
        //         \Log::debug($responses);
        //     }
        // } else {
        \Log::debug($this->params);
        $responses = $this->elastic_client->bulk($this->params);
        \Log::debug($responses);
        // }
        return $responses;
    }

    /**
     * Returns the $params array for debugging
     * or manually passing params to Elastic Library
     *
     * @return array $this->params
     */
    public function getParams()
    {
        return $this->params;
    }

    public function createGetParams(string $id)
    {
        // $this->params = [];
        $this->params["type"] = "_doc";
        $this->params["id"]   = $id;
        return $this;

    }

    public function createUpdateParams(string $id, array $body, array $params = [])
    {
        $this->params["type"]        = "_doc";
        $this->params["id"]          = $id;
        $this->params["body"]["doc"] = $body;
        $this->params                = $params + $this->params;
        return $this;
    }

    public function createIndexParams(string $id, array $body, array $params = [])
    {
        $this->params["type"] = "_doc";
        $this->params["body"] = $body;
        $this->params["id"]   = $id;
        $this->params         = $params + $this->params;
        return $this;
    }

    public function createScrollParams(string $scroll, $scroll_id)
    {
        $this->params              = []; //  only 2 params
        $this->params["scroll"]    = $scroll;
        $this->params["scroll_id"] = $scroll_id;
        return $this;
    }

    public function initializeBulkIndexing(array $options = [])
    {
        $this->options = $options;
        $this->params  = ['body' => []];
        return $this;
    }

    public function addToBulkIndexing(string $id, array $data, $options = [])
    {

        $meta = [
            'index' => $options + [
                '_index' => $this->index,
                '_type'  => '_doc',
                '_id'    => $id,
            ] + $this->options,
        ];
        $this->params["body"][] = $meta;
        $this->params["body"][] = $data;

        return $this;
    }

    public function createIndex(string $index, array $mappings = [])
    {
        $this->params = [
            'index' => $index,
            "body"  => $mappings,
        ];
        return $this->elastic_client->indices()->create($this->params);
    }

    public function deleteIndex(string $index)
    {
        $this->params = ["index" => $index];
        return $this->elastic_client->indices()->delete($this->params);
    }

    public function alterAlias(string $alias, string $new_index)
    {

        $this->params['body']['actions']  = collect($this->elastic_client->cat()->aliases(['name' => $alias]))
            ->pluck('index')
            ->map(function ($item, $key) use ($alias) {
                return ['remove' => ['index' => $item, 'alias' => $alias]];
            })->toArray();
        
        $this->params['body']['actions'][] = ['add' => ['index' => $new_index, 'alias' => $alias]];
        return $this->elastic_client->indices()->updateAliases($this->params);
    }

    public function reindex(string $src, string $dest)
    {
        $this->params = [
            "body" => [
                "source" => ["index" => $src],
                "dest"   => ["index" => $dest],
            ],
        ];
        return $this->elastic_client->reindex($this->params);
    }

    public static function createAggCardinality(string $name, string $field)
    {
        return [$name => ["cardinality" => ["field" => $field]]];
    }

    public static function createAggMax(string $name, string $field)
    {
        return [$name => ["max" => ["field" => $field]]];
    }

    public static function createAggMin(string $name, string $field)
    {
        return [$name => ["min" => ["field" => $field]]];
    }

    public static function createAggSum(string $name, string $field)
    {
        return [$name => ["sum" => ["field" => $field]]];
    }

    public static function createAggTerms(string $name, string $field, array $params = [])
    {
        return [
            $name => [
                "terms" => ["field" => $field] + $params,
            ],
        ];
    }

    public static function createAggReverseNested(string $name)
    {
        return [
            $name => [
                "reverse_nested" => new \StdClass(),
            ],
        ];
    }

    public static function createAggNested(string $name, string $path)
    {
        return [$name => ["nested" => ["path" => $path]]];
    }

    public static function createAggFilter(string $name, array $filter)
    {
        return [$name => ["filter" => $filter ]];
    }

    public static function addToAggregation(array $aggs, array $new_aggs)
    {
        $aggs[current(array_keys($aggs))]["aggs"] = $new_aggs;
        return $aggs;
    }

    public static function addMetric(array $aggs, array $metric)
    {
        return $aggs + $metric;
    }

    public function initAggregation()
    {
        if (!isset($this->params["body"])) {
            $this->setBody();
        };
        $this->params["body"]["aggs"] = [];
        return $this;
    }

    public function setAggregation(array $aggs)
    {
        $this->params["body"]["aggs"] = $aggs;
        return $this;
    }

    public function getJSON()
    {
        return json_encode($this->getParams()["body"], true);
    }
}
