<?php

namespace Drupal\timing_monitor_example\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Database\Connection;
use Drupal\timing_monitor\TimingMonitor;
use Drupal\timing_monitor\TimingMonitorUtility;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for testing Timing monitor.
 */
class ExampleController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The Timing Monitor.
   *
   * @var \Drupal\timing_monitor\TimingMonitor
   */
  protected $tm;

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
  public function __construct(ContainerInterface $container) {
    // TimingMonitorUtility $tm_utility, Connection $database) {
    $this->tm = TimingMonitor::getInstance();
    // $this->tmUtility = $container->get('timing_monitor.utility');
    // $this->database = $container->get('database');
  }

  public function page1(Request $request) {
    // Start a timing monitor.
    // $this->tmUtility->start('example_page1', 'Example Page 1', 'example');
    $this->tm->logTiming("page:1", TimingMonitor::START, "Starting...");

    // Initialize the response.
    $build = [
        '#markup' => "Timing Monitor Example Page 1 completed. Check the logs for timing details.",
    ];

    // Run an expensive function.
    sleep(1);
    $this->tm->logTiming("page:1", TimingMonitor::MARK, "...Mark...");
    \timing_monitor_example_example();

    // Run an expensive function.
    \timing_monitor_example_example();

    $this->tm->logTiming("page:1", TimingMonitor::FINISH, "...Finishing");
    return $build;
  }

}