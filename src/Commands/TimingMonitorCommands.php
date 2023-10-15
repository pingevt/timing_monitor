<?php

namespace Drupal\timing_monitor\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\timing_monitor\TimingMonitorUtility;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class TimingMonitorCommands extends DrushCommands {

  /**
   * The Timing Monitor utility.
   *
   * @var \Drupal\timing_monitor\TimingMonitorUtility
   */
  protected $tmUtility;

  /**
   * {@inheritdoc}
   */
  public function __construct(TimingMonitorUtility $tm_utility) {
    $this->tmUtility = $tm_utility;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static($container->get('timing_monitor.utility'));
  }

  /**
   * Retrieves the current status of the timing monitor.
   *
   * @option option-name
   *   Description
   * @usage timing_monitor:status tm-s
   *   Usage description
   * @table-style default
   * @field-labels
   *   count: Count
   *   type_count: Type Count
   *
   * @command timing_monitor:status
   * @aliases tm-s
   */
  public function status($options = ['format' => 'table']) {

    $data[] = $this->tmUtility->getTimingMonitorStatus();

    return new RowsOfFields($data);
  }

  /**
   * Retrieves the current types of the timing monitor.
   *
   * @option option-name
   *   Description
   * @usage timing_monitor:types tm-t
   *   Usage description
   * @table-style default
   * @field-labels
   *   id: ID
   *   count: Count
   *   avg: Avg
   *
   * @command timing_monitor:types
   * @aliases tm-t
   */
  public function types($options = ['format' => 'table']) {

    $data = $this->tmUtility->getTimingMonitorTypes();

    return new RowsOfFields($data);
  }

  /**
   * Retrieves a list of logs for the given type.
   *
   * @param string type
   *   The log type to list
   *
   * @option page
   *   Page for ordering
   * @option count
   *   Count of items per page
   * @option sort
   *   Sort ordering
   * @usage timing_monitor:type-list tm-tl
   *   Usage description
   * @table-style default
   * @field-labels
   *   id: ID
   *   uid: uid
   *   session_uuid: session_uuid
   *   type: type
   *   marker: marker
   *   message: message
   *   variables: variables
   *   path: path
   *   method: method
   *   timer: timer
   *   duration: duration
   *   timestamp: timestamp
   *
   * @command timing_monitor:type-list
   * @aliases tm-tl
   */
  public function typeList($type, $options = [
    'page' => 0,
    'count' => 50,
    'sort' => 'DESC',
    'format' => 'table',
  ]) {

    // Check for type param.
    if (empty($type)) {
      $types = $this->tmUtility->getTimingMonitorTypes();
      $choices = array_combine(array_keys($types), array_keys($types));

      $type = $this->io()->choice(dt("Choose a type to list"), $choices);
    }

    $data = $this->tmUtility->getTimingMonitorTypeList($type, $options['page'], $options['count'], $options['sort']);

    return new RowsOfFields($data);
  }

  /**
   * Retrieves a list of averages for a given type by day.
   *
   * @param type
   *   The log type to list
   *
   * @option start-day
   *   Optional start date
   * @option end-day
   *   Optional end date
   * @option days
   *   How many days to include
   * @usage timing_monitor:type-daily-avg tm-tda
   *   Usage description
   * @table-style default
   * @field-labels
   *   date: Date
   *   avg: Average
   *
   * @command timing_monitor:type-daily-avg
   * @aliases tm-tda
   */
  public function dailyAverage($type = "", $options = [
    'start-day' => "",
    'end-day' => "",
    'days' => 7,
    'format' =>
    'table'
  ]) {

    // Check for type param.
    if (empty($type)) {
      $types = $this->tmUtility->getTimingMonitorTypes();
      $choices = array_combine(array_keys($types), array_keys($types));

      $type = $this->io()->choice(dt("Choose a type to list"), $choices);
    }

    if ($options['start-day'] && $options['end-day']) {
      $start_day_obj = \DateTime::createFromFormat("Y-m-d", $options['start-day']);
      $end_day_obj = \DateTime::createFromFormat("Y-m-d", $options['end-day']);
    }
    elseif ($options['start-day'] && !$options['end-day']) {
      $start_day_obj = \DateTime::createFromFormat("Y-m-d", $options['start-day']);
      $end_day_obj = (clone $start_day_obj)->modify("-" . $options['days'] . " days");
    }
    elseif (!$options['start-day'] && $options['end-day']) {
      $end_day_obj = \DateTime::createFromFormat("Y-m-d", $options['end-day']);
      $start_day_obj = (clone $end_day_obj)->modify("+" . ($options['days'] - 1) . " days");
    }
    else {
      $start_day_obj = new \DateTime();
      $end_day_obj = (clone $start_day_obj)->modify("-" . $options['days'] . " days");
    }

    $start_day_obj->setTime(23, 59, 59);
    $end_day_obj->setTime(0, 0, 0);

    $dates = $this->tmUtility->getTimingMonitorDailyAverage($type, $start_day_obj, $end_day_obj, $options['days']);

    $data = [];
    foreach ($dates as $date => $average) {
      $data[] = [
        'date' => $date,
        'avg' => $average,
      ];
    }

    return new RowsOfFields($data);
  }

}
