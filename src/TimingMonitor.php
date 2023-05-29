<?php

namespace Drupal\timing_monitor;

/**
 * Add in Timing Monitor.
 */
class TimingMonitor {

  const START = "start";
  const MARK = "mark";
  const FINISH = "finish";

  /**
   *
   */
  protected $startTime = NULL;
  protected $uuid = NULL;

  protected $starts = [];

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

      self::$instance->logTiming("timing_monitor", marker: "start", msg: "init", vars: []);
    }

    return self::$instance;
  }

  protected function initTimingMonitor() {
    if ($this->startTime == NULL) {
      $this->startTime = microtime(TRUE);
    }
  }

  public function logTiming($type, $marker = "mark", $msg = "", $vars = []) {
    $timer = microtime(true) - $this->startTime;

    // Set starts.
    if ($marker == "start") {
      $this->starts[$type] = $timer;
    }

    $this->monLog[] = [
      'type' => $type,
      'marker' => $marker,
      'timer' => $timer,
      'msg' => $msg,
      'vars' => $vars,
      'timestamp' => time(),
    ];
  }

  public function saveTimingLog() {
    // Finish timing.
    $this->logTiming("timing_monitor", marker: "finish", msg: "finish", vars: []);

    $request = \Drupal::request();
    $current_user_id = (int) \Drupal::currentUser()->id();

    $data = [];

    foreach ($this->monLog as $log) {
      // Calculate duration.
      $duration = NULL;
      if (($log['marker'] == "mark" || $log['marker'] == "finish") && isset($this->starts[$log['type']])) {
        $duration = $log['timer'] - $this->starts[$log['type']];
      }

      $data[] = [
        'uid' => $current_user_id,
        'session_uuid' => $this->uuid,
        'type' => $log['type'],
        'marker' => $log['marker'],
        'message' => $log['msg'],
        'variables' => serialize($log['vars']),
        'path' => $request->getRequestUri(),
        'method' => $request->getMethod(),
        'timer' => $log['timer'],
        'duration' => $duration,
        'timestamp' => $log['timestamp'],
      ];
    }
    if (!empty($data)) {
      $this->saveLogToDb($data);
    }

  }

  protected function saveLogToDb($data) {

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
