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

    use Locrian\Bus\EventBus;
    use Locrian\Util\Logger;

    class ErrorHandler{

        /**
         * @var \Locrian\Bus\EventBus
         */
        private $bus;

        /**
         * @var \Locrian\Util\Logger
         */
        private $logger;

        /**
         * ErrorHandler constructor.
         *
         * @param \Locrian\Bus\EventBus $eventBus
         * @param \Locrian\Util\Logger $logger
         */
        public function __construct(EventBus $eventBus, Logger $logger){
            $this->bus = $eventBus;
            $this->logger = $logger;
        }

        /**
         * Registers handlers
         */
        public function register(){
            set_error_handler([&$this, 'handleError']);
            set_exception_handler([&$this, 'handleException']);
        }

        /**
         * UnRegisters error handlers
         */
        public function unRegister(){
            restore_error_handler();
            restore_exception_handler();
        }

        /**
         * @param \Throwable $ex
         * Logs the exceptions
         */
        private function handleException($ex){
            $this->logger->error(sprintf("[EXCEPTION] %s. File: %s on line %s", $ex->getMessage(), $ex->getFile(), $ex->getLine()));
            $err = new Error(Error::EXCEPTION_TYPE);
            $err->setClassName(get_class($ex));
            $err->setFile($ex->getFile());
            $err->setLine($ex->getLine());
            $err->setMessage($ex->getMessage());
            $err->setTrace($ex->getTrace());
            $this->bus->publish(CoreEvent::createErrorEvent($err));
        }

        /**
         * @param $errorNo
         * @param $errorContent
         * @param $errorFile
         * @param $errorLine
         */
        private function handleError($errorNo, $errorContent, $errorFile, $errorLine){
            $this->logger->error(sprintf("[ERROR](%s) %s. File: %s on line %s", $errorNo, $errorContent, $errorFile, $errorLine));
            $ex = new \Exception();
            $err = new Error(Error::ERROR_TYPE);
            $err->setClassName("");
            $err->setFile($errorFile);
            $err->setLine($errorLine);
            $err->setMessage($errorContent);
            $err->setTrace($ex->getTrace());
            $this->bus->publish(CoreEvent::createErrorEvent($err));
        }

    }