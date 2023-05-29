<?php

namespace Drupal\timing_monitor\EventSubscriber;

use Drupal\timing_monitor\TimingMonitor;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Add in Timing Monitor.
 */
class TimingMonitorSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // $events['kernel.request'] = ['onRequest', 28];
    $events['kernel.finish_request'] = ['finishRequest'];
    return $events;
  }

  // phpcs:disable
  /**
   * Event callback for 'kernel.request' event.
   *
   * @param KernelEvent $event
   * @return void
   */
  // public function onRequest(KernelEvent $event) {

  // }
  // phpcs:enable

  /**
   * Event callback for 'kernel.finish_request' event.
   *
   * @param KernelEvent $event
   *   The Kernel event.
   */
  public function finishRequest(KernelEvent $event) {
    if (TimingMonitor::hasInstance()) {
      TimingMonitor::getInstance()->saveTimingLog();
    }
  }

}
