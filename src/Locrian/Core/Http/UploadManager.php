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

    use Closure;
    use Locrian\IO\File;

    class UploadManager{

        // Upload error constants and their values
        const ERR_INI_SIZE = 1;
        const ERR_FORM_SIZE = 2;
        const ERR_PARTIAL = 3;
        const ERR_NO_FILE = 4;
        const ERR_NO_TMP_DIR = 6;
        const ERR_CANT_WRITE = 7;
        const ERR_EXTENSION = 8;
        // Upload error constants and their values

        // Validation error keys
        const MIME_ERROR = "mime_error";
        const MAX_SIZE_ERROR = "max_size_error";
        const MIN_SIZE_ERROR = "min_size_error";
        // Validation error keys

        /**
         * @var array
         * Holds UploadedFile objects which cannot be moved
         */
        private $moveErrors;

        /**
         * @var array
         * Holds UploadedFile objects which are failed in validation
         */
        private $validationErrors;

        /**
         * @var array
         * Holds validation error messages
         */
        private $validationErrorMessages;

        /**
         * @var array
         * Allowed mime types
         */
        private $allowedMimeTypes;

        /**
         * @var array
         * [0] => minimum size
         * [1] => maximum size
         */
        private $validSizes;

        /**
         * @var \Locrian\Collections\ArrayList
         */
        private $files;

        /**
         * @param $files \Locrian\Collections\ArrayList list of files
         *
         * UploadManager constructor.
         *
         * Example usage:
         *
         * $files->setAllowedMimeTypes(["image/*"])
         *       ->setAllowedMinFileSize(0)
         *       ->setAllowedMaxFileSize(5000000) // 5mb
         *       ->validate();
         * if( !$files->hasValidationErrors() ){
         *      $files->moveAll("assets/files/");
         * }
         */
        public function __construct($files){
            $this->files = $files;
            $this->moveErrors = [];
            $this->validationErrors = [];
            $this->allowedMimeTypes = [];
            $this->validSizes = [];
            $this->setAllowedMimeTypes([ "(.*)" ]);
            $this->setAllowedMinFileSize(-1);
            $this->setAllowedMaxFileSize(-1);
        }

        /**
         * @return bool
         * Returns true when all files are uploaded to the temp location
         */
        public function isAllUploaded(){
            $err = false;
            $this->files->each(function(UploadedFile $file) use (&$err){
                if( $file->getError() > 0 ){
                    $err = true;
                }
            });
            return !$err;
        }

        /**
         * @param string $destination destination directory
         */
        public function moveAll($destination){
            $this->files->each(function($i, UploadedFile $file) use($destination){
                if( $file->getError() === 0 && count($file->getErrorMessages()) === 0 ){
                    $d = new File($destination . $file->getName());
                    if( !$file->move($d) ){
                        $this->moveErrors[] = $file;
                    }
                }
            });
        }

        /**
         * @param string $destination
         * @param Closure $callback
         * Sends all file names to the callback and sets the returned name as new name
         */
        public function moveAllWithName($destination, Closure $callback){
            $this->files->each(function($i, UploadedFile $file) use($destination, $callback){
                if( $file->getError() === 0 && count($file->getErrorMessages()) === 0 ){
                    $file->setName($callback($file->getName()));
                }
            });
            $this->moveAll($destination);
        }

        /**
         * @param array $mimeTypes allowed mime types
         * @param string $errorMessage validation error message
         * Sets allowed mime types
         * @return $this for chaining
         */
        public function setAllowedMimeTypes(array $mimeTypes, $errorMessage = "Invalid mime type"){
            $this->allowedMimeTypes = $mimeTypes;
            $this->validationErrorMessages[self::MIME_ERROR] = $errorMessage;
            return $this;
        }

        /**
         * @param int $fileSize maximum file size
         * @param string $errorMessage validation error message
         * @return $this for chaining
         */
        public function setAllowedMaxFileSize($fileSize, $errorMessage = "Size is bigger than allowed maximum size ({0})"){
            $this->validSizes[1] = $fileSize;
            $this->validationErrorMessages[self::MAX_SIZE_ERROR] = str_replace("{0}", $fileSize, $errorMessage);
            return $this;
        }

        /**
         * @param int $fileSize minimum file size
         * @param string $errorMessage validation error message
         * @return $this for chaining
         */
        public function setAllowedMinFileSize($fileSize, $errorMessage = "Size is smaller than allowed minimum size ({0})"){
            $this->validSizes[0] = $fileSize;
            $this->validationErrorMessages[self::MIN_SIZE_ERROR] = str_replace("{0}", $fileSize, $errorMessage);
            return $this;
        }

        /**
         * Validates files
         */
        public function validate(){
            $this->files->each(function($i, UploadedFile $file){
                $errorMessages = [];
                // Mime
                $hasMimeError = true;
                foreach( $this->allowedMimeTypes as $mimeType ){
                    if( preg_match(("%" . $mimeType . "%"), $file->getType()) ){
                        $hasMimeError = false;
                    }
                }
                if( $hasMimeError ){
                    $errorMessages[self::MIME_ERROR] = $this->validationErrorMessages[self::MIME_ERROR];
                }
                // Min size
                if( $this->validSizes[0] >= 0 && ($file->getSize() < $this->validSizes[0]) ){
                    $errorMessages[self::MIN_SIZE_ERROR] = $this->validationErrorMessages[self::MIN_SIZE_ERROR];
                }
                // Max size
                if( $this->validSizes[1] >= 0 && ($file->getSize() > $this->validSizes[1]) ){
                    $errorMessages[self::MAX_SIZE_ERROR] = $this->validationErrorMessages[self::MAX_SIZE_ERROR];
                }
                if( count($errorMessages) > 0 ){
                    $file->setErrorMessages($errorMessages);
                    $this->validationErrors[] = $file;
                }
            });
        }

        /**
         * @return bool
         * Returns true if ad least one file failed in validation
         */
        public function hasValidationErrors(){
            return count($this->validationErrors) > 0;
        }

        /**
         * @return array
         * Returns all the failed UploadedFile objects
         */
        public function getFailedValidationUploads(){
            return $this->validationErrors;
        }

        /**
         * @return array
         * Returns upload error codes which are represented by constants
         */
        public function getUploadErrors(){
            $errors = [];
            $this->files->each(function(UploadedFile $file) use(&$errors){
                if( $file->getError() > 0 ){
                    $errors[] = $file->getError();
                }
            });
            return $errors;
        }

        /**
         * @return bool
         * Returns true if at least one file could not be moved
         */
        public function hasMoveErrors(){
            return count($this->moveErrors) > 0;
        }

        /**
         * @return array
         * Returns all the UploadedFile objects which could not be moved
         */
        public function getMoveErrors(){
            return $this->moveErrors;
        }

        /**
         * @return \Locrian\Collections\ArrayList
         */
        public function getFiles(){
            return $this->files;
        }

    }