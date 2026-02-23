<?php

namespace Drupal\drupal_htmx_examples\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Htmx\Htmx;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\EntityContextDefinition;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;

/**
 * Provides a block that uses HTMX to load the user login form.
 */
#[Block(
  id: "htmx_lazy_loading",
  admin_label: new TranslatableMarkup("HTMX Lazy Loading Block"),
  forms: [
    'settings_tray' => FALSE,
  ],
  context_definitions: [
    "user" => new EntityContextDefinition("entity:user"),
  ],
)]
class LazyLoadingBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The FormBuilder object.
   */
  protected FormBuilderInterface $formBuilder;

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $build = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['accommodation'],
      ],
    ];

    (new Htmx())
      ->get(Url::fromRoute('user.login'))
      ->trigger('revealed delay:500ms')
      ->select('#user-login-form')
      ->swap('outerHTML')
      ->applyTo($build);

    return $build;
  }

}
