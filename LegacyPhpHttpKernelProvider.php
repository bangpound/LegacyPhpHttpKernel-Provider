<?php
namespace Bangpound\Silex;

use Bangpound\LegacyPhp\EventListener\HeaderListener;
use Bangpound\LegacyPhp\EventListener\OutputBufferListener;
use Bangpound\LegacyPhp\EventListener\ShutdownListener;
use Bangpound\LegacyPhp\HttpKernel;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestMatcher;

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
        $app['kernel'] = $app->share(
            function ($c) {
                return new HttpKernel($c['dispatcher'], $c['resolver'], $c['request_stack']);
            }
        );

        $app['legacy.request_matcher'] = $app->share(
            function () {
                return new RequestMatcher();
            }
        );

        $app['legacy.listener.output_buffer'] = $app->share(
            function ($c) {
                return new OutputBufferListener($c['legacy.request_matcher']);
            }
        );
        $app['legacy.listener.shutdown'] = $app->share(
            function ($c) {
                return new ShutdownListener($c['legacy.request_matcher']);
            }
        );
        $app['legacy.listener.header'] = $app->share(
            function ($c) {
                return new HeaderListener($c['legacy.request_matcher']);
            }
        );
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
        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $app['dispatcher'];
        $dispatcher->addSubscriber($app['legacy.listener.output_buffer']);
        $dispatcher->addSubscriber($app['legacy.listener.shutdown']);
        $dispatcher->addSubscriber($app['legacy.listener.header']);
    }
}
