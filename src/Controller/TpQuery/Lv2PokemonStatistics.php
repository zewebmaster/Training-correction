<?php

namespace Drupal\training_correction\Controller\TpQuery;

use Drupal\Core\Controller\ControllerBase;

/**
 * Correction for TP Query - Level 2.
 *
 * Class Lv2PokemonStatistics.
 */
class Lv2PokemonStatistics extends ControllerBase {

  /**
   * Render a table with differents statistics.
   */
  public function render() {

    // Manages tables.
    $rows = [];
    $header = [
      $this->t('Description of data analyzed'),
      $this->t('Total'),
      $this->t('Percent'),
    ];

    // Get distribution of users by level.
    $repartition = $this->countPokemonByGeneration();
    $groupby = 'field_generation_target_id';
    $agregate = 'nid_count';
    $repartion_items_list = $this->getItemsList($repartition, $groupby, $agregate);

    // Render items list.
    return [
      '#theme' => 'item_list',
      '#items' => $repartion_items_list,
    ];
  }

  /**
   * Return distribution of users by level.
   */
  private function countPokemonByGeneration() {
    return $this->entityTypeManager()->getStorage('node')
      ->getAggregateQuery()
      ->conditionAggregate('nid', 'COUNT', '', '!=', 'en')
      ->condition('status', 1, '=', 'en')
      ->condition('type', 'pokemon', '=', 'en')
      ->groupBy('field_generation')
      ->execute();
  }

  /**
   * Return distribution items list.
   */
  private function getItemsList($repartition, $field_target, $field_count) {
    $items = [];
    $tids = \array_column($repartition, $field_target);
    $terms = $this->entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadMultiple($tids);
    foreach ($repartition as $index => $item) {
      if (!empty($terms[$tids[$index]])) {
        $items[] = \sprintf('%s: %s', $terms[$tids[$index]]->getName(), $item[$field_count]);
      }
    }

    return $items;
  }

}
