<?php

namespace Drupal\drupal_htmx_examples\Controller;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\PagerSelectExtender;
use Drupal\Core\Htmx\Htmx;
use Drupal\Core\Htmx\HtmxRequestInfoTrait;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class to show infinite scrolling with nodes.
 */
class HtmxAddMoreNodesController extends ControllerBase {

  use HtmxRequestInfoTrait;

  public function __construct(
    protected RequestStack $requestStack,
    protected Connection $database,
  ) {}

  /**
   * {@inheritdoc}
   */
  protected function getRequest() {
    return $this->requestStack->getCurrentRequest();
  }

  /**
   * Callback for the route drupal_htmx_examples_infinite_scroll_nodes.
   *
   * @return array
   *   The render array.
   */
  public function action() {
    if ($this->isHtmxRequest()) {
      // If this is a HTMX request, so grab the page variable from the query.
      $page = $this->getRequest()->query->get('page');
    }
    else {
      // Default to the first page.
      $page = 0;
    }

    // The page limit variable is the number of items to show per page.
    $pageLimit = 2;

    // Load the node storage.
    $nodeStorage = $this->entityTypeManager()->getStorage('node');

    // Include the node_list and node_view cache tags for this list.
    $cacheTags = ['node_list', 'node_view'];

    // Set up our render array.
    $output = [];

    // Query the database using a PagerSelectExtender query. This type of pager
    // will automatically look for the query string "page" being passed to the
    // response and will use this as the current pager for the query.
    $query = $this->database->select('node', 'n')
      ->fields('n', ['nid']);
    $query->join('node_field_data', 'nfd', '[nfd].[nid] = [n].[nid] AND [nfd].[langcode] = [n].[langcode]');
    $query->orderBy('nfd.created', 'desc');
    $query->where('n.type = :type', [':type' => 'article']);

    // Add the pager to the query.
    $query = $query->extend(PagerSelectExtender::class);
    $query->limit($pageLimit);

    // Set the count query and execute.
    $query->setCountQuery($query->countQuery());
    $queryResult = $query->execute();

    $results = $queryResult->fetchAll();
    $totalItems = $query->getCountQuery()->execute()->fetchField();

    foreach ($results as $id => $result) {
      // Load the current node.
      $node = $nodeStorage->load($result->nid);

      // Render the node using the teaser view mode.
      $entityType = 'node';
      $viewMode = 'teaser';
      $viewBuilder = $this->entityTypeManager()->getViewBuilder($entityType);
      $output['node-' . $node->id()] = $viewBuilder->view($node, $viewMode);

      // Merge this node's cache tags with the list for this page.
      $cacheTags = Cache::mergeTags($cacheTags, $node->getCacheTags());

      if ($id == $pageLimit - 1 && $page * $pageLimit < $totalItems) {
        // This is the last item in the list (but not the last item overall)
        // so we create a link that will act as our load more element.
        $output['add_more_nodes'] = [
          '#type' => 'link',
          '#url' => Url::fromRoute('<current>'),
          '#title' => 'Load more...',
        ];

        // Apply HTMX attributes to the link.
        $htmx = new Htmx();
        $htmx->get(Url::fromRoute(route_name: 'drupal_htmx_examples_add_more_nodes', options: [
          'query' => [
            'page' => ++$page,
            '_wrapper_format' => 'drupal_htmx',
          ],
        ]))
          // Setting the swap value to outerHTML means that we replace the link
          // with the result of the HTMX request.
          ->swap('outerHTML')
          ->trigger('click once')
          ->applyTo($output['add_more_nodes']);
      }
    }

    // Set up the cache for this request.
    $output['#cache'] = [
      'contexts' => [
        'url:path',
        'url:path.query',
      ],
      'tags' => $cacheTags,
    ];

    return $output;
  }

}
