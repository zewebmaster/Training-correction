<?php

namespace Drupal\training_correction\Plugin\Block\TpBlock;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Correction for TP Block - Level 1.
 *
 *  @Block(
 *  id = "block_tp_lv1",
 *  admin_label = @Translation("TP Block 1 : Related contents to article"),
 *  category = @Translation("Training correction"),
 * )
 */
class Lv1RelatedContentBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
  public static function create(
    ContainerInterface $container,
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
  public function __construct(
    array $configuration,
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
  public function blockForm($form, FormStateInterface $form_state) {
    // Build form.
    $form = parent::blockForm($form, $form_state);
    $form['range'] = [
      '#type' => 'number',
      '#title' => $this->t('Choose the number of articles to display'),
      '#default_value' => isset($config['range']) ? $config['range'] : 3,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['range'] = $form_state->getValue('range');
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
    if ($node->bundle() != 'article') {
      return;
    }
    if ($node->get('field_news')->isEmpty()) {
      // Process.
      return;
    }

    // Get the first news.
    $field_new = $node->get('field_news')->first()->getValue();

    // Get the bloc configuration.
    $config = $this->getConfiguration();
    $range = !empty($config['range']) ? $config['range'] : 3;

    // Get the last related articles.
    $nids = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery()
      ->condition('type', 'article', '=')
      ->condition('field_news', $field_new['target_id'], '=')
      ->condition('nid', $node->id(), '!=')
      ->sort('created')
      ->range(0, $range)
      ->execute();

    if (empty($nids)) {
      return;
    }

    // Remove current node and load articles.
    $articles = $this->entityTypeManager
      ->getStorage('node')
      ->loadMultiple($nids);

    // Manage link.
    $list = [];
    foreach ($articles as $article) {
      // Prefer using Drupal\Core\Url rather than UrlGenerator service.
      // @see https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Routing%21UrlGeneratorInterface.php/function/UrlGeneratorInterface%3A%3AgenerateFromRoute/8.4.x
      $label = $article->getTitle();
      $url = Url::fromRoute('entity.node.canonical', ['node' => $article->id()]);
      $list[] = $this->linkGenerator->generate($label, $url);
    }

    // Return an item list.
    return [
      '#theme' => 'item_list',
      '#items' => $list,
    ];
  }

}
