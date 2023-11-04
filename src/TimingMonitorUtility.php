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

  public function getTimingMonitorTypes(): array {
    $data = [];

    $select = $this->database->select('timing_monitor_log', 'tm')->fields('tm', ['type']);
    $select->addExpression('COUNT(*)', 'c');
    $select->addExpression('AVG(duration)', 'a');
    $results = $select->groupBy('type')->execute()->fetchAll();

    foreach ($results as $r) {
      $data[$r->type] = ['id' => $r->type, 'count' => $r->c, 'avg' => $r->a];
    }

    return $data;
  }

  public function getTimingMonitorTypeList(string $type, int $page = 0, int $count = 50, string $sort_order = "DESC"): array {

    // Validate that we have a proper sort order,
    // so the query does not error out.
    if ($sort_order !== "ASC" && $sort_order !== "DESC") {
      $sort_order = "DESC";
    }

    $select = $this->database->select('timing_monitor_log', 'tm')->fields('tm', []);

    $select->condition('type', $type, "LIKE");
    $select->orderBy('id', $sort_order);
    $select->range(($page * $count), $count);

    return $select->execute()->fetchAll(\PDO::FETCH_ASSOC);
  }

}
