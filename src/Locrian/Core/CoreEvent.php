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

    use Locrian\Bus\Event;

    class CoreEvent extends Event{

        /**
         * Error event type
         */
        const ERROR = "error";

        /**
         * @param $message
         * @return \Locrian\Bus\Event
         * Creates new Event with type ERROR
         */
        public static function createErrorEvent($message){
            return new Event(self::ERROR, $message);
        }

    }