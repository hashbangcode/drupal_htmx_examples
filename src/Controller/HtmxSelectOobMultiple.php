<?php

namespace Drupal\drupal_htmx_examples\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Htmx\Htmx;
use Drupal\Core\Htmx\HtmxRequestInfoTrait;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RequestStack;

class HtmxSelectOobMultiple extends ControllerBase {

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

    $output['content'] = [
      '#theme' => 'htmx_template',
      '#description' => $this->t('A description.'),
    ];

    return $output;
  }

  public function htmx() {
    $output = [];

    $output['div1'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => 'div1',
      '#attributes' => [
        'id' => 'div1',
      ]
    ];

    $output['div2'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => 'div2',
      '#attributes' => [
        'id' => 'div2',
      ]
    ];

    $output['div3'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => 'div3',
      '#attributes' => [
        'id' => 'div3',
      ]
    ];

    $output['div4'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => 'div4',
      '#attributes' => [
        'id' => 'div4',
      ]
    ];

    $output['div5'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => 'div5',
      '#attributes' => [
        'id' => 'div5',
      ]
    ];

    $output['div6'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => 'div6',
      '#attributes' => [
        'id' => 'div6',
      ]
    ];

    return $output;
  }

}
