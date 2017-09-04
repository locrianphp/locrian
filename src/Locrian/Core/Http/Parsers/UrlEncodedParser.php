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

    namespace Locrian\Core\Http\Parsers;

    class UrlEncodedParser implements BodyParser{

        /**
         * @param $content string
         * @return mixed
         */
        public function parse($content){
            $result = null;
            parse_str($content, $result);
            return $result;
        }

    }