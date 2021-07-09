<?php

namespace Drupal\training_correction\Plugin\Block\TpBlock;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Correction for TP Block - Level 2.
 *
 * @Block(
 *  id = "block_tp_lv3",
 *  admin_label = @Translation("TP Block 3 : Social networks"),
 *  category = @Translation("Training correction"),
 * )
 */
class Lv3SocialNetworkBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The link generator service.
   *
   * @var \Drupal\Core\Utility\LinkGeneratorInterface
   */
  protected $linkGenerator;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $config;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

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
      $container->get('config.factory'),
      $container->get('link_generator'),
      $container->get('renderer'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactory $config,
    LinkGeneratorInterface $link_generator,
    RendererInterface $renderer
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->config = $config;
    $this->linkGenerator = $link_generator;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $social_network_config = $this->config->get('training_correction.settings');
    $items = [];
    foreach ($social_network_config->getRawData() as $label => $link) {
      // Create link.
      $url = Url::fromUri($link);
      $items[] = $this->linkGenerator->generate($label, $url);
    }

    // Build render array.
    $render_array = [
      '#theme' => 'item_list',
      '#items' => $items,
    ];

    // You can simply return the render array : return $render_array;.
    // You can use the 'renderer' service to render the items_list.
    // Use a personal theme to render the block.
    $items_list = $this->renderer->render($render_array);
    return [
      '#theme' => 'block_socialnetwork',
      '#title' => $this->t('My social network links'),
      '#items_list' => $items_list,
    ];
  }

}
