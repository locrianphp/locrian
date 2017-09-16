<?php

    /**
     * Created by PhpStorm.
     * User: social13
     * Date: 16.09.2017
     * Time: 12:53
     */

    namespace Locrian\Tests;

    use Locrian\Core\Http\Response;
    use Locrian\Util\OBUtils;
    use PHPUnit_Framework_TestCase;

    class ResponseTest extends PHPUnit_Framework_TestCase{

        /**
         * @var Response
         */
        private $response;

        protected function setUp(){
            $this->response = $response = new Response(1024, "1.1");
        }

        public function testWriteHead(){
            $expected = [ "Connection" => "Close", "Content-Type" => "text/html" ];
            $this->response->writeHead("Connection", "Close");
            $this->response->writeHead("Content-Type", "text/html");
            $this->response->getHeaders()->each(function($key, $value) use($expected){
                self::assertEquals($expected[$key], $value);
            });
        }

        public function testWrite(){
            $expected = "Hello world";
            $content = OBUtils::callbackBuffer(function() use($expected){
                $this->response->write($expected, true);
            });
            self::assertEquals($expected, $content);
        }

        public function testGetters(){
            self::assertEquals("1.1", $this->response->getProtocolVersion());
            self::assertEquals(200, $this->response->getStatusCode());
            self::assertFalse($this->response->isOBEnabled());
            $this->response->setContentType("text/html");
            self::assertEquals("text/html", $this->response->getHeaders()->get("Content-Type"));
            self::assertEquals("OK", $this->response->getStatusMessage());
            self::assertTrue($this->response->isOk() && $this->response->isSuccessful());
        }

    }