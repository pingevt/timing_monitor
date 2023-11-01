<?php

namespace Drupal\timing_monitor\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for API responses.
 */
class Api extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  public function status(Request $request): CacheableJsonResponse {

    $data = [
      "status" => "OK",
      "data" => [
        "count" => 0,
        "type_count" => 0,
      ],
    ];

    $select = $this->database->select('timing_monitor_log');
    $data['data']['count'] = $select->countQuery()->execute()->fetchField();

    $select = $this->database->select('timing_monitor_log');
    $data['data']['type_count'] = $select->groupBy('type')->countQuery()->execute()->fetchField();

    $response = new CacheableJsonResponse($data);
    return $response;
  }

  public function types(Request $request): CacheableJsonResponse {

    $data = [
      "status" => "OK",
      "data" => [],
    ];

    $select = $this->database->select('timing_monitor_log', 'tm')->fields('tm', ['type']);
    $select->addExpression('COUNT(*)', 'c');
    $results = $select->groupBy('type')->execute()->fetchAll();

    foreach($results as $r) {
      $data['data'][$r->type] = ['id' => $r->type, 'count' => $r->c];
    }

    $response = new CacheableJsonResponse($data);
    return $response;
  }

}
