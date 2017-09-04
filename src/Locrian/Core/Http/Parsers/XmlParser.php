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

    class XmlParser implements BodyParser{

        /**
         * @param $content string
         * @return mixed
         */
        public function parse($content){
            $backupDisableEntityLoader = libxml_disable_entity_loader(true);
            $backupUseInternalErrors = libxml_use_internal_errors(true);
            $result = simplexml_load_string($content);
            libxml_disable_entity_loader($backupDisableEntityLoader);
            libxml_clear_errors();
            libxml_use_internal_errors($backupUseInternalErrors);
            if ($result === false) {
                return null;
            }
            return $result;
        }

    }