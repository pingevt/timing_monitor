<?php

namespace Drupal\timing_monitor\Routing;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Defines dynamic routes.
 */
class Routes implements ContainerInjectionInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * Provides dynamic routes.
   */
  public function routes() {

    $route_collection = new RouteCollection();
    $config = $this->configFactory->get('timing_monitor.settings');

    if ($config->get('api')) {

      $routes = [];

      $routes['timing_monito.api.status'] = new Route(
        '/api/timing-monitor/status',
        [
          '_controller' => '\Drupal\timing_monitor\Controller\Api::status',
        ],
        [
          '_permission' => 'use timing log api',
        // ],
        // [
        //   'compiler_class' => 'Drupal\Core\Routing\RouteCompiler',
        //   // '_auth' => ['key_auth']
        ]
      );

      $routes['timing_monito.api.status']->setMethods(['GET']);

      // Auth options.
      // @todo setup settings and options for Authorization.
      // ksm($routes);

      foreach ($routes as $route => $r_data) {
        $route_collection->add($route, $r_data);
      }

      ksm($route_collection);
    }


    return $route_collection;
  }

}
