CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Installation
 * Configuration
 * FAQ
 * Maintainers
 * Changelog


INTRODUCTION
------------

This module handles custom functionality for Timing monitor.
Current Functionality:

 * Creates a singlton class to track timing to monitor large processes.


INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

Configuration can be found at: /admin/config/development/timing-monitor

USAGE
-----

```php
$tm = TimingMonitor::getInstance();

$tm->logTiming("preprocess_node:$bundle:" . $variables['view_mode'], TimingMonitor::START, "Starting...");
$tm->logTiming("preprocess_node:$bundle:" . $variables['view_mode'], TimingMonitor::MARK, "...Mark...");
$tm->logTiming("preprocess_node:$bundle:" . $variables['view_mode'], TimingMonitor::FINISH, "...Finishing");

``````

MAINTAINERS
-----------

Current maintainers:

 * Pete Inge (pingevt) - https://www.drupal.org/user/411339

This project has been sponsored by:

 * Bluecadet - https://www.bluecadet.com/


CHANGELOG
---------

# Unreleased

 -
