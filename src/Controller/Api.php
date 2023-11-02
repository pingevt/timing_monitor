<?php

namespace Drupal\timing_monitor\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Database\Connection;
use Drupal\timing_monitor\TimingMonitor;
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

  /**
   * Callback for timing monitor status.
   */
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

  /**
   * Callback for timing monitor types.
   */
  public function types(Request $request): CacheableJsonResponse {

    $data = [
      "status" => "OK",
      "data" => [],
    ];

    $select = $this->database->select('timing_monitor_log', 'tm')->fields('tm', ['type']);
    $select->addExpression('COUNT(*)', 'c');
    $results = $select->groupBy('type')->execute()->fetchAll();

    foreach ($results as $r) {
      $data['data'][$r->type] = ['id' => $r->type, 'count' => $r->c];
    }

    $response = new CacheableJsonResponse($data);
    return $response;
  }

  /**
   * Callback for a list of logs for a specific type.
   */
  public function typeList(string $type, Request $request): CacheableJsonResponse {

    $data = [
      "status" => "OK",
      "data" => [],
    ];

    $select = $this->database->select('timing_monitor_log', 'tm')->fields('tm', []);

    $select->condition('type', $type, "LIKE");
    $select->orderBy('id', 'DESC');
    $select->range(0, 50);

    $data['data'] = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);

    $response = new CacheableJsonResponse($data);
    return $response;
  }

  /**
   * Callback for daily avereages for a specific type.
   */
  public function dailyAverage(string $type, Request $request): CacheableJsonResponse | array {

    $data = [
      "status" => "OK",
      "data" => [
        'type' => $type,
        'dates' => [],
      ],
    ];

    // ksm($type);

    // ksm($request->get('startDate'));
    // ksm($request->get('endDate'));

    $type_match = (strpos($type, "%") !== FALSE) ? "LIKE" : "=";
    $today = new \DateTime();
    $today->setTime(23, 59, 59);
    $days = $request->get('days') ?? 7;
    $start_day = $request->get('startDate') ?? $today->format('Y-m-d');
    $end_date_obj = (clone $today)->modify("-$days days");
    $end_date_obj->setTime(0, 0, 0);
    $end_day = $request->get('endDate') ?? $end_date_obj->format('Y-m-d');

    // ksm($start_day, $end_day, $today, $end_date_obj);

    for ($i = 0; $i < $days; $i++) {
      $dates[(clone $today)->modify("-$i days")->format('Y-m-d')] = NULL;
    }




    $select = $this->database->select('timing_monitor_log', 'tm')->fields('tm', []);
    // ksm($this->database, $select);
    // $select->addExpression("DATE_FORMAT(FROM_UNIXTIME(timestamp), :format)", 'date', [":format" => "%Y%m%d"]);
    // $select->condition('type', $type);
    // $select->condition('marker', TimingMonitor::FINISH);
    // $select->groupBy('type', 'timestamp', 'd');
    // $select->orderBy('id', 'DESC');
    // $select->range(0, 50);
    // $results = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);

    $query = $this->database->query('
      SELECT t.date, AVG(duration) avg
      FROM (
        SELECT duration, DATE_FORMAT(FROM_UNIXTIME(timestamp), :format) date
        FROM {timing_monitor_log}
        WHERE type '. $type_match .' :type
        AND marker = :marker
        AND timestamp <= :start_date
        AND timestamp >= :end_date
      ) as t
      GROUP BY date
    ', [
      ":format" => "%Y-%m-%d",
      ":type" => $type,
      ":marker" => TimingMonitor::FINISH,
      ":start_date" => $today->format("U"),
      ":end_date" => $end_date_obj->format("U"),
    ]);
    // ksm($query, $query->getQueryString());
    // ksm($query->arguments());
    $results = $query->fetchAll(\PDO::FETCH_ASSOC);
    // ksm($results);

    foreach ($results as $result) {
      $dates[$result['date']] = (float) $result['avg'];
    }

    $data['data']['dates'] = $dates;

    // ksm($data);
    // return [];

    $response = new CacheableJsonResponse($data);
    return $response;
  }

}
