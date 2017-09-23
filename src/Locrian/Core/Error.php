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

    namespace Locrian\Core;

    class Error{

        /**
         * If error is a php error
         */
        const ERROR_TYPE = "error_type";

        /**
         * If error is an exception
         */
        const EXCEPTION_TYPE = "exception_type";

        /**
         * @var int error line
         */
        private $line;

        /**
         * @var string error file path
         */
        private $file;

        /**
         * @var string ERROR_TYPE|EXCEPTION_TYPE
         */
        private $type;

        /**
         * @var string class name if error is an exception
         */
        private $className;

        /**
         * @var string Error message
         */
        private $message;

        /**
         * @var array stack trace
         */
        private $trace;

        /**
         * Error constructor.
         * @param $type string
         */
        public function __construct($type){
            $this->type = $type;
        }

        /**
         * @return int
         */
        public function getLine(){
            return $this->line;
        }

        /**
         * @param int $line
         */
        public function setLine($line){
            $this->line = $line;
        }

        /**
         * @return string
         */
        public function getFile(){
            return $this->file;
        }

        /**
         * @param string $file
         */
        public function setFile($file){
            $this->file = $file;
        }

        /**
         * @return string
         */
        public function getType(){
            return $this->type;
        }

        /**
         * @return string
         */
        public function getClassName(){
            return $this->className;
        }

        /**
         * @param string $className
         */
        public function setClassName($className){
            $this->className = $className;
        }

        /**
         * @return string
         */
        public function getMessage(){
            return $this->message;
        }

        /**
         * @param string $message
         */
        public function setMessage($message){
            $this->message = $message;
        }

        /**
         * @return array
         */
        public function getTrace(){
            return $this->trace;
        }

        /**
         * @param array $trace
         */
        public function setTrace($trace){
            $this->trace = $trace;
        }

    }