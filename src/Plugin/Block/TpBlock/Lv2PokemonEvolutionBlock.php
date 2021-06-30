<?php

namespace Drupal\training_correction\Plugin\Block\TpBlock;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Correction for TP Block - Level 2.
 *
 * @Block(
 *  id = "block_tp_lv2",
 *  admin_label = @Translation("TP Block 2 : Pokemon Evolutions"),
 *  category = @Translation("Training correction"),
 * )
 */
class Lv2PokemonEvolutionBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
    // Get the route parameters.
    if ($this->routeMatch->getRouteName() != 'entity.node.canonical') {
      return;
    }
    // Get the node.
    $node = $this->routeMatch->getParameter('node');
    if ($node->bundle() != 'pokemon') {
      return;
    }
    if ($node->get('field_evolutions')->isEmpty()) {
      return [
        '#markup' => $this->t('This pokemon has no evolution'),
      ];
    }

    // Get the first news.
    $render = [];
    $evolutions = $node->get('field_evolutions')->referencedEntities();
    $render = $this->entityTypeManager->getViewBuilder('node')->viewMultiple($evolutions, 'teaser');

    return $render;
  }

}
