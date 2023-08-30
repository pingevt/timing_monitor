<?php

namespace Drupal\timing_monitor\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Batch process and archive outdated timing logs.
 *
 * @internal
 */
class ArchiveRecordsBatch extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'timing_monitor_archive_records_batch';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['count'] = [
      '#type' => 'select',
      '#title' => $this->t('How many to archive?'),
      '#options' => [
        '1' => 1,
        '10' => 10,
        '100' => 100,
        '10000' => 10000,
        '100000' => 100000,
        '500000' => 500000,
      ],
      '#default_value' => 10,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Run',
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $row_limit = \Drupal::config('timing_monitor.settings')->get('row_limit');

    $values = $form_state->getValues();

    $batch = [
      'title' => $this->t("Archive timing logs"),
      'operations' => [
        ['timing_monitor__archiving', [(int) $values['count'], $row_limit]],
      ],
      'finished' => 'timing_monitor__archiving_finished',

    ];

    batch_set($batch);
  }

}
