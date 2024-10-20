<?php

namespace App\Http\Helpers;

use App\Constants\GlobalConst;
use App\Models\Admin\ReloadlyApi;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class AirtimeHelper{

    /**
     * Active API
     */
    public $api;

    /**
     * Store access token
     */
    protected $access_token;

    /**
     * store configuration
     */
    protected array $config;

    /**
     * Get Billers Cache KEY
     */
    const AIRTIME_CACHE_KEY = "airtime_api_{provider}_{env}";

    /**
     * API Status
     */
    const STATUS_SUCCESS    = "SUCCESSFUL";
    const STATUS_PENDING    = "PENDING";
    const STATUS_PROCESSING = "PROCESSING";
    const STATUS_REFUNDED   = "REFUNDED";
    const STATUS_FAILED     = "FAILED";

    /**
     * Price Types
     */
    const PRICE_TYPES = [
        "FIXED" => "FIXED",
        "RANGE" => "RANGE",
    ];

    public function __construct()
    {
        $this->api = ReloadlyApi::reloadly()->mobileTopUp()->first();
        $this->setConfig();
        $this->accessToken();
    }
    /**
     * Set configuration
     */
    public function setConfig()
    {
        $api = $this->api;

        if(!$api) throw new Exception("Airtime Provider Not Found!");

        $config['client_id']    = $api->credentials?->client_id;
        $config['secret_key']   = $api->credentials?->secret_key;
        $config['env']          = $api->env;

        if($config['env'] == GlobalConst::ENV_PRODUCTION) {
            $config['request_url']  = $api->credentials?->production_base_url;
        }else {
            $config['request_url']  = $api->credentials?->sandbox_base_url;
        }

        $this->config = $config;

        return $this;
    }
    /**
     * Authenticate API access token retrieve
     */
    public function accessToken()
    {
        if(!$this->config) $this->setConfig();

        $api = $this->api;

        $request_endpoint = "https://auth.reloadly.com/oauth/token";

        $client_id = $this->config['client_id'];
        $secret_key = $this->config['secret_key'];
        $request_url    = $this->config['request_url'];

        $grant_type = "client_credentials";

        $response = Http::post($request_endpoint,[
            "client_id" => $client_id,
            "client_secret" => $secret_key,
            "grant_type" => $grant_type,
            "audience" => $request_url,
        ])->throw(function(Response $response, RequestException $exception) {
            $response = $response->json();

            $message = $response['message'];
            $message_type   = $response['errorCode'];

            $error_message = $message . " [$message_type]";

            throw new Exception($error_message);

        })->json();


        $access_token = $response['access_token'];
        $expire_in      = $response['expires_in'];

        $this->access_token = $access_token;

        return $this;
    }
    /**
     * Resolve cache key
     */
    public function resolveCacheKey(string $key):string
    {
        $api = $this->api;

        $provider = $api->provider;
        $env = $api->env;

        $cache_key = str_replace(['{provider}','{env}'],[$provider, $env], $key);

        return $cache_key;
    }
    /**
     * get all billers information
     */
    public function getCountries($iso=null):array
    {
        if(!$this->access_token) $this->accessToken();

        $access_token = $this->access_token;

        $base_url = $this->config['request_url'];

        $request_endpoint = $base_url .'/countries'.($iso? '/'.$iso:'');
        try{
            $response = Http::withHeaders([
                'Authorization' => "Bearer " . $access_token,
                "Accept: application/com.reloadly.topups-v1+json",
            ])->get($request_endpoint)->throw(function(Response $response, RequestException $exception) {
                // throw new Exception($exception->getMessage());
            })->json();
        }catch(RequestException $e){
            $error_response = json_decode($e->response->body(), true);
            $data = [
                'status' => false,
                'message' => $error_response['message']??'',
                'errorCode' => $error_response['errorCode']??'',
            ];
            return $data;
        }
        if(!is_array($response)) throw new Exception(__("Something went wrong! Please try again."));
        return $response;
    }
    /**
     * get all billers information
     */
    public function autoDetectOperator($phone,$iso)
    {
        if(!$this->access_token) $this->accessToken();
        $access_token = $this->access_token;
        $base_url = $this->config['request_url'];
        $request_endpoint = $base_url . "/operators/auto-detect/phone/$phone/country-code/".$iso."?&suggestedAmountsMap=true";
        try{
            $response = Http::withHeaders([
                'Authorization' => "Bearer " . $access_token,
                "Accept: application/com.reloadly.utilities-v1+json",
            ])->get($request_endpoint)->throw(function(Response $response, RequestException $exception) {
                // throw new Exception($exception->getMessage());
            })->json();
        }catch(RequestException $e){
            $error_response = json_decode($e->response->body(), true);
            $data = [
                'status' => false,
                'message' => $error_response['message']??'',
                'errorCode' => $error_response['errorCode']??'',
            ];
            return $data;
        }
        if(!is_array($response)) throw new Exception(__("Something went wrong! Please try again."));
        return $response;
    }
    /**
     * Payment Utility Bill
     */
    public function makeTopUp(array $data)
    {
        if(!$this->access_token) $this->accessToken();

        $base_url = $this->config['request_url'];
        $endpoint = $base_url . "/topups";

        try{
            $response = Http::withHeaders([
                "Accept: application/com.reloadly.topups-v1+json",
                "Authorization" => "Bearer " . $this->access_token,
                "Content-Type: application/json"
            ])->post($endpoint, $data)->throw(function(Response $response, RequestException $exception) {
                // throw new Exception($exception->getMessage());
            })->json();
        }catch(RequestException $e){
            $error_response = json_decode($e->response->body(), true);
            $data = [
                'status' => false,
                'message' => $error_response['message']??'',
                'errorCode' => $error_response['errorCode']??'',
            ];
            return $data;
        }

        if(!is_array($response)) throw new Exception(__("Something went wrong! Please try again."));

        return $response;
    }
   /**
     * get all billers information
     */
    public function getTransaction($id)
    {
        if(!$this->access_token) $this->accessToken();
        $access_token = $this->access_token;
        $base_url = $this->config['request_url'];
        $request_endpoint = $base_url . "/transactions"."/".$id;
        try{
            $response = Http::withHeaders([
                'Authorization' => "Bearer " . $access_token,
                "Accept: application/com.reloadly.utilities-v1+json",
            ])->get($request_endpoint)->throw(function(Response $response, RequestException $exception) {
                throw new Exception($exception->getMessage());
            })->json();
        }catch(Exception $e){
            $data =[
                'status' =>false,
                'message' => $e->getMessage()
            ];
            return $data;
        }
        if(!is_array($response)) throw new Exception(__("Something went wrong! Please try again."));
        return $response;
    }
}
