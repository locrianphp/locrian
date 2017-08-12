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

    namespace Locrian\Core\MVC\Routing;

    use Closure;
    use Locrian\Collections\ArrayList;
    use Locrian\Collections\HashMap;
    use Locrian\InvalidArgumentException;

    class Router{

        /**
         * @var \Locrian\Collections\HashMap list of get routes
         */
        private $get;


        /**
         * @var \Locrian\Collections\HashMap list of post routes
         */
        private $post;


        /**
         * @var \Locrian\Collections\HashMap list of put routes
         */
        private $put;


        /**
         * @var \Locrian\Collections\HashMap list of delete routes
         */
        private $delete;


        /**
         * @var \Locrian\Collections\HashMap list of options routes
         */
        private $options;


        /**
         * @var \Locrian\Collections\HashMap list of head routes
         */
        private $head;


        /**
         * @var array Request types Post, Get...
         */
        const VALID_REQUEST_METHODS = [ "GET", "POST", "PUT", "DELETE", "OPTIONS", "HEAD" ];


        /**
         * @var Router Singleton pattern
         */
        private static $instance = null;


        /**
         * Router constructor.
         */
        private function __construct(){
            $this->get = null;
            $this->post = null;
            $this->put = null;
            $this->delete = null;
            $this->options = null;
            $this->head = null;
        }


        /**
         * Removes all registered routes
         */
        public function clearRoutes(){
            $this->get = null;
            $this->post = null;
            $this->put = null;
            $this->delete = null;
            $this->options = null;
            $this->head = null;
        }


        /**
         * @param $type
         * @param $routePattern
         *
         * @return bool
         * @throws InvalidArgumentException
         *
         * Checks the routePattern is valid in a specific request method
         */
        private function isRouteValid($type, $routePattern){
            if( in_array(strtoupper($type), self::VALID_REQUEST_METHODS) ){
                $type = strtolower($type);
                if( $this->$type == null ){
                    $this->$type = new HashMap();   // Lazy initialize
                    return true;
                }
                else{
                    if( $this->$type->has($routePattern) ){
                        return false;
                    }
                    else{
                        return true;
                    }
                }

            }
            else{
                throw new InvalidArgumentException("Invalid request method");
            }
        }


        /**
         * @return Router Singleton pattern
         */
        public static function getInstance(){
            if( self::$instance == null ){
                self::$instance = new Router();
            }
            return self::$instance;
        }


        /**
         * @param Route $route
         * Adds route to the list
         */
        private function addRoute(Route $route){
            $method = $route->getRequestMethod();
            $method = strtolower($method);
            $this->$method->add($route->getRoutePattern(), $route);
        }


        /**
         * @param $requestMethod
         * @param $path
         *
         * @return bool
         * @throws InvalidArgumentException
         * @throws RouterException
         *
         * Finds and returns the route which belongs to the request method and request pattern.
         * See the following example
         *
         * Router::get("/{username}/home", function(Route $route){
         *      $route->setController(\App\Controller\HomeController::class)
         *            ->setMethod("Index")
         *            ->setName("home.index")
         *            ->setMiddleware([ \App\Middleware\SomeMiddleware::class, \App\Middleware\SomeOtherMiddleware::class ]);
         * });
         *
         * $router = Router::getInstance();
         * $router->find("get","/social13/home");
         *
         * This will return the route that you defined above. If the route did not exist the this
         * method will return null.
         */
        public function find($requestMethod, $path){
            if( is_string($requestMethod) && is_string($path) ){
                if( !in_array(strtoupper($requestMethod), self::VALID_REQUEST_METHODS) ){
                    throw new RouterException("Unsupported request method");
                }
                $type = strtolower($requestMethod);
                if( $this->$type == null ){
                    return null;
                }
                $patterns = $this->$type->getKeys();
                foreach( $patterns as $pattern ){
                    // Translate the pattern to a regex string
                    $regex = preg_replace("@{[^{}/]+}@", "([^/]+)", $pattern);
                    // Check whether the route begins with our pattern
                    if( preg_match_all("%^" . $regex . "/?$%", $path, $parameterValuesResult) ){
                        $route = $this->$type->get($pattern);
                        // Fill parameters
                        preg_match_all("@{([^{/]*)}@", $pattern, $parameterNamesResult);
                        // Check whether the path parameters exist
                        if( count($parameterNamesResult) > 0 && count($parameterNamesResult[0]) > 0 ){
                            $parameterNames = $parameterNamesResult[1];
                            $len = count($parameterValuesResult);
                            $parameters = [];
                            for( $i = 1; $i < $len; $i++ ){ // [0] is all string, following indexes are values
                                $parameters[$parameterNames[$i - 1]] = $parameterValuesResult[$i][0];
                            }
                            $route->setPathParameters($parameters);
                        }
                        return $route;
                    }
                }
                // If we are here then it means we could'nt find the route
                // So we return null
                return null;
            }
            else{
                throw new InvalidArgumentException("Request type and route pattern must be string!");
            }
        }


        /**
         * @param $name string name of the route
         *
         * @return Route | bool
         *
         * Searches all the routes in a specific request method and returns it if it is founded. If not returns false.
         */
        public function searchByName($name){
            foreach( self::VALID_REQUEST_METHODS as $value ){
                $method = strtolower($value);
                if( $this->$method == null ){
                    continue;
                }
                else{
                    $keys = $this->$method->getKeys();
                    foreach( $keys as $k ){
                        $route = $this->$method->get($k);
                        if( $name === $route->getName() ){
                            return $route;
                        }
                    }
                }
            }
            return false;
        }


        /**
         * @param $requestMethod string
         * @return \Locrian\Collections\ArrayList of Route
         * @throws \Locrian\InvalidArgumentException
         */
        public function getRoutes($requestMethod){
            $method = strtolower($requestMethod);
            if( in_array(strtoupper($requestMethod), self::VALID_REQUEST_METHODS) ){
                $list = new ArrayList();
                $this->$method->each(function($key, Route $route) use($list){
                    $list->add($route);
                });
                return $list;
            }
            else{
                throw new InvalidArgumentException("Unsupported request method");
            }
        }


        /**
         * @param $requestMethod
         * @param $routePattern
         * @param Closure $callback
         *
         * @throws InvalidArgumentException
         * @throws RouterException
         *
         * Common route adder method
         */
        private static function addRouteStatic($requestMethod, $routePattern, Closure $callback){
            if( is_string($routePattern) ){
                $router = Router::getInstance();
                // If route already exists throw an exception
                if( $router->isRouteValid($requestMethod, $routePattern) ){
                    $route = new Route($requestMethod, $routePattern);
                    $callback($route);
                    $router->addRoute($route);
                }
                else{
                    throw new RouterException("Route names must be unique");
                }
            }
            else{
                throw new InvalidArgumentException("Route pattern must be a string");
            }
        }


        /**
         * @param $routePattern
         * @param Closure $callback
         *
         * @throws InvalidArgumentException
         * @throws RouterException
         *
         * Route for get request
         */
        public static function get($routePattern, Closure $callback){
            self::addRouteStatic("GET", $routePattern, $callback);
        }


        /**
         * @param $routePattern
         * @param Closure $callback
         *
         * @throws InvalidArgumentException
         * @throws RouterException
         *
         * Route for post request
         */
        public static function post($routePattern, Closure $callback){
            self::addRouteStatic("POST", $routePattern, $callback);
        }


        /**
         * @param $routePattern
         * @param Closure $callback
         *
         * @throws InvalidArgumentException
         * @throws RouterException
         *
         * Route for put request
         */
        public static function put($routePattern, Closure $callback){
            self::addRouteStatic("PUT", $routePattern, $callback);
        }


        /**
         * @param $routePattern
         * @param Closure $callback
         *
         * @throws InvalidArgumentException
         * @throws RouterException
         *
         * Route for delete request
         */
        public static function delete($routePattern, Closure $callback){
            self::addRouteStatic("DELETE", $routePattern, $callback);
        }


        /**
         * @param $routePattern
         * @param Closure $callback
         *
         * @throws InvalidArgumentException
         * @throws RouterException
         *
         * Route for options request
         */
        public static function options($routePattern, Closure $callback){
            self::addRouteStatic("OPTIONS", $routePattern, $callback);
        }


        /**
         * @param $routePattern
         * @param Closure $callback
         *
         * @throws InvalidArgumentException
         * @throws RouterException
         *
         * Route for head request
         */
        public static function head($routePattern, Closure $callback){
            self::addRouteStatic("HEAD", $routePattern, $callback);
        }


        /**
         * @param $routePattern
         * @param Closure $callback
         *
         * Matches all the http methods
         */
        public static function any($routePattern, Closure $callback){
            $methods = ["GET", "POST", "PUT", "DELETE", "OPTIONS", "HEAD"];
            foreach( $methods as $method ){
                self::addRouteStatic($method, $routePattern, $callback);
            }
        }

    }