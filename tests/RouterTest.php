<?php

    /**
     * Created by PhpStorm.
     * User: social13
     * Date: 30.07.2017
     * Time: 11:48
     */

    namespace Locrian\Tests;

    use Locrian\Core\MVC\Routing\Route;
    use Locrian\Core\MVC\Routing\Router;
    use PHPUnit_Framework_TestCase;

    class RouterTest extends PHPUnit_Framework_TestCase{

//        public function testPregReplace(){
//            self::assertEquals("/test/.*/profile", preg_replace("@{.*}@", ".*", "/test/{username}/profile"));
//            preg_match_all("@{([^{/]*)}@", "/test/{username}/profile/{age}", $matched);
//            print_r($matched);
//            preg_match_all("@/test/([^/]+)/profile/page/([^/]+)@", "/test/social13/profile/page/5", $matched);
//            print_r($matched);
//        }

        protected function setUp(){
            Router::get("/profile/{username}", function(Route $route){
               $route->setController("HomeController")  // This must be fully qualified name with namespace
                    ->setMethod("indexGet")
                    ->setName("user.profile.get");
            });
            Router::get("/profile/{username}/friends/{friendName}", function(Route $route){
                $route->setController("HomeController")  // This must be fully qualified name with namespace
                      ->setMethod("indexGet2")
                      ->setName("user.friend.get");
            });
            Router::post("/profile/{username}", function(Route $route){
                $route->setController("HomeController")  // This must be fully qualified name with namespace
                      ->setMethod("indexPost")
                      ->setName("user.profile.post");
            });
            Router::put("/profile/{username}", function(Route $route){
                $route->setController("HomeController")  // This must be fully qualified name with namespace
                      ->setMethod("indexPut")
                      ->setName("user.profile.put");
            });
            Router::delete("/profile/{username}", function(Route $route){
                $route->setController("HomeController")  // This must be fully qualified name with namespace
                      ->setMethod("indexDelete")
                      ->setName("user.profile.delete");
            });
            Router::options("/profile/{username}", function(Route $route){
                $route->setController("HomeController")  // This must be fully qualified name with namespace
                      ->setMethod("indexOptions")
                      ->setName("user.profile.options");
            });
            Router::head("/profile/{username}", function(Route $route){
                $route->setController("HomeController")  // This must be fully qualified name with namespace
                      ->setMethod("indexHead")
                      ->setName("user.profile.head");
            });
        }

        public function testGetRoutes(){
            $getRoutes = [ "/profile/{username}", "/profile/{username}/friends/{friendName}" ];
            $otherRoutes = [ "/profile/{username}" ];
            Router::getInstance()->getRoutes("get")->each(function($k, Route $r) use($getRoutes){
                self::assertTrue(in_array($r->getRoutePattern(), $getRoutes));
            });
            Router::getInstance()->getRoutes("post")->each(function($k, Route $r) use($otherRoutes){
                self::assertTrue(in_array($r->getRoutePattern(), $otherRoutes));
            });
            Router::getInstance()->getRoutes("put")->each(function($k, Route $r) use($otherRoutes){
                self::assertTrue(in_array($r->getRoutePattern(), $otherRoutes));
            });
            Router::getInstance()->getRoutes("delete")->each(function($k, Route $r) use($otherRoutes){
                self::assertTrue(in_array($r->getRoutePattern(), $otherRoutes));
            });
            Router::getInstance()->getRoutes("options")->each(function($k, Route $r) use($otherRoutes){
                self::assertTrue(in_array($r->getRoutePattern(), $otherRoutes));
            });
            Router::getInstance()->getRoutes("head")->each(function($k, Route $r) use($otherRoutes){
                self::assertTrue(in_array($r->getRoutePattern(), $otherRoutes));
            });
        }

        public function testSearchByName(){
            $r = Router::getInstance()->searchByName("user.profile.options");
            self::assertEquals("OPTIONS", $r->getRequestMethod());
            self::assertEquals("/profile/{username}", $r->getRoutePattern());
            self::assertEquals("user.profile.options", $r->getName());
        }

        public function testFind(){
            $r = Router::getInstance()->find("get", "/profile/social13");
            self::assertEquals("user.profile.get", $r->getName());
            $r = Router::getInstance()->find("get", "/profile/social13/friends/friendOfMe");
            self::assertEquals("user.friend.get", $r->getName());
        }

        public function testRouteParams(){
            $expected2 = [ "username" => "social13" ];
            $expected = [ "username" => "social13", "friendName" => "friendOfMe" ];
            $r = Router::getInstance()->find("get", "/profile/social13/friends/friendOfMe");
            self::assertEquals("user.friend.get", $r->getName());
            self::assertEquals($expected, $r->getPathParameters());
            $r = Router::getInstance()->find("get", "/profile/social13");
            self::assertEquals("user.profile.get", $r->getName());
            self::assertEquals($expected2, $r->getPathParameters());
        }

        public function testSlashAtTheEnd(){
            $r = Router::getInstance()->find("get", "/profile/social13/");
            self::assertEquals("user.profile.get", $r->getName());
        }

        public function testAny(){
            Router::any("/help", function(Route $r){
                $r->setController("HelpController")
                    ->setName("help.any");
            });
            $r = Router::getInstance()->find("get", "/help");
            self::assertEquals("help.any", $r->getName());
        }

        protected function tearDown(){
            Router::getInstance()->clearRoutes();
        }

    }