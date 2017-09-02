<?php
    /**
     * Created by PhpStorm.
     * User: social13
     * Date: 02.09.2017
     * Time: 17:26
     */


    use Locrian\Bus\EventBus;
    use Locrian\Conf\Conf;
    use Locrian\Core\MVC\Routing\Router;
    use Locrian\DI\Container;
    use Locrian\DI\ServiceProvider;
    use Locrian\Util\Logger;

    class DefaultServiceProvider implements ServiceProvider{

        /**
         * @var \Locrian\Conf\Conf
         */
        private $conf;

        /**
         * @var \Locrian\Bus\EventBus
         */
        private $bus;

        public function __construct(Conf $conf, EventBus $kernelBus){
            $this->conf = $conf;
            $this->bus = $kernelBus;
        }

        public function register(Container $container){
            // Add router
            $container->singleton("router", function(){
                return Router::getInstance();
            });

            // Add Conf
            $container->singleton("conf", function(){
                return $this->conf;
            });

            $container->singleton("kernelBus", function(){
                return $this->bus;
            });

            // Add logger
            $container->singleton("logger", function(){
                $files = $this->conf->findAll("locrian.path.log");
                return new Logger($files['info'], $files['warn'], $files['error']);
            });
        }

    }