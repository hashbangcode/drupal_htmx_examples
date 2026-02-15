<?php

namespace Drupal\drupal_htmx_examples\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Htmx\Htmx;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to show cascading selects using HTMX.
 */
class AddAnotherForm extends FormBase {

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
    return 'htmx_add_another_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $userInput = $form_state->getUserInput();

    $count = (int) ($userInput['text_count'] ?? 1);

    if ($this->isHtmxRequest()) {
      $count++;
    }

    $form['text-wrapper'] = [
      '#type' => 'container',
    ];

  for ($i = 1; $i <= $count; $i++) {
    $form['text-wrapper']['text_' . $i] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text %i', ['%i' => $i]),
      '#default_value' => isset($userInput['text_' . $i]) ? $userInput['text_' . $i] : NULL,
      '#wrapper_attributes' => [
        'id' => 'wrapper-text_' . $i,
      ]
    ];
  }

    $form['text_count'] = [
      '#type' => 'hidden',
      '#value' => $count,
      '#attributes' => [
        'id' => [
          'text-count'
        ],
      ],
    ];

    // Add HTMX to pull this element out of the payload for the form.
    (new Htmx())
      ->swapOob('true')
      ->applyTo($form['text_count']);

    $form['add_another'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add more'),
    ];

    (new Htmx())
      ->post()
      ->target('#edit-text-wrapper')
      // Select is required to pick out the correct element from our response, which will contain the entire form.
      ->select('#wrapper-text_' . $count + 1)
      // Swap out of bounds required to swap THIS button.
      ->swapOob(TRUE)
      ->swap('beforeend')
      ->applyTo($form['add_another']);

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Submit',
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state): void {
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    if ($this->isHtmxRequest()) {
      return;
    }
    $values = $form_state->getValues();
    $textFields = [];
    foreach ($values as $id => $text) {
      if (str_contains($id, 'text_')) {
        $textFields[$id] = $text;
      }
    }
    $this->messenger()->addMessage(print_r($textFields, TRUE));
  }

}
