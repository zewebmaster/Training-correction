<?php

namespace Drupal\training_correction\Plugin\Block\TpCache;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Correction for TP Cache.
 *
 * @Block(
 *  id = "tp_cache",
 *  admin_label = @Translation("TP Cache : Author of the pokemon"),
 *  category = @Translation("Training correction"),
 * )
 */
class PokemonAuthorBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current route interface.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_route_match'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_manager,
    RouteMatchInterface $route_match
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_manager;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    if ($this->routeMatch->getRouteName() != 'entity.node.canonical') {
      return;
    }

    $node = $this->routeMatch->getParameter('node');
    if ($node->bundle() != 'pokemon') {
      return;
    }

    $author = $node->getOwner();
    $list = [
      $author->getAccountName(),
      $author->getEmail(),
    ];

    return [
      '#theme' => 'item_list',
      '#items' => $list,
      '#cache' => [
        'tags' => [
          \sprintf('user:%u', $author->id()),
          \sprintf('node:%u', $node->id()),
        ],
        'contexts' => [
          'url.path',
        ],
      ],
    ];
  }

}
