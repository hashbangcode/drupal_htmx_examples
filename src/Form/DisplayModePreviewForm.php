<?php

namespace Drupal\drupal_htmx_examples\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Htmx\Htmx;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to show a.
 */
class DisplayModePreviewForm extends FormBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity display repository service.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepository
   */
  protected $entityDisplayRepository;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container): self {
    $instance = new static($container);

    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->entityDisplayRepository = $container->get('entity_display.repository');

    return $instance;
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'display_mode_preview_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $userInput = $form_state->getUserInput();

    $nid = $form_state->getValue('node_id', $userInput['node_id'] ?? '');
    $viewMode = $form_state->getValue('view_mode', $userInput['view_mode'] ?? 'teaser');

    $form['node_id'] = [
      '#type' => 'number',
      '#title' => $this->t('Node ID'),
      '#default_value' => $nid,
    ];

    (new Htmx())
      ->post()
      ->trigger('input delay:0.5s')
      ->target('#node-preview-output')
      // Select is required to pick out the correct element from our response, which will contain the entire form.
      ->select('#node-preview-output')
      ->applyTo($form['node_id']);

    $viewModes = $this->entityDisplayRepository->getViewModes('node');

    $viewModesSelection = [];
    foreach ($viewModes as $id => $mode) {
      $viewModesSelection[$id] = $mode['label'];
    }

    $form['view_mode'] = [
      '#title' => $this->t('View modes'),
      '#type' => 'select',
      '#empty_option' => $this->t('- Select -'),
      '#options' => $viewModesSelection,
      '#default_value' => $viewMode,
    ];

    (new Htmx())
      ->post()
      ->target('#node-preview-output')
      // Select is required to pick out the correct element from our response, which will contain the entire form.
      ->select('#node-preview-output')
      ->applyTo($form['view_mode']);

    $form['output'] = [
      '#markup' => '<div id="node-preview-output"></div>',
    ];

    if ($nid === '') {
      return $form;
    }

    // node ID
    $nodeStorage = $this->entityTypeManager->getStorage('node');
    $node = $nodeStorage->load($nid);

    if (!$node) {
      $form['output'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $this->t('Node not found'),
        '#attributes' => [
          'id' => 'node-preview-output',
        ],
      ];
      return $form;
    }

    $viewBuilder = $this->entityTypeManager->getViewBuilder('node');
    $renderArray = $viewBuilder->view($node, $viewMode);

    $form['output'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'id' => 'node-preview-output',
      ],
      'children' => $renderArray,
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // Do nothing.
  }

}
