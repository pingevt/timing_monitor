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

    $data['data'] = $this->tmUtility->getTimingMonitorTypes();

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

    // Page is 0 based.
    $page = $request->get('page') ?? 0;
    $count = $request->get('count') ?? 50;
    $sort = $request->get('sort') ?? "DESC";

    if ($sort !== "ASC" && $sort !== "DESC") {
      $data['status'] = "WARNING";
      $data['msg'] = "Bad sort paramater, defaulting to DESC";
      $sort = "DESC";
    }

    $data['data'] = $this->tmUtility->getTimingMonitorTypeList($type, $page, $count, $sort);

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

    // @todo validate start.
    // $start_day_obj = $request->get('start') ? \DateTime::createFromFormat("Y-m-d", $request->get('start')) : new \DateTime();
    // $start_day_obj->setTime(23, 59, 59);
    $days = $request->get('days') ? (int) $request->get('days') : 7;
    // $end_day_obj = (clone $start_day_obj)->modify("-$days days");
    // $end_day_obj->setTime(0, 0, 0);

    if ($request->get('start') && $request->get('end')) {
      $start_day_obj = \DateTime::createFromFormat("Y-m-d", $request->get('start'));
      $end_day_obj = \DateTime::createFromFormat("Y-m-d", $request->get('end'));
    }
    else if ($request->get('start') && !$request->get('end')) {
      $start_day_obj = \DateTime::createFromFormat("Y-m-d", $request->get('start'));
      $end_day_obj = (clone $start_day_obj)->modify("-" . $days . " days");
    }
    else if (!$request->get('start') && $request->get('end')) {
      $end_day_obj = \DateTime::createFromFormat("Y-m-d", $request->get('end'));
      $start_day_obj = (clone $end_day_obj)->modify("+" . ($days - 1). " days");
    }
    else {
      $start_day_obj = new \DateTime();
      $end_day_obj = (clone $start_day_obj)->modify("-" . $days . " days");
    }

    $start_day_obj->setTime(23, 59, 59);
    $end_day_obj->setTime(0, 0, 0);


    // @todo setup caching.

    $dates = $this->tmUtility->getTimingMonitorDailyAverage($type, $start_day_obj, $end_day_obj, $days);

    $data['data']['dates'] = $dates;

    // Send response.
    $response = new CacheableJsonResponse($data);
    return $response;
  }

}
