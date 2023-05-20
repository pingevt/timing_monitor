<?php

namespace Drupal\timing_monitor\EventSubscriber;


use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\timing_monitor\TimingMonitor;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\views\Views;

/**
 * Add in Timing Monitor.
 */
class TimingMonitorSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['kernel.request'] = ['onRequest', 28];
    $events['kernel.finish_request'] = ['finishRequest'];
    return $events;
  }

  public function onRequest(KernelEvent $event) {
    // ksm("On Request", $event);
  }

  public function finishRequest(KernelEvent $event) {
    // ksm("Finish Request", $event);
    // ksm(TimingMonitor::hasInstance());

    if (TimingMonitor::hasInstance()) {
      TimingMonitor::getInstance()->saveTimingLog();
    }
  }
}
