<?php

namespace Drupal\drupal_htmx_examples\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Htmx\Htmx;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to show inline validation using HTMX.
 */
class InlineValidationForm extends FormBase {

  /**
   * The email validation service.
   *
   * @var \Drupal\Component\Utility\EmailValidatorInterface
   */
  protected $emailValidator;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container): self {
    $instance = new static($container);

    $instance->emailValidator = $container->get('email.validator');

    return $instance;
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'htmx_inline_validation_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $email = $form_state->getValue('email', '');

    $form['email'] = [
      '#type' => 'textfield',
      '#title' => t('Email'),
      '#default_value' => $email,
    ];

    if ($this->isHtmxRequest()) {
      // This is a HTMX request, attempt to validate the email address.
      if ($email !== '' && $this->emailValidator->isValid($email) === FALSE) {
        $message = $this->t('The email address %mail is not valid. Use the format user@example.com.', ['%mail' => $email]);
        $form['email']['#description'] = '<span class="form-item--error-message">' . $message . '</span>';
        $form['email']['#attributes']['class'][] = 'error';
      }
    }

    (new Htmx())
      ->post()
      ->target('*:has(>input[name="email"])')
      // Select is required to pick out the correct element from our response, which will contain the entire form.
      ->select('*:has(>input[name="email"])')
      ->trigger('keyup delay:1s')
      ->applyTo($form['email']);

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Submit',
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $email = $form_state->getValue('email', '');
    if ($email !== '' && $this->emailValidator->isValid($email) === FALSE) {
      $form_state->setError($form['email'], t('The email address %mail is not valid. Use the format user@example.com.', ['%mail' => $email]));
    }
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // Just report on the email submitted.
    $this->messenger()
      ->addMessage($this->t('Submitted email is %email', ['%email' => $form_state->getValue('email', '')]));
  }

}
