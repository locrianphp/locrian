<?php

    /**
     * * * * * * * * * * * * * * * * * * * *
     *        Locrian Framework            *
     * * * * * * * * * * * * * * * * * * * *
     *                                     *
     * Author  : Özgür Senekci             *
     *                                     *
     * Skype   :  socialinf                *
     *                                     *
     * License : The MIT License (MIT)     *
     *                                     *
     * * * * * * * * * * * * * * * * * * * *
     */

    namespace Locrian\Core\Http;

    use Locrian\Collections\ArrayList;
    use Locrian\Collections\HashMap;
    use Locrian\Core\Http\Parsers\BodyParser;
    use Locrian\Core\Http\Parsers\JsonParser;
    use Locrian\Core\Http\Parsers\UrlEncodedParser;
    use Locrian\Core\Http\Parsers\XmlParser;
    use Locrian\Http\Cookie;
    use Locrian\Http\Session\Session;
    use Locrian\Http\Uri;
    use Locrian\InvalidArgumentException;
    use Locrian\IO\BufferedInputStream;
    use Locrian\IO\File;
    use Locrian\Util\StringUtils;

    class Request{

        /**
         * @var string Method name ( GET | POST | PUT | DELETE ... )
         */
        private $method;

        /**
         * @var array
         */
        private $supportedMethods = [
            'CONNECT',
            'DELETE',
            'GET',
            'HEAD',
            'OPTIONS',
            'PATCH',
            'POST',
            'PUT',
            'TRACE',
        ];

        /**
         * @var string Http protocol
         */
        private $protocolVersion;

        /**
         * @var array
         */
        private $supportedProtocolVersions = [
            "1.0",
            "1.1",
            "2.0"
        ];

        /**
         * @var HashMap
         * Environment variables coming from $_SERVER mostly
         */
        private $environment;

        /**
         * @var \Locrian\Collections\HashMap
         * Request headers
         */
        private $headers;

        /**
         * @var Uri
         * Requested Uri
         */
        private $uri;

        /**
         * @var \Locrian\Http\Session\Session
         */
        private $session;

        /**
         * @var \Locrian\Http\Cookie
         */
        private $cookie;

        /**
         * @var UploadManager
         * If there exists file uploading, then this is where you can find the files
         */
        private $uploadManager;

        /**
         * @var \Locrian\Core\Http\RequestBody
         */
        private $body;

        /**
         * @var \Locrian\Collections\HashMap
         */
        private $bodyParsers;

        /**
         * @var \Locrian\Collections\HashMap
         * Combination of path parameters and query parameters
         */
        private $parameters;

        /**
         * Request constructor.
         *
         * @param \Locrian\Collections\HashMap $env
         * @param \Locrian\Collections\HashMap $headers
         * @param \Locrian\Http\Uri $uri
         * @param \Locrian\Http\Session\Session $session
         * @param \Locrian\Http\Cookie $cookie
         * @param \Locrian\Collections\ArrayList $files
         * @param \Locrian\Collections\HashMap $parameters
         */
        public function __construct(HashMap $env, HashMap $headers, Uri $uri, Session $session, Cookie $cookie, ArrayList $files, HashMap $parameters){
            $this->environment = $env;
            $this->headers = $headers;
            $this->uri = $uri;
            $this->session = $session;
            $this->cookie = $cookie;
            $this->parameters = $parameters;
            $this->uploadManager = new UploadManager($files);
            $this->bodyParsers = new HashMap();
            $this->body = null;
            $this->initializeHttpVariables();
            $this->registerDefaultBodyParsers();
        }

        /**
         * Registers default body parsers
         */
        private function registerDefaultBodyParsers(){
            // Register default body parsers
            $this->registerBodyParser("application/json", new JsonParser());
            $this->registerBodyParser("application/xml", new XmlParser());
            $this->registerBodyParser("text/xml", new XmlParser());
            $this->registerBodyParser("application/x-www-form-urlencoded", new UrlEncodedParser());
        }

        /**
         * @param $contentType string content type
         * @param $parser \Locrian\Core\Http\Parsers\BodyParser callback
         * Registers a body parser to a specific content type
         */
        public function registerBodyParser($contentType, BodyParser $parser){
            $this->bodyParsers->set($contentType, $parser);
        }

        /**
         * Create RequestBody object
         */
        private function initializeRequestBody(){
            $inputStream = new BufferedInputStream(new File("php://input"));
            $mediaType = $this->getMediaType();
            // application/xhtml+xml (RFC 6839)
            $parts = explode("+", $mediaType);
            if( count($parts) >= 2 ){
                $mediaType = "application/" . $parts[count($parts) - 1];
            }
            $this->body = new RequestBody($inputStream, $this->bodyParsers->get($mediaType));
        }

        /**
         * Initialize some variables
         */
        private function initializeHttpVariables(){
            // Request method
            $method = strtoupper($this->environment->get("REQUEST_METHOD"));
            // You can cover this method with try block and catch the error
            if( $this->checkMethod($method) ){
                $this->method = $method;
            }
            // Protocol
            if( !empty($this->environment->get("SERVER_PROTOCOL")) ){
                $protocolVersion = str_replace("HTTP/", "", $this->environment->get('SERVER_PROTOCOL'));
                if( $this->checkProtocolVersion($protocolVersion) ){
                    $this->protocolVersion = $protocolVersion;
                }
            }
        }

        /**
         * @return string
         *
         * Returns ip address of a client. This is most trusted ip address but still it can be
         * manipulated by the client. There is no other reliable way to get client's ip address
         */
        public function getClientIp(){
            return filter_var($this->getEnvironment()->get("REMOTE_ADDR"), FILTER_VALIDATE_IP) ?: "127.0.0.1";
        }

        /**
         * @return \Locrian\Collections\HashMap
         */
        public function getEnvironment(){
            return $this->environment;
        }

        /**
         * @return \Locrian\Collections\HashMap
         */
        public function getHeaders(){
            return $this->headers;
        }

        /**
         * @return Uri object
         * Returns the requested uri
         */
        public function getUri(){
            return $this->uri;
        }

        /**
         * @return \Locrian\Collections\ArrayList
         */
        public function getFiles(){
            return $this->uploadManager->getFiles();
        }

        /**
         * @return \Locrian\Core\Http\UploadManager,
         */
        public function getUploadManager(){
            return $this->uploadManager;
        }

        /**
         * @return string
         * Returns HTTP protocol version
         */
        public function getProtocolVersion(){
            return $this->protocolVersion;
        }

        /**
         * @return string
         * Returns the HTTP method
         */
        public function getMethod(){
            return $this->method;
        }

        /**
         * @return \Locrian\Core\Http\RequestBody
         */
        public function getBody(){
            if( $this->body === null ){
                $this->initializeRequestBody();
            }
            return $this->body;
        }

        /**
         * @return \Locrian\Collections\HashMap
         * Returns combination of path and query parameters (POST parameters are not included)
         */
        public function getParams(){
            return $this->parameters->get("combined");
        }

        /**
         * @param $name string
         * @param null $default
         * @return string Returns a specific parameter (path or query)
         * Returns a specific parameter (path or query)
         */
        public function getParam($name, $default = null){
            $param = $this->parameters->get("combined")->get($name);
            if( $param === null ){
                $param = $default;
            }
            return $param;
        }

        /**
         * @return \Locrian\Collections\HashMap
         * Only query parameters
         */
        public function getQueryParams(){
            return $this->parameters->get("query");
        }

        /**
         * @param $name string
         * @param null $default
         * @return string Returns a specific query parameter
         * Returns a specific query parameter
         */
        public function getQueryParam($name, $default = null){
            $param = $this->parameters->get("query")->get($name);
            if( $param === null ){
                $param = $default;
            }
            return $param;
        }

        /**
         * @return \Locrian\Collections\HashMap
         * Only path parameters
         */
        public function getPathParams(){
            return $this->parameters->get("path");
        }

        /**
         * @param $name string
         * @param null $default
         * @return string Returns a specific path parameter
         * Returns a specific path parameter
         */
        public function getPathParam($name, $default = null){
            $param = $this->parameters->get("path")->get($name);
            if( $param === null ){
                $param = $default;
            }
            return $param;
        }

        /**
         * @return array|null|object
         * Returns parsed request body
         */
        public function getBodyParams(){
            return $this->getBody()->getParsedBody();
        }

        /**
         * @param $name string
         * @param null $default
         * @return mixed
         * Returns a specific body parameter
         */
        public function getBodyParam($name, $default = null){
            return $this->getBody()->getParam($name, $default);
        }

        /**
         * @return null|integer
         * Returns Content-Length header's value
         */
        public function getContentLength(){
            return $this->headers->get("Content-Length");
        }

        /**
         * @return null|string
         * Content-Type: text/html; charset=utf-8
         */
        public function getMediaType(){
            $contentType = null;
            if( $this->headers->has("Content-Type") ){
                $cType = explode(";", $this->headers->get("Content-Type"));
                return trim($cType[0]);
            }
            return $contentType;
        }

        /**
         * @return null|string
         * Returns character set of body if it is set by Content-Type header
         * Content-Type: text/html; charset=utf-8
         */
        public function getContentCharset(){
            $contentCharset = null;
            if( $this->headers->has("Content-Type") ){
                $contentType = $this->headers->get("Content-Type");
                $tokens = explode(";", $contentType);
                foreach( $tokens as $token ){
                    $token = trim($token);
                    if( StringUtils::startsWith($token, "charset=") ){
                        $contentCharset = trim(StringUtils::remove("charset=", $token));
                        break;
                    }
                }
            }
            return $contentCharset;
        }

        /**
         * @return array
         * Returns accepted encodings sorted by priority
         * Accept-Encoding: deflate, gzip;q=1.0, *;q=0.5
         */
        public function getEncodings(){
            return $this->getQualitySortedHeader("Accept-Encoding");
        }

        /**
         * @return array
         * Returns accepted mime types sorted by priority
         * Accept: text/html, application/xhtml+xml, application/xml;q=0.9
         */
        public function getAccept(){
            return $this->getQualitySortedHeader("Accept");
        }

        /**
         * @return array
         * Returns accepted languages sorted by priority
         * Accept-Language: fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5
         */
        public function getAcceptLanguage(){
            return $this->getQualitySortedHeader("Accept-Language");
        }

        /**
         * @param $header
         * @return array
         * Parses quality type headers and returns values sorted by quality
         */
        private function getQualitySortedHeader($header){
            $list = [];
            if( $this->headers->has($header) ){
                $parts = explode(',', $this->headers->get($header));
                $result = [];
                foreach( $parts as $part ){
                    $withQuality = explode(";", $part);
                    if( count($withQuality) === 2 ){
                        $result[trim($withQuality[0])] = floatval(trim(StringUtils::remove("q=", $withQuality[1])));
                    }
                    else{
                        $result[trim($withQuality[0])] = 1;
                    }
                }
                $list = $this->sortByPriority($result);
            }
            return $list;
        }

        /**
         * @param $arr array
         * @return array
         * Sorts headers by quality values
         * For more information: https://developer.mozilla.org/en-US/docs/Glossary/Quality_values
         */
        private function sortByPriority($arr){
            arsort($arr);
            return array_keys($arr);
        }

        /**
         * @return bool|int
         * Returns true if client is on mobile
         */
        public function isClientMobile(){
            if( $this->headers->has('User-Agent') ){
                $userAgent = $this->headers->get('User-Agent');
                return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\\.browser|up\\.link|webos|wos)/iu", $userAgent) !== 0;
            }
            else{
                return false;
            }
        }

        /**
         * @return bool|int
         * Detects whether client is bot
         */
        public function isClientBot(){
            if( $this->headers->has('User-Agent') ){
                $userAgent = $this->headers->get('User-Agent');
                return preg_match('/bot|crawl|slurp|spider|google|yahoo/i', $userAgent) !== 0;
            }
            else{
                // Normal browser connections send user agent header
                // If we don't have user agent header then client is probably a bot
                return true;
            }
        }

        /**
         * @return bool
         * returns true if request method is connect
         */
        public function isConnect(){
            return $this->getMethod() === "CONNECT";
        }

        /**
         * @return bool
         * returns true if request method is patch
         */
        public function isPatch(){
            return $this->getMethod() === "PATCH";
        }

        /**
         * @return bool
         * returns true if request method is trace
         */
        public function isTrace(){
            return $this->getMethod() === "TRACE";
        }

        /**
         * @return bool
         * returns true if request method is get
         */
        public function isGet(){
            return $this->getMethod() === "GET";
        }

        /**
         * @return bool
         * returns true if request method is post
         */
        public function isPost(){
            return $this->getMethod() === "POST";
        }

        /**
         * @return bool
         * returns true if request method is put
         */
        public function isPut(){
            return $this->getMethod() === "PUT";
        }

        /**
         * @return bool
         * returns true if request method is delete
         */
        public function isDelete(){
            return $this->getMethod() === "DELETE";
        }

        /**
         * @return bool
         * returns true if request method is options
         */
        public function isOptions(){
            return $this->getMethod() === "OPTIONS";
        }

        /**
         * @return bool
         * returns true if request method is head
         */
        public function isHead(){
            return $this->getMethod() === "HEAD";
        }

        /**
         * @return bool
         * Detects whether the request is an Ajax request
         */
        public function isXhr(){
            return ($this->headers->has("X-Requested-With") &&
                strtolower($this->headers->get("X-Requested-With")) === "xmlhttprequest");
        }

        /**
         * @return bool
         * Detects whether the connection is persistent or non-persistent
         */
        public function isConnectionPersistent(){
            if( $this->headers->has("Connection") ){
                return $this->headers->get("Connection") === "keep-alive";
            }
            else{
                return false;
            }
        }

        /**
         * @param $method
         *
         * @return boolean
         * @throws InvalidArgumentException
         *
         * Checks whether the request method is supported
         */
        private function checkMethod($method){
            if( is_string($method) ){
                if( in_array($method, $this->supportedMethods) ){
                    return true;
                }
                else{
                    throw new InvalidArgumentException("Unsupported request method!");
                }
            }
            else{
                throw new InvalidArgumentException("Method must be a string!");
            }
        }

        /**
         * @param $version string
         *
         * @throws InvalidArgumentException
         * @return mixed
         *
         * Checks whether the http protocol version is supported
         */
        private function checkProtocolVersion($version){
            if( is_string($version) ){
                if( in_array($version, $this->supportedProtocolVersions) ){
                    return true;
                }
                else{
                    throw new InvalidArgumentException("Unsupported http version!");
                }
            }
            else{
                throw new InvalidArgumentException("Http version must be a string!");
            }
        }

    }