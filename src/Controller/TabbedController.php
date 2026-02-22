<?php

namespace Drupal\drupal_htmx_examples\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Htmx\Htmx;
use Drupal\Core\Htmx\HtmxRequestInfoTrait;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Render\MainContent\HtmxRenderer;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller to show a tabbed region on the page using HTMX.
 */
class TabbedController extends ControllerBase {

  use HtmxRequestInfoTrait;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The HTMX Renderer service.
   *
   * @var \Drupal\Core\Render\MainContent\HtmxRenderer
   */
  protected $htmxRenderer;

  /**
   * The route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  public static function create(ContainerInterface $container) {
    $instance = new self();
    $instance->requestStack = $container->get('request_stack');
    $instance->htmxRenderer = $container->get('main_content_renderer.htmx');
    $instance->currentRouteMatch = $container->get('current_route_match');
    $instance->database = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function getRequest() {
    return $this->requestStack->getCurrentRequest();
  }

  /**
   * Callback for the route drupal_htmx_examples_tabbed.
   */
  public function action() {
    $output = [];

    if ($this->isHtmxRequest()) {
      // This is a HTMX request, so we create some output and respond with a
      // full HTMX Renderer response.
      // First we find the element that triggered the request.
      $trigger = $this->getHtmxTriggerName();

      // Then map to a node by finding the n-th item in the database depending
      // on what tab was clicked on.
      $number = str_replace('page_', '', $trigger);

      $node = $this->loadNthNode($number);

      $viewBuilder = $this->entityTypeManager()->getViewBuilder('node');
      $renderArray = $viewBuilder->view($node, 'teaser');

      // Then, set up the detail div and render it.
      $output['tab_content'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'id' => 'detail',
        ],
        'children' => $renderArray,
      ];

      $output['#cache'] = [
        'contexts' => [
          'url:path',
          'url:path.query',
        ],
        'tags' => $node->getCacheTags(),
      ];

      return $this->htmxRenderer->renderResponse(
        $output,
        $this->requestStack->getCurrentRequest(),
        $this->currentRouteMatch);
    }

    $range = range(1, 5);

    $items = [];

    foreach ($range as $item) {
      $id = 'page_' . $item;
      $items[$id] = [
        '#type' => 'html_tag',
        '#tag' => 'a',
        '#value' => $this->t('Page @number', ['@number' => $item]),
        '#attributes' => [
          'name' => $id,
          'href' => '#',
        ],
      ];

      $trigger = 'click';

      (new Htmx())
        ->get()
        ->swap('outerHTML')
        ->target('#detail')
        ->trigger($trigger)
        ->applyTo($items[$id]);
    }

    $output['list_of_items'] = [
      '#theme' => 'item_list',
      '#title' => 'Links',
      '#items' => $items,
      '#type' => 'ul',
    ];

    // Load the first node in the database.
    $node = $this->loadNthNode(1);

    // Convert the node to a render array for the view mode "teaser".
    $viewBuilder = $this->entityTypeManager()->getViewBuilder('node');
    $renderArray = $viewBuilder->view($node, 'teaser');

    $output['tab_content'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => '',
      '#attributes' => [
        'id' => 'detail',
      ],
      'children' => $renderArray,
    ];

    // Set up the cache for this request.
    $output['#cache'] = [
      'contexts' => [
        'url:path',
        'url:path.query',
      ],
      'tags' => $node->getCacheTags(),
    ];

    return $output;
  }

  /**
   * Load the node in position "nth", ordered by date created descending.
   *
   * @param int $nth
   *   The position of the node to load, ordered by date created descending.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The node, or null if the node failed to load.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function loadNthNode(int $nth): ?EntityInterface {
    $query = $this->database->select('node', 'n')
      ->fields('n', ['nid']);
    $query->join('node_field_data', 'nfd', '[nfd].[nid] = [n].[nid] AND [nfd].[langcode] = [n].[langcode]');
    $query->orderBy('nfd.created', 'desc');
    $query->where('n.type = :type', [':type' => 'article']);
    $query->range($nth, 1);
    $nid = $query->execute()->fetchField();

    // Then we load the data accordingly.
    $nodeStorage = $this->entityTypeManager()->getStorage('node');
    return $nodeStorage->load($nid);
  }

}
