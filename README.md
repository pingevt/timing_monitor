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

API Documentation
-----------------

## Endpoint: GET `/api/timing-monitor/status`

### Example Response

```json
{
  "status": "OK",
  "data": {
    "count": "261",
    "type_count": "4"
  }
}
```

## Endpoint: GET `/api/timing-monitor/types`

### Example Response

```json
{
  "status": "OK",
  "data": {
    "preprocess_node:article:full": {
      "id": "preprocess_node:article:full",
      "count": "60"
    },
    "preprocess_node:article:teaser": {
      "id": "preprocess_node:article:teaser",
      "count": "141"
    },
    "preprocess_node:page:full": {
      "id": "preprocess_node:page:full",
      "count": "12"
    },
    "timing_monitor": {
      "id": "timing_monitor",
      "count": "48"
    }
  }
}
```

## Endpoint: GET `/api/timing-monitor/{type}/list`

### Path Arguments

| arg | description | options |
| --- | ----------- | ------- |
| type | string - The type string from the monitor | You can use % in the url (%25 as url encoded) as a wildcard.

### Query Arguments

| arg         | description | options |
| ----------- | ----------- | ------- |
| limit       |  | to be implemented |
| start   |  | to be implemented |
| end     |  | to be implemented |

### Example Response

```json
{
  "status": "OK",
  "data": [
    {
      "id": "260",
      "uid": "1",
      "session_uuid": "c76cda75-6341-4d81-9396-29b65704ae62",
      "type": "preprocess_node:article:full",
      "marker": "finish",
      "message": "...Finishing",
      "variables": "a:0:{}",
      "path": "/sketches-notes/checking-field-content-twig-file",
      "method": "GET",
      "timer": "0.0022380352020264",
      "duration": "0.0021839141845703",
      "timestamp": "1698894252"
    },
    {
      "id": "259",
      "uid": "1",
      "session_uuid": "c76cda75-6341-4d81-9396-29b65704ae62",
      "type": "preprocess_node:article:full",
      "marker": "mark",
      "message": "...Mark...",
      "variables": "a:0:{}",
      "path": "/sketches-notes/checking-field-content-twig-file",
      "method": "GET",
      "timer": "0.0001380443572998",
      "duration": "8.392333984375E-5",
      "timestamp": "1698894252"
    }
  ]
}
```

## Endpoint: GET `/api/timing-monitor/{type}/list`

### Path Arguments

| arg | description | options |
| --- | ----------- | ------- |
| type | string - The type string from the monitor | You can use % in the url (%25 as url encoded) as a wildcard.

### Query Arguments

| arg         | description | options |
| ----------- | ----------- | ------- |
| start       |   | to be implemented |
| end         |   | to be implemented |
| days        |   | to be implemented |

### Example Response

```json
{
  "status": "OK",
  "data": {
    "type": "preprocess_node:article:full",
    "dates": {
      "2023-11-01": 0.0020719766616821,
      "2023-10-31": 0.0016613245010376,
      "2023-10-30": null,
      "2023-10-29": null,
      "2023-10-28": null,
      "2023-10-27": null,
      "2023-10-26": null
    }
  }
}
```

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
