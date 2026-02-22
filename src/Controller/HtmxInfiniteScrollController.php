<?php

namespace Drupal\drupal_htmx_examples\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Htmx\Htmx;
use Drupal\Core\Htmx\HtmxRequestInfoTrait;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Render\MainContent\HtmxRenderer;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RequestStack;

class HtmxInfiniteScrollController extends ControllerBase {

  use HtmxRequestInfoTrait;

  public function __construct(protected RequestStack $requestStack) {}

  /**
   * {@inheritdoc}
   */
  protected function getRequest() {
    return $this->requestStack->getCurrentRequest();
  }

  public function action() {
    $output = [];

    if ($this->isHtmxRequest()) {
      $page = $this->getRequest()->query->get('page');
      $startCount = $page * 10 + 1;
      $nextPage = $page + 1;
    }
    else {
      $startCount = 1;
      $nextPage = 1;
    }

    for ($i = $startCount; $i <= $startCount + 9; $i++) {
      if ($i == $startCount + 9) {
        $output['paragraph_' . $i] = [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => $i,
        ];
        $htmx = new Htmx();
        $htmx->get(Url::fromRoute(route_name: 'drupal_htmx_examples_infinite_scroll', options: [
          'query' => [
            'page' => $nextPage,
            '_wrapper_format' => 'drupal_htmx',
          ],
        ]))
          ->swap('afterend')
          ->trigger('revealed once');
        $htmx->applyTo($output['paragraph_' . $i]);
      }
      else {
        $output['paragraph_' . $i] = [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => $i,
        ];
      }
    }

    // Set up the cache for this request.
    $output['#cache'] = [
      'contexts' => [
        'url:path',
        'url:path.query',
      ],
    ];

    return $output;
  }

}
