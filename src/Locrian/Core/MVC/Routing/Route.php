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

    use Locrian\Cloneable;
    use Locrian\InvalidArgumentException;

    class Route implements Cloneable{

        /**
         * @var string
         * Optional route name. Names can be used directly with redirect.
         */
        private $name;


        /**
         * @var string
         * Route uri pattern. "/", "/about", "admin/dashboard" ...
         */
        private $routePattern;


        /**
         * @var string
         *
         * Request method. You can use the same route with different methods.
         * For example route pattern is "/login" and you want to use the same route pattern "/login"
         * got get request and the post request. This allows you to do that.
         */
        private $requestMethod;


        /**
         * @var string
         * Controller class that you want to attach to the route
         */
        private $controller;


        /**
         * @var string
         * Method of the controller class
         */
        private $method;


        /**
         * @var array
         * Optional middleware list.
         */
        private $middlewareList;


        /**
         * @var array
         * Query and path parameters
         */
        private $pathParameters;


        /**
         * Route constructor.
         *
         * @param $routePattern
         * @param $requestMethod
         */
        public function __construct($requestMethod, $routePattern){
            $this->setRequestMethod($requestMethod)
                 ->setRoutePattern($routePattern)
                 ->setName("")
                 ->setController("")
                 ->setMethod("")
                 ->setMiddlewareList([])
                 ->setPathParameters([]);
        }


        /**
         * @return bool
         * Returns whether the route has middleware
         */
        public function hasMiddleware(){
            return count($this->middlewareList) > 0;
        }


        /**
         * @return string route name
         */
        public function getName(){
            return $this->name;
        }


        /**
         * @param mixed $name
         *
         * @return $this
         * @throws InvalidArgumentException
         */
        public function setName($name){
            if( is_string($name) ){
                $this->name = $name;
                return $this;
            }
            else{
                throw new InvalidArgumentException("Name must be a string!");
            }
        }


        /**
         * @return mixed
         */
        public function getRequestMethod(){
            return $this->requestMethod;
        }


        /**
         * @param mixed $requestMethod
         *
         * @return $this
         * @throws InvalidArgumentException
         */
        private function setRequestMethod($requestMethod){
            if( is_string($requestMethod) ){
                $this->requestMethod = $requestMethod;
                return $this;
            }
            else{
                throw new InvalidArgumentException("Request method must be a string!");
            }
        }


        /**
         * @return mixed
         */
        public function getController(){
            return $this->controller;
        }


        /**
         * @param mixed $controller
         *
         * @return $this
         * @throws InvalidArgumentException
         */
        public function setController($controller){
            if( is_string($controller) ){
                $this->controller = $controller;
                return $this;
            }
            else{
                throw new InvalidArgumentException("Controller name must be a string!");
            }
        }


        /**
         * @return mixed
         */
        public function getMethod(){
            return $this->method;
        }


        /**
         * @param mixed $method
         *
         * @return $this
         * @throws InvalidArgumentException
         */
        public function setMethod($method){
            if( is_string($method) ){
                $this->method = $method;
                return $this;
            }
            else{
                throw new InvalidArgumentException("Method name must be a string!");
            }
        }


        /**
         * @return string
         */
        public function getRoutePattern(){
            return $this->routePattern;
        }


        /**
         * @param string $routePattern
         *
         * @return $this
         * @throws InvalidArgumentException
         */
        private function setRoutePattern($routePattern){
            if( is_string($routePattern) ){
                $this->routePattern = $routePattern;
                return $this;
            }
            else{
                throw new InvalidArgumentException("Route pattern must be a string!");
            }
        }


        /**
         * @return mixed
         * @throws InvalidArgumentException
         */
        public function getMiddlewareList(){
            return $this->middlewareList;
        }


        /**
         * @param mixed $middleware
         *
         * @return $this
         * @throws InvalidArgumentException
         */
        public function setMiddlewareList(array $middleware){
            $this->middlewareList = $middleware;
            return $this;
        }


        /**
         * @return array
         */
        public function getPathParameters(){
            return $this->pathParameters;
        }


        /**
         * @param array $pathParameters
         * @return $this
         */
        public function setPathParameters(array $pathParameters){
            $this->pathParameters = $pathParameters;
            return $this;
        }


        /**
         * @return Route
         * Clones the object
         */
        public function makeClone(){
            $clone = clone $this;
            return $clone;
        }

    }