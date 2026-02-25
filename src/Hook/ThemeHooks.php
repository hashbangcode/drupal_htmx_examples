<?php

namespace Drupal\drupal_htmx_examples\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hooks for the theme layer.
 */
class ThemeHooks {

  /**
   * Implements hook_theme().
   *
   * @return array[]
   *   The theme options to set.
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
