<?php

namespace Drupal\timing_monitor;

use Drupal\Core\Logger\RfcLogLevel;

/**
 * Add in Timing Monitor.
 */
class TimingMonitor {

  /**
   *
   */
  protected $startTime = NULL;
  protected $uuid = NULL;

  /**
   *
   */
  protected $monLog = [];

  private static $instance = null;

  private function __construct() {
  }

  public static function hasInstance() {
    return !(self::$instance == null);
  }

  public static function getInstance() {
    if (self::$instance == null) {
      self::$instance = new TimingMonitor();
      self::$instance->initTimingMonitor();
      $uuid_service = \Drupal::service('uuid');
      self::$instance->uuid = $uuid_service->generate();

      self::$instance->logTiming(RfcLogLevel::DEBUG, "timing_monitor", "init", []);
    }

    return self::$instance;
  }

  protected function initTimingMonitor() {
    if ($this->startTime == NULL) {
      $this->startTime = microtime(TRUE);
    }
  }

  public function logTiming($severity, $type, $msg = "", $vars = []) {

    $this->monLog[] = [
      'type' => $type,
      'timer' => microtime(true) - $this->startTime,
      'severity' => $severity,
      'msg' => $msg,
      'vars' => $vars,
      'timestamp' => time(),
    ];
  }

  public function saveTimingLog() {
    $request = \Drupal::request();
    $current_user_id = (int) \Drupal::currentUser()->id();

    $data = [];

    foreach ($this->monLog as $log) {
      $data[] = [
        'uid' => $current_user_id,
        'session_uuid' => $this->uuid,
        'type' => $log['type'],
        'message' => $log['msg'],
        'variables' => serialize($log['vars']),
        'severity' => $log['severity'],
        'path' => $request->getRequestUri(),
        'method' => $request->getMethod(),
        'timer' => $log['timer'],
        'timestamp' => $log['timestamp'],
      ];
    }
    if (!empty($data)) {
      $this->saveLogToDb($data);
    }

  }

  protected function saveLogToDb($data) {
    $request = \Drupal::request();

    $insert = \Drupal::service('database')->insert('timing_monitor_log');
    $insert->fields(array_keys($data[0]));

    foreach ($data as $d) {
      $insert->values($d);
    }

    try {
      $insert->execute();
    }
    catch (\Exception $e) {
      throw $e;
    }
  }

}
