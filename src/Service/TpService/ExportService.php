<?php

namespace Drupal\training_correction\Service\TpService;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Correction for TP Service.
 *
 * Class ExportService.
 */
class ExportService {

  /**
   * The entityTypeManager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Get all pokemon by type.
   *
   * @param array $tids
   *   The id of the term.
   *
   * @return Symfony\Component\HttpFoundation\Response
   *   The csv file.
   */
  public function exportByType(array $tids) {
    $nids = $this->entityTypeManager->getStorage('node')
      ->getQuery()
      ->condition('type', 'pokemon')
      ->condition('field_types', $tids, 'IN')
      ->execute();
    $csv = $this->getCsvData($nids);
    return $this->returnCsv($csv);
  }

  /**
   * Return data, csv formatted.
   *
   * @param array $nids
   *   Pokemon nids.
   *
   * @return string
   *   The csv-formatted data.
   */
  private function getCsvData(array $nids) {
    $handle = \fopen('php://temp', 'w+');
    $header = [
      'Name',
      'Pockedex id',
      'Types',
    ];
    \fputcsv($handle, $header);

    $pokemons = $this->entityTypeManager->getStorage('node')
      ->loadMultiple($nids);
    foreach ($pokemons as $pokemon) {
      $name = $pokemon->getTitle();
      $pokedex_id = $pokemon->get('field_id')->first()->getValue();
      $field_types = $pokemon->get('field_types')->ReferencedEntities();
      $types = '';
      foreach ($field_types as $type) {
        $types .= $type->getName() . " ";
      }
      $csv = [
        $name,
        $pokedex_id['value'],
        \trim($types),
      ];
      \fputcsv($handle, $csv);
    }
    \rewind($handle);
    $csv_data = \stream_get_contents($handle);
    \fclose($handle);

    return $csv_data;
  }

  /**
   * Return data, csv formatted.
   *
   * @param string $csv
   *   Csv data formatted.
   *
   * @return Symfony\Component\HttpFoundation\Response
   *   The csv file.
   */
  private function returnCsv(string $csv) {
    $response = new Response();
    $response->headers->set('Content-Type', 'text/csv');
    // $filename = 'export_pokemon_' . date("Y-m-d", time()) . '.csv';
    $filename = \sprintf('export_pokemon_%s.csv', date("Y-m-d", time()));
    $response->headers->set('Content-Disposition', \sprintf('attachment; filename=%s', $filename));
    $response->setContent($csv);

    return $response;
  }

}
