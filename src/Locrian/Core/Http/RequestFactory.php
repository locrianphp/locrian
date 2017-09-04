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
    use Locrian\Core\MVC\Routing\Route;
    use Locrian\Http\Cookie;
    use Locrian\Http\Session\Session;
    use Locrian\Http\Uri;
    use Locrian\Util\StringUtils;

    class RequestFactory{

        public static function createRequest(Session $session, Cookie $cookie, Route $route){
            $env = self::createEnvironment();
            $headers = self::createHeaders($env);
            $uri = self::createUri($env);
            $files = self::createFiles($_FILES);
            $params = self::getParameters($route, $uri);

            $env->set("SERVER_NAME", $uri->getHost());
            $env->set("HTTP_HOST", $uri->getHost());
            $headers->set("Host", $uri->getHost());

            $request = new Request($env, $headers, $uri, $session, $cookie, $files, $params);

            return $request;
        }

        private static function getParameters(Route $route, Uri $uri){
            $params = new HashMap();
            $combined = new HashMap();
            $queryParams = new HashMap();
            $pathParams = new HashMap();
            foreach( $route->getPathParameters() as $name => $value ){
                $combined->set($name, $value);
                $pathParams->set($name, $value);
            }
            $queryParts = explode("&", $uri->getQuery());
            foreach( $queryParts as $param ){
                $param = explode("=", $param);
                $combined->set($param[0], $param[1]);
                $queryParams->set($param[0], $param[1]);
            }
            $params->set("query", $queryParams);
            $params->set("path", $pathParams);
            $params->set("combined", $combined);
            return $params;
        }

        private static function createEnvironment(){
            if( !isset($_SERVER) || !is_array($_SERVER) ){
                $_SERVER = [];
            }
            $envVariables = array_merge([
                'SERVER_PROTOCOL' => 'HTTP/1.1',
                'REQUEST_METHOD' => 'GET',
                'SCRIPT_NAME' => '',
                'REQUEST_URI' => '',
                'QUERY_STRING' => '',
                'SERVER_NAME' => 'localhost',
                'SERVER_PORT' => 80,
                'HTTP_HOST' => 'localhost',
                'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8',
                'HTTP_USER_AGENT' => '',
                'REMOTE_ADDR' => '127.0.0.1'
            ], $_SERVER);

            $env = new HashMap();
            foreach( $envVariables as $key => $value ){
                $env->add(strtoupper($key), $value);
            }
            return $env;
        }

        private static function createHeaders(HashMap $env){
            $headers = new HashMap();
            if( function_exists("getallheaders") ){
                foreach( getallheaders() as $header => $value ){
                    $headers->set($header, $value);
                }
            }
            else{
                $additionalHeaderFields = [ "CONTENT_TYPE", "CONTENT_LENGTH", "PHP_AUTH_USER", "PHP_AUTH_PW", "PHP_AUTH_DIGEST", "AUTH_TYPE" ];
                $env->each(function($key, $value) use($headers, $additionalHeaderFields){
                    if( StringUtils::startsWith("HTTP_", $key) || in_array($key, $additionalHeaderFields) ){
                        // $headers->set($key, $value);
                        // Add with real header name
                        $headers->set(str_replace(" ", "-", ucwords(strtolower(str_replace(["HTTP_", "_"], ["", " "], $key)))), $value);
                    }
                });
            }
            return $headers;
        }

        private static function createUri(HashMap $env){
            $scheme = (($env->get("HTTPS") !== false && $env->get('HTTPS') !== "off") ? "https" : "http");
            $username = $env->get('PHP_AUTH_USER') ?: "";
            $password = $env->get('PHP_AUTH_PW') ?: "";

            if( $env->has('HTTP_HOST') ){
                $host = $env->get('HTTP_HOST');
            }
            else{
                $host = $env->get('SERVER_NAME');
            }

            if( $scheme == "https" ){
                $port = (int) ($env->get("SERVER_PORT") ?: 443);
            }
            else{
                $port = (int) ($env->get("SERVER_PORT") ?: 80);
            }
            $urlParts = parse_url($_SERVER['REQUEST_URI']);

            $path = isset($urlParts['path']) ? rtrim($urlParts['path'], '/') : "";
            $query = isset($urlParts['query']) ? $urlParts['query'] : "";

            $uri = new Uri();
            $uri->setScheme($scheme)
                ->setUser($username)
                ->setPassword($password)
                ->setHost($host)
                ->setPort($port)
                ->setPath($path)
                ->setQuery($query);

            return $uri;
        }

        private static function createFiles($fileList){
            $files = new ArrayList();
            if( isset($fileList) && is_array($fileList) ){
                foreach( $fileList as $inputName => $uploadedFiles ){
                    // Single file upload
                    if( is_int($uploadedFiles["error"]) ){
                        $files->add(self::createFile($uploadedFiles['size'], $uploadedFiles["name"],
                            $uploadedFiles["error"], $uploadedFiles["type"], $uploadedFiles["tmp_name"]));
                    }
                    // Multiple file upload
                    else if( is_array($uploadedFiles["error"]) ){
                        $len = count($uploadedFiles["name"]);
                        for( $i = 0; $i < $len; $i++ ){
                            $files->add(self::createFile($uploadedFiles["size"][$i], $uploadedFiles["name"][$i],
                                $uploadedFiles["error"][$i], $uploadedFiles["type"][$i], $uploadedFiles["tmp_name"][$i]));
                        }
                    }
                }
            }
            return $files;
        }

        private static function createFile($size, $name, $error, $type, $tmpName){
            $file = new UploadedFile();
            $file->setSize($size);
            $file->setName($name);
            $file->setError($error);
            $file->setType($type);
            $file->setTmpName($tmpName);
            return $file;
        }

    }