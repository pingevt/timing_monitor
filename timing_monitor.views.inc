<?php

/**
 * @file
 * Provide views data for timing_monitor.module.
 */

/**
 * Implements hook_views_data().
 */
function timing_monitor_views_data() {
  $data = [];

  $data['timing_monitor_log']['table']['group'] = t('Timing Log');
  $data['timing_monitor_log']['table']['provider'] = 'timing_monitor';

  $data['timing_monitor_log']['table']['base'] = [
    'field' => 'id',
    'title' => t('Timing Log entries'),
    'help' => t('Contains a list of timing log entries.'),
  ];

  $data['timing_monitor_log']['id'] = [
    'title' => t('ID'),
    'help' => t('Unique timing_monitor_log event ID.'),
    'field' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  $data['timing_monitor_log']['uid'] = [
    'title' => t('UID'),
    'help' => t('The user ID of the user on which the log entry was written.'),
    'field' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'relationship' => [
      'title' => t('User'),
      'help' => t('The user on which the log entry as written.'),
      'base' => 'users_field_data',
      'base field' => 'uid',
      'id' => 'standard',
    ],
  ];

  $data['timing_monitor_log']['session_uuid'] = [
    'title' => t('UUID'),
    'help' => t('The UUID of the session.'),
    'field' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'argument' => [
      'id' => 'string',
    ],
  ];

  $data['timing_monitor_log']['type'] = [
    'title' => t('Type'),
    'help' => t('The type of the log entry, for example "user" or "page not found".'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'in_operator',
      'options callback' => 'timing_monitor_get_type',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  $data['timing_monitor_log']['marker'] = [
    'title' => t('Marker'),
    'help' => t('The type of marker.'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'in_operator',
      'options callback' => 'timing_monitor_get_marker',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  $data['timing_monitor_log']['message'] = [
    'title' => t('Message'),
    'help' => t('The actual message of the log entry.'),
    'field' => [
      'id' => 'dblog_message',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  $data['timing_monitor_log']['variables'] = [
    'title' => t('Variables'),
    'help' => t('The variables of the log entry in a serialized format.'),
    'field' => [
      'id' => 'serialized',
      'click sortable' => FALSE,
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  $data['timing_monitor_log']['path'] = [
    'title' => t('Path'),
    'help' => t('API path'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  $data['timing_monitor_log']['method'] = [
    'title' => t('Method'),
    'help' => t('Method used for the call.'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  $data['timing_monitor_log']['timer'] = [
    'title' => t('Timer'),
    'help' => t('The amount of seconds.'),
    'field' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
  ];

  $data['timing_monitor_log']['duration'] = [
    'title' => t('Duration'),
    'help' => t('The amount of seconds.'),
    'field' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
  ];

  $data['timing_monitor_log']['timestamp'] = [
    'title' => t('Timestamp'),
    'help' => t('Date when the event occurred.'),
    'field' => [
      'id' => 'date',
    ],
    'argument' => [
      'id' => 'date',
    ],
    'filter' => [
      'id' => 'date',
    ],
    'sort' => [
      'id' => 'date',
    ],
  ];

  return $data;
}
