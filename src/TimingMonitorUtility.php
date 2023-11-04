<?php

namespace Drupal\timing_monitor;

use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add in Timing Monitor.
 */
class TimingMonitorUtility implements ContainerInjectionInterface {

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

  public function getTimingMonitorStatus(): array {
    $data = [
      "count" => 0,
      "type_count" => 0,
    ];

    $select = $this->database->select('timing_monitor_log');
    $data['count'] = $select->countQuery()->execute()->fetchField();

    $select = $this->database->select('timing_monitor_log');
    $data['type_count'] = $select->groupBy('type')->countQuery()->execute()->fetchField();

    return $data;
  }

  }

}
