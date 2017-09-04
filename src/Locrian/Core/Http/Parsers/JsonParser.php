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

    class JsonParser implements BodyParser{

        /**
         * @param $content string
         * @return mixed
         */
        public function parse($content){
            $result = json_decode($content, true);
            if (!is_array($result)) {
                return null;
            }
            return $result;
        }

    }