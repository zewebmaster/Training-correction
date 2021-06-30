<?php

namespace Drupal\training_correction\Controller\TpController;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Correction for TP Controller - Level 2.
 *
 * Class Lv2JsonResponseController.
 */
class Lv2JsonResponseController extends ControllerBase {

  /**
   * Return a json of all pokemon.
   */
  public function listAll() {

    // Search all pokemon.
    // return full entities.
    $pokemons = $this->entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
        'type' => 'pokemon',
      ]
    );

    // Foreach pokemons.
    $results = [];
    foreach ($pokemons as $pokemon) {
      // Get datas.
      // Type.
      $type_to_encode = [];

      if (!$pokemon->get('field_types')->isEmpty()) {
        $types = $pokemon->get('field_types')
          ->referencedEntities();
        foreach ($types as $type) {
          $type_to_encode[] = $type->getName();
        }
      }

      // Pokedex id.
      $id = '';
      if (!$pokemon->get('field_id')->isEmpty()) {
        $field_id = $pokemon->get('field_id')
          ->first()
          ->getValue();
        $id = $field_id['value'];
      }

      // Create row.
      $results[] = [
        'name' => $pokemon->getTitle(),
        'pokedex_id' => $id,
        'types' => $type_to_encode,
        'updated' => $pokemon->getChangedTime(),
      ];
    }

    $data = [
      'total' => \count($pokemons),
      'items' => $results,
    ];

    return new JsonResponse($data);
  }

}
