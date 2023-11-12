<?php

namespace Drupal\timing_monitor\EventSubscriber;

use Drupal\Core\EventSubscriber\HttpExceptionSubscriberBase;
use Drupal\timing_monitor\TimingMonitor;
use Drupal\Core\Logger\LoggerChannelTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Add in Timing Monitor.
 */
class TimingMonitorSubscriber extends HttpExceptionSubscriberBase implements EventSubscriberInterface {

  use LoggerChannelTrait;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events = parent::getSubscribedEvents();
    // $events['kernel.request'] = ['onRequest', 28];
    $events['kernel.finish_request'] = ['finishRequest'];
    return $events;
  }

  /**
   * {@inheritdoc}
   */
  protected static function getPriority() {
    return 350;
  }

  /**
   * {@inheritdoc}
   */
  protected function getHandledFormats() {
    return [];
  }

  /**
   * Redirects on 400 Bad Request kernel exceptions.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The Event to process.
   */
  public function on400(RequestEvent $event) {

    $request = $event->getRequest();
    $exception = $event->getThrowable();

    if (strpos($request->getRequestUri(), "/api/timing-monitor/") === 0 || $request->getRequestUri() == "/api/timing-monitor") {
      $data = [
        'status' => (int) $exception->getStatusCode(),
        'error_msg' => 'Bad Request',
        'full_msg' => $exception->getMessage(),
      ];
      $response = new JsonResponse($data);
      $event->setResponse($response);

      // Log this call.
      $this->getLogger('timing_monitor')->error("400: Bad Api call. " . $exception->getMessage(), [
        "request" => $request,
        "exception" => $exception,
      ]);
    }
  }

  /**
   * Redirects on 403 Access Denied kernel exceptions.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The Event to process.
   */
  public function on403(RequestEvent $event) {

    $request = $event->getRequest();
    $exception = $event->getThrowable();

    if (strpos($request->getRequestUri(), "/api/timing-monitor/") === 0 || $request->getRequestUri() == "/api/timing-monitor") {

      $data = [
        'status' => (int) $exception->getStatusCode(),
        'error_msg' => 'Access Denied',
        // 'full_msg' => $exception->getMessage(), DEBUG ONLY
      ];
      $response = new JsonResponse($data);
      $event->setResponse($response);

      // Log this call.
      $this->getLogger('timing_monitor')->error("403: Bad Api call. " . $exception->getMessage(), [
        "request" => $request,
        "exception" => $exception,
      ]);
    }
  }

  /**
   * Redirects on 404 Not Found kernel exceptions.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The Event to process.
   */
  public function on404(RequestEvent $event) {

    $request = $event->getRequest();
    $exception = $event->getThrowable();

    if (strpos($request->getRequestUri(), "/api/timing-monitor/") === 0 || $request->getRequestUri() == "/api/timing-monitor") {
      $data = [
        'status' => (int) $exception->getStatusCode(),
        'error_msg' => 'Not Found',
        'full_msg' => $exception->getMessage(),
      ];
      $response = new JsonResponse($data);
      $event->setResponse($response);

      // Log this call.
      $this->getLogger('timing_monitor')->error("403: Bad Api call. " . $exception->getMessage(), [
        "request" => $request,
        "exception" => $exception,
      ]);
    }

  }

  // phpcs:disable
  /**
   * Event callback for 'kernel.request' event.
   *
   * @param \Symfony\Component\HttpKernel\Event\KernelEvent $event
   * @return void
   */
  // public function onRequest(KernelEvent $event) {

  // }
  // phpcs:enable

  /**
   * Event callback for 'kernel.finish_request' event.
   *
   * @param \Symfony\Component\HttpKernel\Event\KernelEvent $event
   *   The Kernel event.
   */
  public function finishRequest(KernelEvent $event) {
    if (TimingMonitor::hasInstance()) {
      TimingMonitor::getInstance()->saveTimingLog();
    }
  }

}
