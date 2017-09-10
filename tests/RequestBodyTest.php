<?php

    /**
     * Created by PhpStorm.
     * User: social13
     * Date: 04.09.2017
     * Time: 17:56
     */

    namespace Locrian\Tests;

    use Locrian\Core\Http\Parsers\JsonParser;
    use Locrian\Core\Http\RequestBody;
    use Locrian\IO\BufferedInputStream;
    use Locrian\IO\File;

    class RequestBodyTest extends \PHPUnit_Framework_TestCase{

        /**
         * @var \Locrian\Core\Http\RequestBody
         */
        private $requestBody;

        protected function setUp(){
            file_put_contents("tests/tempFile.json", '{"var": 2}');
            $this->requestBody = new RequestBody(new BufferedInputStream(new File("tests/tempFile.json")), new JsonParser());
        }

        public function testRawBody(){
            self::assertEquals('{"var": 2}', $this->requestBody->getRawBody());
        }

        public function testParsedBody(){
            self::assertEquals(["var" => 2], $this->requestBody->getParsedBody());
        }

        public function testGetParam(){
            self::assertEquals(2, $this->requestBody->getParam("var"));
        }

        protected function tearDown(){
            $this->requestBody->getInputStream()->close();
            unlink("tests/tempFile.json");
        }

    }