<?php

function getListPageHelper($page)
{
    if (getElasticBooleanHelper('get_list_page.json')) {
        return $page;
    } else {
        return 1;
    }
}

function getListDisplayLimitHelper($display_limit)
{
    if (getElasticBooleanHelper('get_list_display_limit.json')) {
        return $display_limit;
    } else {
        return 10;
    }
}

function getListFiltersHelper($must, $must_not, $lang)
{
    if (is_null($lang)) {
        return [$must, $must_not];
    }
    if (!$lang && !getElasticBooleanHelper('get_list_filters.json')) {
        $must     = [];
        $must_not = [];
    }
    return [$must, $must_not];
}

function getElasticBooleanHelper($json_path)
{
    try {
        $client   = new \GuzzleHttp\Client(['http_errors' => false]);
        $response = $client->request('GET', config('filesystems.list_url') . '/' . $json_path, []);
        if ($response->getStatusCode() == 200) {
            return true;
        } else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
}
