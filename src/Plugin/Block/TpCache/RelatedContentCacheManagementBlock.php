<?php

namespace Drupal\training_correction\Plugin\Block\TpCache;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Example of cache management with the related content block - TP Block 1.
 *
 * @Block(
 *  id = "tp_cache_example",
 *  admin_label = @Translation("TP Cache : Related contents with cache management"),
 *  category = @Translation("Training correction"),
 * )
 */
class RelatedContentCacheManagementBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The link generator service.
   *
   * @var \Drupal\Core\Utility\LinkGeneratorInterface
   */
  protected $linkGenerator;

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
      $container->get('link_generator'),
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
    LinkGeneratorInterface $link_generator,
    RouteMatchInterface $route_match
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_manager;
    $this->linkGenerator = $link_generator;
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
    if ($node->bundle() != 'article') {
      return;
    }
    if ($node->get('field_news')->isEmpty()) {
      return;
    }

    $field_new = $node->get('field_news')->first()->getValue();
    $nids = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery()
      ->condition('type', 'article', '=')
      ->condition('field_news', $field_new['target_id'], '=')
      ->condition('nid', $node->id(), '!=')
      ->sort('created')
      ->range(0, 3)
      ->execute();

    if (empty($nids)) {
      return;
    }

    $articles = $this->entityTypeManager
      ->getStorage('node')
      ->loadMultiple($nids);

    foreach ($articles as $article) {
      // Create link.
      $label = $article->getTitle();
      $url = Url::fromRoute('entity.node.canonical', ['node' => $article->id()]);
      $list[] = $this->linkGenerator->generate($label, $url);
    }

    return [
      '#theme' => 'item_list',
      '#items' => $list,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), ['node_list:article']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['url.path']);
  }

}
