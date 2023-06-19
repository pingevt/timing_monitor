<?php

namespace Drupal\timing_monitor;

/**
 * Add in Timing Monitor.
 *
 * The timing monitor is meant to be used as a Singleton class. Once intiated,
 * you can use the monitor anywhere in your code.
 */
class TimingMonitor {

  const START = "start";
  const MARK = "mark";
  const FINISH = "finish";

  /**
   * The start time of the monitor.
   *
   * @var float|null
   */
  protected $startTime = NULL;

  /**
   * UUID for this instance.
   *
   * @var string
   */
  protected $uuid = NULL;

  /**
   * Helper array to keep the time of the "starts".
   *
   * @var array
   */
  protected $starts = [];

  /**
   * Array of log messages.
   *
   * @var array
   */
  protected $monLog = [];

  /**
   * The instance of the Singleton.
   *
   * @var \Drupal\timing_monitor\TimingMonitor
   */
  private static $instance = NULL;

  /**
   * Constructor.
   */
  private function __construct() {
  }

  /**
   * Check to see if an instance has been initiated.
   */
  public static function hasInstance():bool {
    return !(self::$instance == NULL);
  }

  /**
   * Get the instance of the singleton.
   */
  public static function getInstance() {
    if (self::$instance == NULL) {
      self::$instance = new TimingMonitor();
      self::$instance->initTimingMonitor();
      $uuid_service = \Drupal::service('uuid');
      self::$instance->uuid = $uuid_service->generate();

      // self::$instance->logTiming("timing_monitor", marker: "start", msg: "init", vars: []);
      // Initialise at 0.
      self::$instance->monLog[] = [
        'type' => "timing_monitor",
        'marker' => "start",
        'timer' => 0,
        'msg' => "init timing_monitor",
        'duration' => NULL,
        'vars' => [],
        'timestamp' => time(),
      ];

      self::$instance->starts["timing_monitor"] = 0;
    }

    return self::$instance;
  }

  /**
   * Initiate the monitor.
   */
  protected function initTimingMonitor() {
    if ($this->startTime == NULL) {
      $this->startTime = microtime(TRUE);
    }
  }

  /**
   * Create a timing log.
   *
   * @param string $type
   *   The log type.
   * @param string $marker
   *   The type of Marker, start|mark|finish.
   * @param string $msg
   *   The message to log.
   * @param array $vars
   *   The variables used for the message.
   */
  public function logTiming(string $type, string $marker = "mark", string $msg = "", array $vars = []) {
    $timer = microtime(TRUE) - $this->startTime;

    // Set starts.
    // Will overwrite if already exists, regardless if one ever finishes.
    if ($marker == "start") {
      $this->starts[$type] = $timer;
    }

    // Calculate duration.
    $duration = NULL;
    if (($marker == "mark" || $marker == "finish") && isset($this->starts[$type])) {
      $duration = $timer - $this->starts[$type];
    }

    $this->monLog[] = [
      'type' => $type,
      'marker' => $marker,
      'timer' => $timer,
      'duration' => $duration,
      'msg' => $msg,
      'vars' => $vars,
      'timestamp' => time(),
    ];
  }

  /**
   * Format data and save to the database.
   */
  public function saveTimingLog() {
    // @todo what happens if this is called multiple times?
    // Finish timing.
    $this->logTiming("timing_monitor", marker: "finish", msg: "finish", vars: []);

    $request = \Drupal::request();
    $current_user_id = (int) \Drupal::currentUser()->id();

    $data = [];

    foreach ($this->monLog as $log) {
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
        'duration' => $log['duration'],
        'timestamp' => $log['timestamp'],
      ];
    }
    if (!empty($data)) {
      $this->saveLogToDb($data);
    }
  }

  /**
   * Saved the stored log to the Database.
   *
   * @param array $data
   *   The data to be logged.
   */
  protected function saveLogToDb(array $data) {

    $insert = \Drupal::service('database')->insert('timing_monitor_log');
    $insert->fields(array_keys($data[0]));

    foreach ($data as $d) {
      $insert->values($d);
    }

    try {
      $insert->execute();
    }
    catch (\Exception $e) {
      \Drupal::logger('timing_monitor')->error($e->getMessage() . "\r\n\r\n<pre>" . print_r($data, TRUE) . "</pre>");
      // throw $e;
    }
  }

}
