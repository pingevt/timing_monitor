<?php

namespace Drupal\Tests\timing_monitor\Unit;

use Drupal\timing_monitor\TimingMonitor;
use Drupal\Tests\UnitTestCase;

/**
 * Unit tests for timing_monitor module.
 */
class TimingMonitorUnitTests extends UnitTestCase {

  /**
   * Before a test method is run, setUp() is invoked.
   * Create new unit object.
   */
  public function setUp() {

  }

  /**
   * Test staic vars.
   */
  public function testCheckStaticVars() {
    $this->assertTrue(TRUE);
  }

  /**
   * Test the singleton behavior of TimingMonitor::getInstance().
   */
  public function testGetInstance() {
    // Get the first instance of TimingMonitor.
    $instance1 = TimingMonitor::getInstance();

    // Get the second instance of TimingMonitor.
    $instance2 = TimingMonitor::getInstance();

    // Assert that both instances are the same.
    $this->assertSame($instance1, $instance2, 'TimingMonitor::getInstance() should return the same instance.');

    // Assert that the instance is of the correct class.
    $this->assertInstanceOf(TimingMonitor::class, $instance1, 'The instance should be of type TimingMonitor.');
  }

  /**
   * Unset the test object.
   *
   * Once test method has finished running, whether it succeeded or failed,
   * tearDown() will be invoked.
   */
  public function tearDown() {

  }

}
