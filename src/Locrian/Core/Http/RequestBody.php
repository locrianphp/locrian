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

    use Locrian\Core\Http\Parsers\BodyParser;
    use Locrian\IO\BufferedInputStream;

    class RequestBody{

        /**
         * @var \Locrian\IO\BufferedInputStream
         */
        private $stream;

        /**
         * @var string
         * Raw body
         */
        private $body;

        /**
         * @var object|array|null
         */
        private $parsedBody;

        /**
         * @var \Locrian\Core\Http\Parsers\BodyParser
         */
        private $bodyParser;

        /**
         * RequestBody constructor.
         *
         * @param \Locrian\IO\BufferedInputStream $stream
         * @param \Locrian\Core\Http\Parsers\BodyParser $parser
         */
        public function __construct(BufferedInputStream $stream, BodyParser $parser){
            $this->stream = $stream;
            $this->bodyParser = $parser;
            $this->parsedBody = null;
            $this->body = null;
        }

        /**
         * @return array|null|object
         * Returns parsed body
         * If body parser is null (it means that either Content-Type header is missing or
         * we don't have any appropriate body parser for the current Content-Type) this method will return null
         */
        public function getParsedBody(){
            if( $this->bodyParser !== null ){
                if( $this->parsedBody === null ){
                    $this->parsedBody = $this->bodyParser->parse($this->getRawBody());
                }
                return $this->parsedBody;
            }
            else{
                return null;
            }
        }

        /**
         * @param $name string
         * @param $default mixed
         * @return mixed
         * Returns a parameter's value from parsed body
         */
        public function getParam($name, $default = null){
            $param = $default;
            $parsedBody = $this->getParsedBody();
            if( $parsedBody !== null ){
                if( is_array($parsedBody) && isset($parsedBody[$name]) ){
                    $param = $parsedBody[$name];
                }
                else if( is_object($parsedBody) && property_exists($parsedBody, $name) ){
                    $param = $parsedBody->$name;
                }
            }
            return $param;
        }

        /**
         * @return null|string
         * Reads and returns raw request body
         */
        public function getRawBody(){
            if( $this->body === null ){
                $this->stream->rewind();
                $data = "";
                while( ($chunk = $this->stream->read()) !== null ){
                    $data .= $chunk;
                }
                $this->body = $data;
            }
            return $this->body;
        }

        /**
         * @return \Locrian\Core\Http\Parsers\BodyParser
         */
        public function getBodyParser(){
            return $this->bodyParser;
        }

        /**
         * @return \Locrian\IO\BufferedInputStream
         */
        public function getInputStream(){
            return $this->stream;
        }

    }