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

    use Locrian\IO\File;

    class UploadedFile{

        /**
         * @var string
         * Real name of the uploaded file
         */
        private $name;

        /**
         * @var string
         * Mime type
         */
        private $type;

        /**
         * @var string
         * Temporary name with location
         */
        private $tmpName;

        /**
         * @var int
         * Upload error
         */
        private $error;

        /**
         * @var int
         * Size of the file
         */
        private $size;

        /**
         * @var array error message
         */
        private $errorMessages;

        /**
         * UploadedFile constructor.
         */
        public function __construct(){
            $this->errorMessages = [];
        }

        /**
         * @return bool
         */
        public function isUploaded(){
            return is_uploaded_file($this->getTmpName());
        }

        /**
         * @param File $destination destination path with name
         * @return bool
         */
        public function move(File $destination){
            $file = new File($this->tmpName);
            return $file->move($destination);
        }

        /**
         * @return mixed
         */
        public function getName(){
            return $this->name;
        }

        /**
         * @param mixed $name
         */
        public function setName($name){
            $this->name = $name;
        }

        /**
         * @return mixed
         */
        public function getType(){
            return $this->type;
        }

        /**
         * @param mixed $type
         */
        public function setType($type){
            $this->type = $type;
        }

        /**
         * @return mixed
         */
        public function getTmpName(){
            return $this->tmpName;
        }

        /**
         * @param mixed $tmpName
         */
        public function setTmpName($tmpName){
            $this->tmpName = $tmpName;
        }

        /**
         * @return mixed
         */
        public function getError(){
            return $this->error;
        }

        /**
         * @param mixed $error
         */
        public function setError($error){
            $this->error = $error;
        }

        /**
         * @return mixed
         */
        public function getSize(){
            return $this->size;
        }

        /**
         * @param mixed $size
         */
        public function setSize($size){
            $this->size = $size;
        }

        /**
         * @return array
         */
        public function getErrorMessages(){
            return $this->errorMessages;
        }

        /**
         * @param array $errorMessages
         */
        public function setErrorMessages($errorMessages){
            $this->errorMessages = $errorMessages;
        }

        /**
         * @return string
         */
        function __toString(){
            return "UploadedFile{"
                . "\n\tName: " . $this->getName()
                . "\n\tType: " . $this->getType()
                . "\n\tTmpName: " . $this->getTmpName()
                . "\n\tSize: " . $this->getSize()
                . "\n\tError: " . $this->getError()
                . "\n\tError Message: " . implode(" - ", $this->getErrorMessages())
                . "\n}";
        }

    }