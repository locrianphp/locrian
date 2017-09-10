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

    namespace Locrian\Core\Http\Builder;

    use Locrian\Collections\HashMap;

    abstract class ResponseBuilder{

        /**
         * @var string
         */
        private $content;

        /**
         * @var \Locrian\Collections\HashMap
         */
        private $headers;

        /**
         * ResponseBuilder constructor.
         */
        public function __construct(){
            $this->setHeaders(new HashMap());
            $this->setContent("");
        }

        /**
         * @param $content
         */
        public function setContent($content){
            $this->content = $content;
        }

        /**
         * @return string
         */
        public function getContent(){
            return $this->content;
        }

        /**
         * @param HashMap $headers
         */
        public function setHeaders(HashMap $headers){
            $this->headers = $headers;
        }

        /**
         * @param $key string
         * @param $value mixed
         */
        public function addHeader($key, $value){
            $this->headers->set($key, $value);
        }

        /**
         * @param $key
         */
        public function removeHeader($key){
            $this->headers->remove($key);
        }

        /**
         * @return HashMap
         */
        public function getHeaders(){
            return $this->headers;
        }

    }