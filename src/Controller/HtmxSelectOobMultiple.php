<?php

namespace Drupal\drupal_htmx_examples\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Htmx\HtmxRequestInfoTrait;
use Symfony\Component\HttpFoundation\RequestStack;

class HtmxSelectOobMultiple extends ControllerBase {

  use HtmxRequestInfoTrait;

  public function __construct(
    protected RequestStack $requestStack,
    protected Connection $database
  ) {}

  /**
   * {@inheritdoc}
   */
  protected function getRequest() {
    return $this->requestStack->getCurrentRequest();
  }

  public function action() {
    $output = [];

    $output['content'] = [
      '#theme' => 'htmx_template',
      '#description' => $this->t('A description.'),
    ];

    return $output;
  }

  public function htmx() {
    $output = [];

    for ($i = 1; $i <= 6; $i++) {
      // Load the most recent nodes from the database, one chunk at a time.
      $query = $this->database->select('node', 'n')
        ->fields('n', ['nid']);
      $query->join('node_field_data', 'nfd', '[nfd].[nid] = [n].[nid] AND [nfd].[langcode] = [n].[langcode]');
      $query->orderBy('nfd.created', 'desc');
      $query->where('n.type = :type', [':type' => 'article']);
      $query->range($i, 1);

      $nid = $query->execute()->fetchField();

      $nodeStorage = $this->entityTypeManager()->getStorage('node');
      $node = $nodeStorage->load($nid);

      $viewBuilder = $this->entityTypeManager()->getViewBuilder('node');
      $renderArray = $viewBuilder->view($node, 'teaser');

      $output['div' . $i] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'id' => 'div' . $i,
          'class' => 'content-div',
        ],
        'children' => $renderArray,
      ];
    }

    return $output;
  }

}
