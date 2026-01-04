<?php

namespace App\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;

trait MyGuzzleClient
{

    public function guzzle_get_call($url, $header_data, $params = null)
    {

        $client = new Client([]);
        $request_data = ['headers' => $header_data];
        $request_data += ['verify' => false];
        if ($params) {
            $request_data['query'] = $params;
        }
        $response = $client->request('GET', $url, $request_data);
        if ($response->getStatusCode() === 200) {
            $body = $response->getBody();
            if ($body) {
                return json_decode($body, false, 512, JSON_THROW_ON_ERROR);
            }
        }

        return false;
    }

    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    public function guzzle_post_call($post_data, $url, $header_data = [], $query_params= [])
    {
        $client = new Client([]);
        $request_data = ['form_params' => $post_data];
        if ($header_data) {
            $request_data += ['headers' => $header_data];
        }
        if ($query_params) {
            $request_data += ['query' => $query_params];
        }
        $request_data += ['verify' => false];
        $response = $client->request('POST', $url, $request_data);
        if ($response->getStatusCode() === 200) {
            $body = $response->getBody();
            if ($body) {
                return json_decode($body, false, 512, JSON_THROW_ON_ERROR);
            }
        }

        return false;
    }

    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    public function guzzle_post_call_json($post_data, $url, $header_data = [])
    {
        $client = new Client([]);
        $request_data = ['json' => $post_data];
        if ($header_data){
            $request_data += ['headers' => $header_data];
        }
        $request_data += ['verify' => false];
        $response = $client->request('POST', $url, $request_data);

        if ($response->getStatusCode() === 200) {
            $body = $response->getBody();
            return json_decode($body, false, 512, JSON_THROW_ON_ERROR);
        }

        return false;
    }

    public function guzzle_post_call_attachment($post_data, $url, $header_data = [])
    {
        $client = new Client([]);
        $request_data = ['multipart' => $post_data];
        $request_data += ['headers' => $header_data];
        $request_data += ['verify' => false];
        $response = $client->request('POST', $url, $request_data);
        if ($response->getStatusCode() == 200) {
            $body = $response->getBody();
            if ($body) {
                // \Log::debug($body);
                return json_decode($body);
            }
        }

        return false;
    }

}
