<?php

namespace Drupal\timing_monitor\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Cache\CacheableJsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for API responses.
 */
class Api extends ControllerBase implements ContainerInjectionInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct() {

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(

    );
  }

  public function status(Request $request): CacheableJsonResponse {

    $data = [
      "status" => "OK",
      "data" => [],
    ];

    $response = new CacheableJsonResponse($data);
    return $response;
  }

}
