<?php

namespace Drupal\training_correction\Controller\TpCache;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheableJsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Correction for TP Cache - Level 3.
 *
 * Class Lv3CacheableJsonResponseController.
 */
class Lv3CacheableJsonResponseController extends ControllerBase {

  /**
   * Return a json of all pokemon.
   */
  public function list(Request $request) {

    if (!$request->query->has('types')) {
      return $this->buildResponse(['error' => 'Types parameter is required.'], 400);
    }

    if ($request->query->has('types')) {
      if ("" === $request->query->getDigits('types')) {
        return $this->buildResponse(['error' => 'Types parameter must be an integer.'], 400);
      }
    }

    // Search all pokemon.
    // return full entities.
    $pokemons = $this->entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
        'type' => 'pokemon',
        'field_types' => [$request->query->get('types')],
      ]
    );

    // Foreach pokemons.
    $items = [];
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
      $items[] = [
        'name' => $pokemon->getTitle(),
        'pokedex_id' => $id,
        'types' => $type_to_encode,
        'updated' => $pokemon->getChangedTime(),
      ];
    }

    $data = [
      'total' => \count($pokemons),
      'types' => (int) $request->query->get('types'),
      'items' => $items,
    ];

    return $this->buildResponse($data);

  }

  /**
   * Build response.
   */
  private function buildResponse(array $data, int $status = 200): CacheableJsonResponse {

    $cacheMetadata = new CacheableMetadata();
    $cacheMetadata->addCacheContexts(['url.query_args']);
    $cacheMetadata->addCacheTags(['node_list:pokemon']);

    $response = new CacheableJsonResponse($data, $status);
    $response->addCacheableDependency($cacheMetadata);

    return $response;

  }

}
