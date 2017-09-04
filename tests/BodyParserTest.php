<?php

    /**
     * Created by PhpStorm.
     * User: social13
     * Date: 04.09.2017
     * Time: 17:41
     */

    namespace Locrian\Tests;

    use Locrian\Core\Http\Parsers\JsonParser;
    use Locrian\Core\Http\Parsers\UrlEncodedParser;
    use Locrian\Core\Http\Parsers\XmlParser;
    use PHPUnit_Framework_TestCase;

    class BodyParserTest extends PHPUnit_Framework_TestCase{

        public function testJsonParser(){
            $json = '{"num": 7}';
            $parser = new JsonParser();
            $parsed = $parser->parse($json);
            self::assertEquals(7, $parsed['num']);
        }

        public function testUrlEncodedParser(){
            $str = "action=click&publisher_id=9558&site_id=2385";
            $parser = new UrlEncodedParser();
            $parsed = $parser->parse($str);
            self::assertEquals("click", $parsed['action']);
            self::assertEquals(9558, $parsed["publisher_id"]);
            self::assertEquals(2385, $parsed["site_id"]);
        }

        public function testXmlParser(){
            $xml = "<content><num>7</num></content>";
            $parser = new XmlParser();
            $parsed = $parser->parse($xml);
            self::assertEquals(7, intval($parsed->num));
        }

    }