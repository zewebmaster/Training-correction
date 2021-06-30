<?php

namespace Drupal\training_correction\Controller\TpController;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Correction for TP Controller - Level 1.
 *
 * Class Lv1CockpitController.
 */
class Lv1CockpitController extends ControllerBase {

  /**
   * The link generator service.
   *
   * @var \Drupal\Core\Utility\LinkGeneratorInterface
   */
  protected $linkGenerator;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('link_generator'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(LinkGeneratorInterface $link_generator) {
    $this->linkGenerator = $link_generator;
  }

  /**
   * Render all the content created by current user.
   */
  public function render() {
    // Search all content created by the current user.
    // return full entities.
    $contents = $this->entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
        'uid' => $this->currentUser()->id(),
      ]);

    // Loop on entities.
    $rows = [];
    foreach ($contents as $content) {
      // Generate link.
      // Prefer using Drupal\Core\Url rather than UrlGenerator service.
      // @see https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Routing%21UrlGeneratorInterface.php/function/UrlGeneratorInterface%3A%3AgenerateFromRoute
      $url = Url::fromRoute('entity.node.edit_form', ['node' => $content->id()]);
      $label = $this->t('Modify');
      $link = $this->linkGenerator->generate($label, $url);

      // Manage created time display.
      $created = $content->getCreatedTime();
      $date = new \DateTime();
      $date->setTimestamp($created);
      $created_date = $date->format('H:i');

      // Create row.
      $row = [];
      $row[] = $content->bundle();
      $row[] = $content->getTitle();
      $row[] = $created_date;
      $row[] = $link;
      $rows[] = $row;
    }

    // Display theme table.
    $header = [
      $this->t('Content type'),
      $this->t('Title'),
      $this->t('Crteated'),
      $this->t('Edit'),
    ];

    return [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];
  }

}
