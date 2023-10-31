<?php

namespace Drupal\timing_monitor\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Configure logging settings for this site.
 *
 * @internal
 */
class TimingMonitorSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'timing_monitor_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['timing_monitor.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('timing_monitor.settings');

    $row_limits = [5, 100, 1000, 10000, 100000, 1000000];
    $form['row_limit'] = [
      '#type' => 'select',
      '#title' => $this->t('Database log messages to keep'),
      '#default_value' => $config->get('row_limit'),
      '#options' => [0 => $this->t('All')] + array_combine($row_limits, $row_limits),
      '#description' => $this->t('The maximum number of messages to keep in the database log. Requires a <a href=":cron">cron maintenance task</a>.', [':cron' => Url::fromRoute('system.status')->toString()]),
    ];

    $form['directory'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Log File location'),
      '#default_value' => $config->get('directory') ?? "tm_logs",
      '#description' => $this->t('The location where logs are saved. Relative paths are inside the public files/ directory, but protected from web access. Should not start or end with a "/".'),
      '#required' => TRUE,
    ];

    $form['gzip'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Compress archived files with gzip (EXPERIMENTAL)'),
      '#default_value' => $config->get('gzip'),
      '#description' => "",
    ];

    $form['api'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable API'),
      '#default_value' => $config->get('api'),
      '#description' => "Enable api access to timing data",
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Check directory.
    $dir_input = $form_state->getValue('directory');

    $start_slash_pattern = "/^\//i";
    if (preg_match_all($start_slash_pattern, $dir_input)) {
      $form_state->setErrorByName('directory', "The log file location should not start with a slash.");
    }
    $end_slash_pattern = "/\/$/i";
    if (preg_match_all($end_slash_pattern, $dir_input)) {
      $form_state->setErrorByName('directory', "The log file location should not end with a slash.");
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('timing_monitor.settings')
      ->set('row_limit', $form_state->getValue('row_limit'))
      ->set('directory', $form_state->getValue('directory'))
      ->set('gzip', $form_state->getValue('gzip'))
      ->set('api', $form_state->getValue('api'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
