<?php
namespace Bangpound\Silex;

use Bangpound\LegacyPhp\EventListener\HeaderListener;
use Bangpound\LegacyPhp\EventListener\OutputBufferListener;
use Bangpound\LegacyPhp\EventListener\ShutdownListener;
use Bangpound\LegacyPhp\HttpKernel;
use Silex\Application;
use Silex\ServiceProviderInterface;

class LegacyPhpHttpKernelProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
        $app['kernel'] = $app->share(function ($c) {
            return new HttpKernel($c['dispatcher'], $c['resolver'], $c['request_stack']);
        });
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     */
    public function boot(Application $app)
    {
        $dispatcher = $app['dispatcher'];

        $dispatcher->addSubscriber(new OutputBufferListener($app['legacy.request_matcher']));
        $dispatcher->addSubscriber(new ShutdownListener($app['legacy.request_matcher']));
        $dispatcher->addSubscriber(new HeaderListener($app['legacy.request_matcher']));
    }
}
