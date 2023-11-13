<?php

namespace Drupal\Tests\timing_monitor\Functional;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

/**
 * Test Timing Monitor.
 *
 * @group timing_monitor
 */
class TimingMonitorFuncTest extends BrowserTestBase {

  /**
   * The modules to load to run the test.
   *
   * @var array
   */
  protected static $modules = [
    'node',
    'field',
    'text',
    'options',
    'devel',
    'timing_monitor',
  ];

  /**
   * Default theme.
   *
   * @var string
   */
  protected $defaultTheme = 'claro';

  /**
   * A user with administration rights.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * An authenticated user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $authenticatedUser;

  /**
   * A test menu.
   *
   * @var \Drupal\system\Entity\Menu
   */
  protected $menu;

  /**
   * {@inheritdoc}
   */
  protected function setUp() : void {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser([
      'access administration pages',
      'view timing log',
    ]);
    $this->authenticatedUser = $this->drupalCreateUser([
      'use timing log api',
    ]);

    // Set devel settings.
    $config = $this->config('devel.settings');
    $config->set('debug_logfile', DRUPAL_ROOT . '/drupal_debug.txt')->save();
    $config->set('debug_pre', FALSE)->save();
  }

  /**
   * Test Basic Functionality.
   */
  public function testAuthAdminBasicFunc() {
    $session = $this->assertSession();
    $this->assertTrue(TRUE);

    // Login as authenticated admin.
    $this->drupalLogin($this->adminUser);

    
    $this->drupalGet('/admin/config');
    $session->statusCodeEquals(200);

    // Check that settings page exists, and 200.
    $first_url = Url::fromRoute('timing_monitor.settings')->toString();
    ddm($first_url);
    dump($first_url);
    $this->drupalGet($first_url);
    $session->statusCodeEquals(200);

    // Check Page title on settings Page.
    $session->titleEquals("Timing Monitor and errors");

    // Check that settings exist.
    $session->fieldEnabled("row_limit");
    $session->fieldEnabled("directory");
    $session->fieldEnabled("gzip");
    $session->fieldEnabled("api");

    // Check that view logs page exists, and 200.
    $second_url = Url::fromRoute('timing_monitor.archive')->toString();
    $this->drupalGet($second_url);
    $session->statusCodeEquals(200);

    // Check Page title on Log Page.
    $session->titleEquals("Archive Logs");
  }

}
