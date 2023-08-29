<?php

namespace Drupal\timing_monitor\TwigExtension;

use Drupal\timing_monitor\TimingMonitor;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * A test Twig extension that adds a custom function and a custom filter.
 */
class TimingMonitorExtension extends AbstractExtension {

  /**
   * Generates a list of all Twig functions that this extension defines.
   *
   * @return array
   *   A key/value array that defines custom Twig functions. The key denotes the
   *   function name used in the tag, e.g.:
   *   @code
   *   {{ testfunc() }}
   *   @endcode
   *
   *   The value is a standard PHP callback that defines what the function does.
   */
  public function getFunctions() {
    return [
      'timingMonitorLogTiming' => new TwigFunction('timingMonitorLogTiming', [
        'Drupal\timing_monitor\TwigExtension\TimingMonitorExtension',
        'logTiming',
      ]),
    ];
  }

  /**
   * Generates a list of all Twig filters that this extension defines.
   *
   * @return array
   *   A key/value array that defines custom Twig filters. The key denotes the
   *   filter name used in the tag, e.g.:
   *   @code
   *   {{ foo|testfilter }}
   *   @endcode
   *
   *   The value is a standard PHP callback that defines what the filter does.
   */
  public function getFilters() {
    return [];
  }

  /**
   * Gets a unique identifier for this Twig extension.
   *
   * @return string
   *   A unique identifier for this Twig extension.
   */
  public function getName() {
    return 'timing_monitor.twig.extension';
  }

  /**
   * Create a timing log.
   */
  public static function logTiming(string $type, string $marker = "mark", string $msg = "", array $vars = []) {
    $tm = TimingMonitor::getInstance();

    $tm->logTiming($type, $marker, $msg, $vars);
  }

}
