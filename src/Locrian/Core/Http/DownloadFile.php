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

    class DownloadFile extends File{

        /**
         * @var string
         */
        private $downloadName;

        /**
         * DownloadFile constructor.
         *
         * @param $path string real file path
         * @param string $downloadName name that will be send as real name
         */
        public function __construct($path, $downloadName){
            $this->downloadName = $downloadName;
            parent::__construct($path, null);
        }

        /**
         * @return mixed
         */
        public function getDownloadName(){
            return $this->downloadName;
        }

        /**
         * @param mixed $downloadName
         */
        public function setDownloadName($downloadName){
            $this->downloadName = $downloadName;
        }

    }