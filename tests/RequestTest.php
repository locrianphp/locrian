<?php

    /**
     * Created by PhpStorm.
     * User: social13
     * Date: 09.09.2017
     * Time: 00:06
     */

    namespace Locrian\Tests;

    use Locrian\Core\Http\RequestFactory;
    use Locrian\Core\Http\UploadedFile;
    use Locrian\Core\MVC\Routing\Route;
    use Locrian\Core\MVC\Routing\Router;
    use Locrian\Http\Cookie;
    use Locrian\Http\Session\Session;
    use PHPUnit_Framework_TestCase;

    class RequestTest extends PHPUnit_Framework_TestCase{

        /**
         * @var \Locrian\Core\Http\Request
         */
        private $request;

        protected function setUp(){
            $_SERVER = [
                "HTTP_HOST" => "local.dev",
                "HTTP_USER_AGENT" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:55.0) Gecko/20100101 Firefox/55.0",
                "HTTP_ACCEPT" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
                "HTTP_ACCEPT_LANGUAGE" => "en;q=0.5,en-US",
                "HTTP_ACCEPT_ENCODING" => "gzip, deflate",
                "HTTP_DNT" => "1",
                "HTTP_CONNECTION" => "keep-alive",
                "HTTP_PRAGMA" => "no-cache",
                "HTTP_CACHE_CONTROL" => "no-cache",
                "PATH" => "/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin",
                "SERVER_SOFTWARE" => "Apache/2.4.18 (Ubuntu)",
                "SERVER_NAME" => "local.dev",
                "SERVER_ADDR" => "127.0.0.1",
                "SERVER_PORT" => "80",
                "REMOTE_ADDR" => "127.0.0.1",
                "DOCUMENT_ROOT" => "/var/www/html",
                "REQUEST_SCHEME" => "http",
                "CONTEXT_PREFIX" => "",
                "CONTEXT_DOCUMENT_ROOT" => "/var/www/html",
                "SCRIPT_FILENAME" => "/var/www/html/test/index.php",
                "REMOTE_PORT" => "45734",
                "GATEWAY_INTERFACE" => "CGI/1.1",
                "SERVER_PROTOCOL" => "HTTP/1.1",
                "REQUEST_METHOD" => "GET",
                "QUERY_STRING" => "qp=1&qp2=1",
                "REQUEST_URI" => "/test/?qp=1&qp2=1",
                "SCRIPT_NAME" => "/test/index.php",
                "PHP_SELF" => "/test/index.php",
                "REQUEST_TIME_FLOAT" => "1504948574.48",
                "REQUEST_TIME" => "1504948574",
                "HTTP_X_REQUESTED_WITH" => "XMLHttpRequest"
            ];
            $_FILES = [
                "image" => [
                    "name" => [ "400.png", "401.png" ],
                    "type" => [ "image/png", "image/png" ],
                    "tmp_name" => [ "/tmp/php5Wx0aJ", "/tmp/php5Wx087" ],
                    "error" => [ 0, 0 ],
                    "size" => [ 15735, 19775 ]
                ]
            ];
            $router = Router::getInstance();
            $router->clearRoutes();
            Router::get("/profile/{username}", function(Route $route){
                $route->setName("some.name");
            });
            $route = $router->find("GET", "/profile/locrian");
            $this->request = RequestFactory::createRequest(new Session("id"), new Cookie(), $route);
        }

        public function testSimpleGetters(){
            self::assertEquals("127.0.0.1", $this->request->getClientIp());
            self::assertEquals("http://local.dev/test/?qp=1&qp2=1", $this->request->getUri()->__toString());
            self::assertEquals("1.1", $this->request->getProtocolVersion());
            self::assertEquals("GET", $this->request->getMethod());
            self::assertEquals("locrian", $this->request->getParam("username"));
            self::assertEquals("locrian", $this->request->getPathParam("username"));
            self::assertEquals("1", $this->request->getParam("qp"));
            self::assertEquals("1", $this->request->getQueryParam("qp"));
            self::assertNull($this->request->getParam("notExists", null));
            self::assertNull($this->request->getContentLength());
            self::assertNull($this->request->getMediaType());
            self::assertNull($this->request->getContentCharset());
            self::assertEquals(["gzip", "deflate"], $this->request->getEncodings());
            self::assertEquals(["text/html", "application/xhtml+xml", "application/xml", "*/*"], $this->request->getAccept());
            self::assertEquals(["en-US", "en"], $this->request->getAcceptLanguage());
            self::assertFalse($this->request->isClientMobile());
            self::assertFalse($this->request->isClientBot());
            self::assertTrue($this->request->isGet());
            self::assertFalse($this->request->isConnect());
            self::assertTrue($this->request->isXhr());
            self::assertTrue($this->request->isConnectionPersistent());
        }

        public function testHeaders(){
            $headers = [
                "Host" => "local.dev",
                "User-Agent" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:55.0) Gecko/20100101 Firefox/55.0",
                "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
                "Accept-Language" => "en;q=0.5,en-US",
                "Accept-Encoding" => "gzip, deflate",
                "Dnt" => "1",
                "Connection" => "keep-alive",
                "Pragma" => "no-cache",
                "Cache-Control" => "no-cache",
                "X-Requested-With" => "XMLHttpRequest"
            ];
            $this->request->getHeaders()->each(function($key, $value) use($headers){
                self::assertEquals($headers[$key], $value);
            });
        }

        public function testFiles(){
            $this->request->getFiles()->each(function($index, UploadedFile $file){
                self::assertEquals($_FILES["image"]["name"][$index], $file->getName());
                self::assertEquals($_FILES["image"]["size"][$index], $file->getSize());
                self::assertEquals($_FILES["image"]["type"][$index], $file->getType());
                self::assertEquals($_FILES["image"]["tmp_name"][$index], $file->getTmpName());
            });
        }

        public function testFiles2(){
            $_FILES = [
                "image" => [
                    "name" => "400.png",
                    "type" => "image/png",
                    "tmp_name" => "/tmp/php5Wx0aJ",
                    "error" => 0,
                    "size" => 9775
                ]
            ];
            $request = RequestFactory::createRequest(new Session("id"), new Cookie(), new Route("", ""));
            $request->getFiles()->each(function($index, UploadedFile $file){
                self::assertEquals($_FILES["image"]["name"], $file->getName());
                self::assertEquals($_FILES["image"]["size"], $file->getSize());
                self::assertEquals($_FILES["image"]["type"], $file->getType());
                self::assertEquals($_FILES["image"]["tmp_name"], $file->getTmpName());
            });
        }

        public function testFiles3(){
            $_FILES = [
                "image" => [
                    "name" => "400.png",
                    "type" => "image/png",
                    "tmp_name" => "/tmp/php5Wx0aJ",
                    "error" => 0,
                    "size" => 9775
                ],
                "image2" => [
                    "name" => "4001.png",
                    "type" => "image/png",
                    "tmp_name" => "/tmp/php5Wx0a1",
                    "error" => 0,
                    "size" => 5614651
                ]
            ];
            $request = RequestFactory::createRequest(new Session("id"), new Cookie(), new Route("", ""));
            $request->getFiles()->each(function($index, UploadedFile $file){
                if( $file->getName() === "400.png" ){
                    $arr = $_FILES["image"];
                }
                else{
                    $arr = $_FILES['image2'];
                }
                self::assertEquals($arr["name"], $file->getName());
                self::assertEquals($arr["size"], $file->getSize());
                self::assertEquals($arr["type"], $file->getType());
                self::assertEquals($arr["tmp_name"], $file->getTmpName());
            });
        }

        protected function tearDown(){
            Router::getInstance()->clearRoutes();
        }

    }