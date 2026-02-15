<?php

namespace Drupal\drupal_htmx_examples\Hook;

use Drupal\Core\Hook\Attribute\Hook;

class ThemeHooks {

  /**
   * @return array[]
   */
  #[Hook('theme')]
  public function theme() {
    return [
      'htmx_template' => [
        'variables' => [
          'description' => '',
        ],
      ],
    ];
  }
}