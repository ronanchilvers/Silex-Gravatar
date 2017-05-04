<?php


namespace SilexGravatar;

use Silex\Application;
use Silex\Api\BootableProviderInterface;

use Gravatar\Service,
    Gravatar\Cache\FilesystemCache,
    Gravatar\Cache\ExpiringCache,
    Gravatar\Extension\Twig\GravatarExtension as TwigGravatarExtension;

class GravatarExtension implements BootableProviderInterface
{
    public function boot(Application $app)
    {
        $app['gravatar.cache'] = function () use ($app) {
            $cache = null;
            if(isset($app['gravatar.cache_dir'])) {
                $ttl   = isset($app['gravatar.cache_ttl']) ? $app['gravatar.cache_ttl'] : 360;
                $file  = new FilesystemCache($app['gravatar.cache_dir']);
                $cache = new ExpiringCache($file, $ttl);
            }
            return $cache;
        };

        $app['gravatar'] = function () use ($app) {
            $options = isset($app['gravatar.options']) ? $app['gravatar.options'] : array();
            return new Service($options, $app['gravatar.cache']);
        };

        if (isset($app['twig'])) {
            $app['twig'] = $app->extend('twig', function ($twig, $app) {
                $twig->addExtension(new TwigGravatarExtension($app['gravatar']));

                return $twig;
            });
        }
    }
}
