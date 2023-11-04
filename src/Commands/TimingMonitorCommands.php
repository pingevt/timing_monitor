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

  }

}
