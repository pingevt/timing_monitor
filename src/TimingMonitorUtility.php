<?php

namespace Drupal\timing_monitor;

/**
 * Add in Timing Monitor.
 */
class TimingMonitorUtility {

  /**
   * Craete a csv safe test string.
   */
  public function csvEscape(array $data, $delimiter = ',') {
    $buffer = fopen('php://temp', 'r+');
    fputcsv($buffer, $data, $delimiter);
    rewind($buffer);
    $csv = fgets($buffer);
    fclose($buffer);

    return rtrim($csv);
  }

}
