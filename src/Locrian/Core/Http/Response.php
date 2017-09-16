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

    use Locrian\Collections\HashMap;
    use Locrian\Core\Http\Builder\ResponseBuilder;
    use Locrian\InvalidArgumentException;
    use Locrian\IO\BufferedInputStream;
    use Locrian\IO\BufferedOutputStream;
    use Locrian\IO\File;
    use Locrian\IO\IOException;

    class Response{

        /**
         * @var \Locrian\Core\Http\StatusCodes
         */
        private $statusCodeList;

        /**
         * @var integer
         */
        private $statusCode;

        /**
         * @var \Locrian\Collections\HashMap
         * Response headers
         */
        private  $headers;

        /**
         * @var \Locrian\IO\BufferedOutputStream
         * Output stream
         */
        private $outputStream;

        /**
         * @var integer
         * Protocol version
         */
        private $protocolVersion;

        /**
         * @var boolean
         */
        private $outputBuffer;

        /**
         * Response constructor.
         *
         * @param $bufferSize integer
         * @param $protocolVersion string
         * @param $statusCode integer
         * @param \Locrian\IO\BufferedOutputStream|null $outputStream
         */
        public function __construct($bufferSize, $protocolVersion, BufferedOutputStream $outputStream = null, $statusCode = 200){
            if( $bufferSize <= 0 ){
                $bufferSize = 1; // Minimum buffer size
                $this->outputBuffer = true;
            }
            else{
                $this->outputBuffer = false;
            }
            if( $outputStream === null ){
                $this->outputStream = new BufferedOutputStream(new File("php://output"), $bufferSize);
            }
            else{
                $this->outputStream = $outputStream;
            }
            $this->protocolVersion = $protocolVersion;
            $this->statusCode = $statusCode;
            $this->headers = new HashMap();
            $this->statusCodeList = new StatusCodes();
        }

        /**
         * @param $chunk mixed
         * @param bool $flush
         * @throws \Locrian\InvalidArgumentException
         *
         * Writes chunk to stream
         */
        public function write($chunk, $flush = false){
            if( is_string($chunk) || method_exists($chunk, "__toString") ){
                if( !$this->headersSent() ){
                    $this->sendHeaders();
                }
                $this->outputStream->write($chunk);
                if( $flush === true || $this->outputBuffer === false ){
                    $this->outputStream->flush();
                }
            }
            else{
                throw new InvalidArgumentException("Content must be either a string or stringable!");
            }
        }

        /**
         * @param $name string
         * @param $value mixed
         * @throws \Locrian\InvalidArgumentException
         *
         * Writes a new header line or overrides existing one
         */
        public function writeHead($name, $value){
            if( (is_string($value) || is_bool($value) || is_numeric($value)) && is_string($name) ){
                $this->headers->set($name, $value);
            }
            else{
                throw new InvalidArgumentException("Header must be a string!");
            }
        }

        /**
         * Sends all headers
         */
        public function sendHeaders(){
            if( $this->headersSent() === false ){
                header(sprintf(
                    'HTTP/%s %s %s',
                    $this->getProtocolVersion(),
                    $this->getStatusCode(),
                    $this->statusCodeList->getMessage($this->getStatusCode())
                ));
                $this->headers->each(function($name, $value){
                    header(sprintf("%s: %s", $name, $value));
                });
            }
            else{
                throw new HttpException("Headers are already sent");
            }
        }

        /**
         * @return bool
         */
        public function headersSent(){
            return headers_sent();
        }

        /**
         * @return string
         */
        public function getProtocolVersion(){
            return $this->protocolVersion;
        }

        /**
         * @param $version string
         */
        public function setProtocolVersion($version){
            $this->protocolVersion = $version;
        }

        /**
         * @return \Locrian\Collections\HashMap
         */
        public function getHeaders(){
            return $this->headers;
        }

        /**
         * @param \Locrian\Collections\HashMap $headers
         */
        public function setHeaders(HashMap $headers){
            $this->headers = $headers;
        }

        /**
         * @return int
         */
        public function getStatusCode(){
            return $this->statusCode;
        }

        /**
         * @param $statusCode integer
         * @throws \Locrian\InvalidArgumentException
         */
        public function setStatusCode($statusCode){
            if( is_int($statusCode) ){
                if( $this->statusCodeList->has($statusCode) ){
                    $this->statusCode = $statusCode;
                }
                else{
                    throw new InvalidArgumentException("Invalid status code!");
                }
            }
            else{
                throw new InvalidArgumentException("Status code must be an integer!");
            }
        }

        /**
         * @param $contentType string
         * Sets content type header
         */
        public function setContentType($contentType){
            $this->writeHead("Content-Type", $contentType);
        }

        /**
         * @param $data mixed
         * Send 200 response
         */
        public function ok($data){
            $this->withStatus($data, 200);
        }

        /**
         * @param string $data
         * Send 404 response
         */
        public function notFound($data = ""){
            $this->withStatus($data, 404);
        }

        /**
         * @param string $data
         * Send 403 response
         */
        public function forbidden($data = ""){
            $this->withStatus($data, 403);
        }

        /**
         * @param string $data
         * Send 500 response
         */
        public function internalServerError($data = ""){
            $this->withStatus($data, 500);
        }

        /**
         * @param string $data
         * Send 400 response
         */
        public function badRequest($data = ""){
            $this->withStatus($data, 400);
        }

        /**
         * @param $location
         * @param integer|null $timeout
         * Send 302 response
         */
        public function redirect($location, $timeout = null){
            $this->writeHead("Cache-Control", "no-store, no-cache, must-revalidate");
            $this->writeHead("Connection", "Close");
            if( !preg_match("#^http(s)?://#", $location) ){
                $location = "http://" . $location;
            }
            if( !is_null($timeout) && is_int($timeout) ){
                $this->writeHead("Refresh", $timeout . ";url=" . $location);
            }
            else{
                $this->writeHead("Location", $location);
            }
            $this->withStatus("", 302);
        }

        /**
         * @param string $data
         * Send 401 response
         */
        public function unauthorized($data = ""){
            $this->withStatus($data, 401);
        }

        /**
         * @param $response mixed
         * @param $statusCode integer
         *
         * Sends response with a specific status code
         */
        public function withStatus($response, $statusCode){
            $this->setStatusCode($statusCode);
            $this->processResponse($response);
        }

        /**
         * @param $data
         *
         * @throws \Locrian\IO\IOException
         * @throws \Locrian\InvalidArgumentException
         * Process the response body, create input output streams if necessary
         */
        private function processResponse($data){
            if( is_string($data) || is_numeric($data) ){
                $this->writeHead("Content-Type", "text/html;charset=utf-8");
                $this->write($data);
            }
            else if( $data instanceof File ){
                if( $data->exists() && $data->isFile() ){
                    if( $data instanceof DownloadFile ){
                        $this->writeHead("Content-Disposition", "attachment;filename=" . $data->getDownloadName());
                    }
                    else{
                        $this->writeHead("Content-Disposition", "attachment;filename=" . $data->getName());
                    }
                    $this->writeHead("Content-Type", $data->getMime());
                    $this->writeHead("Expires", "0");
                    $this->writeHead("Pragma", "no-cache");
                    $this->writeHead("Content-Length", $data->getSize());
                    $bufferSize = 1024;
                    if( $this->outputStream->getBufferSize() > 1024 ){
                        $bufferSize = $this->outputStream->getBufferSize();
                    }
                    $in = new BufferedInputStream($data, $bufferSize);
                    while( ($chunk = $in->read()) !== null ){
                        $this->write($chunk);
                    }
                    $in->close();
                }
                else{
                    throw new IOException("File not found: " . $data->getPath());
                }
            }
            else if( $data instanceof ResponseBuilder ){
                $headers = $data->getHeaders();
                foreach( $headers as $key => $value ){
                    $this->writeHead($key, $value);
                }
                $this->write($data->getContent());
            }
            else{
                throw new InvalidArgumentException("Response body can only be string, File object or ResponseBuilder object");
            }
        }

        /**
         * Finalize response and close output stream
         */
        public function finalize(){
            $this->outputStream->flush();
            $this->outputStream->close();
        }

        /**
         * @return \Locrian\IO\BufferedOutputStream
         */
        public function getOutputStream(){
            return $this->outputStream;
        }

        /**
         * @return bool
         */
        public function isOBEnabled(){
            return $this->outputBuffer;
        }

        /**
         * @return string|null
         * Returns status message line Ok, Not Found...
         */
        public function getStatusMessage(){
            return $this->statusCodeList->getMessage($this->statusCode);
        }

        /**
         * @return bool
         */
        public function isInformational(){
            return $this->getStatusCode() >= 100 && $this->getStatusCode() < 200;
        }

        /**
         * @return bool
         */
        public function isOk(){
            return $this->getStatusCode() === 200;
        }

        /**
         * @return bool
         */
        public function isSuccessful(){
            return $this->getStatusCode() >= 200 && $this->getStatusCode() < 300;
        }

        /**
         * @return bool
         */
        public function isRedirect(){
            return in_array($this->getStatusCode(), [301, 302, 303, 307]);
        }

        /**
         * @return bool
         */
        public function isForbidden(){
            return $this->getStatusCode() === 403;
        }

        /**
         * @return bool
         */
        public function isNotFound(){
            return $this->getStatusCode() === 404;
        }

        /**
         * @return bool
         */
        public function isClientError(){
            return $this->getStatusCode() >= 400 && $this->getStatusCode() < 500;
        }

        /**
         * @return bool
         */
        public function isServerError(){
            return $this->getStatusCode() >= 500 && $this->getStatusCode() < 600;
        }

    }