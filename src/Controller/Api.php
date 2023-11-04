<?php

namespace Drupal\timing_monitor\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Database\Connection;
use Drupal\timing_monitor\TimingMonitor;
use Drupal\timing_monitor\TimingMonitorUtility;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for API responses.
 */
class Api extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The Timing Monitor utility.
   *
   * @var \Drupal\timing_monitor\TimingMonitorUtility
   */
  protected $tmUtility;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * {@inheritdoc}
   */
  public function __construct(TimingMonitorUtility $tm_utility, Connection $database) {
    $this->tmUtility = $tm_utility;
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('timing_monitor.utility'),
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

    $data['data'] = $this->tmUtility->getTimingMonitorStatus();

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
  public function dailyAverage(string $type, Request $request): CacheableJsonResponse {

    // Initialize return data.
    $data = [
      "status" => "OK",
      "data" => [
        'type' => $type,
        'dates' => [],
      ],
    ];

    // @todo validate or filter $type.
    $type_match = (strpos($type, "%") !== FALSE) ? "LIKE" : "=";
    // @todo validate start.
    $start_day_obj = $request->get('start') ? \DateTime::createFromFormat("Y-m-d", $request->get('start')) : new \DateTime();
    // ksm(strtotime($request->get('start')));
    // ksm($start_day_obj);
    $start_day_obj->setTime(23, 59, 59);
    $days = $request->get('days') ? (int) $request->get('days') : 7;
    // $start_day = $request->get('start') ?? $start_day_obj->format('Y-m-d');
    $end_date_obj = (clone $start_day_obj)->modify("-$days days");
    $end_date_obj->setTime(0, 0, 0);
    // @todo validate end.
    // $end_day = $request->get('end') ?? $end_date_obj->format('Y-m-d');

    // @todo setup caching.

    // Fill out the return data arrray.
    $dates = [];
    for ($i = 0; $i < ($days); $i++) {
      $dates[(clone $start_day_obj)->modify("-$i days")->format('Y-m-d')] = NULL;
    }

    // Build complex query.
    $query = $this->database->query('
      SELECT t.date, AVG(duration) avg
      FROM (
        SELECT duration, DATE_FORMAT(FROM_UNIXTIME(timestamp), :format) date
        FROM {timing_monitor_log}
        WHERE type ' . $type_match . ' :type
        AND marker = :marker
        AND timestamp <= :start_date
        AND timestamp >= :end_date
      ) as t
      GROUP BY date
    ', [
      ":format" => "%Y-%m-%d",
      ":type" => $type,
      ":marker" => TimingMonitor::FINISH,
      ":start_date" => $start_day_obj->format("U"),
      ":end_date" => $end_date_obj->format("U"),
    ]);

    // Get results in an array of arrays.
    $results = $query->fetchAll(\PDO::FETCH_ASSOC);

    // Fill out return data.
    foreach ($results as $result) {
      if (in_array($result['date'], array_keys($dates))) {
        $dates[$result['date']] = (float) $result['avg'];
      }
    }

    $data['data']['dates'] = $dates;

    // Send response.
    $response = new CacheableJsonResponse($data);
    return $response;
  }

}
