<?php

namespace Drupal\timing_monitor\Routing;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
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
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler) {
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler')
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

      // Status.
      $routes['timing_monito.api.status'] = new Route(
        '/api/timing-monitor/status',
        [
          '_controller' => '\Drupal\timing_monitor\Controller\Api::status',
        ]
      );

      // Type.
      $routes['timing_monito.api.types'] = new Route(
        '/api/timing-monitor/types',
        [
          '_controller' => '\Drupal\timing_monitor\Controller\Api::types',
        ]
      );

      // Type list.
      $routes['timing_monito.api.type.list'] = new Route(
        '/api/timing-monitor/{type}/list',
        [
          '_controller' => '\Drupal\timing_monitor\Controller\Api::typeList',
        ]
      );

      // Type Daily average.
      $routes['timing_monito.api.type.daily_average'] = new Route(
        '/api/timing-monitor/{type}/daily-average',
        [
          '_controller' => '\Drupal\timing_monitor\Controller\Api::dailyAverage',
        ]
      );

      // Auth options.
      // @todo setup settings and options for Authorization.
      foreach ($routes as $route => $r_data) {
        $r_data->setMethods(['GET']);
        $r_data->setRequirement('_permission', 'use timing log api');

        if ($this->moduleHandler->moduleExists('key_auth')) {
          $r_data->setOption('_auth', ['key_auth']);
        }
        $route_collection->add($route, $r_data);
      }
    }

    return $route_collection;
  }

}
