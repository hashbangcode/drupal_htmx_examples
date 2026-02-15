<?php

namespace Drupal\drupal_htmx_examples\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Htmx\Htmx;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to show cascading selects using HTMX.
 */
class CascadingSelectForm extends FormBase {

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container): self {
    $instance = new static($container);

    return $instance;
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'htmx_cascade_select_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $years = range(2019, 2050);
    $years = array_combine($years, $years);
    $year = $form_state->getValue('year');

    $form['year'] = [
      '#title' => $this->t('Year'),
      '#type' => 'select',
      '#empty_option' => $this->t('- Select -'),
      '#options' => $years,
      '#default_value' => $year,
    ];

    $months = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'];
    $month = $form_state->getValue('month');

    $form['month'] = [
      '#title' => $this->t('Month'),
      '#type' => 'select',
      '#options' => $months,
      '#empty_option' => $this->t('- Select -'),
      '#default_value' => $month,
      '#states' => [
        '!visible' => [
          ':input[name="year"]' => ['value' => ''],
        ],
      ],
    ];

    $days = [];
    if ($month) {
      $number = cal_days_in_month(CAL_GREGORIAN, $month, $year);
      $days = range(1, $number);
      $days = array_combine($days, $days);
    }
    $day = $form_state->getValue('day');

    $form['day'] = [
      '#title' => $this->t('Day'),
      '#type' => 'select',
      '#options' => $days,
      '#empty_option' => $this->t('- Select -'),
      '#default_value' => $day,
      '#states' => [
        '!visible' => [
          ':input[name="month"]' => ['value' => ''],
        ],
      ],
    ];

    (new Htmx())
      ->post()
      ->select('*:has(>select[name="month"])')
      ->target('*:has(>select[name="month"])')
      // We also target the edit-day ID (which is the select element) with a
      // out of bounds select to replace the day select. This catches edge
      // cases where te 29th Feb is selected and a non-leap year is selected.
      ->selectOob('#edit-day')
      ->swap('outerHTML')
      ->applyTo($form['year']);

    (new Htmx())
      ->post()
      ->select('*:has(>select[name="day"])')
      ->target('*:has(>select[name="day"])')
      ->swap('outerHTML')
      ->applyTo($form['month']);

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Submit',
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
  }

}
