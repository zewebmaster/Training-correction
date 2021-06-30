<?php

namespace Drupal\training_correction\Form\TpService;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\training_correction\Service\TpService\ExportService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Correction for TP Service.
 *
 * Class ExportPokemonForm.
 */
class ExportPokemonForm extends FormBase {

  /**
   * The entityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The export service.
   *
   * @var \Drupal\training_correction\Service\TpService\ExportService
   */
  protected $exportService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('training_correction.export_service'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
    ExportService $export_service
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->exportService = $export_service;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'export_pokemon_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['export'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Filter criteria'),
      '#open' => TRUE,
    ];
    $form['export']['types'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Type of Pokemon'),
      '#description' => $this->t('Separate types with a comma.'),
      '#required' => TRUE,
      '#tags' => TRUE,
      '#target_type' => 'taxonomy_term',
      '#selection_settings' => [
        'target_bundles' => [
          'pokemon_type',
        ],
      ],
    ];
    $form['export']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $types = $form_state->getValue('types');
    $tids = \array_column($types, 'target_id');
    $nids = $this->entityTypeManager->getStorage('node')
      ->getQuery()
      ->condition('type', 'pokemon')
      ->condition('field_types', $tids, 'IN')
      ->execute();
    if (empty($nids)) {
      $form_state->setErrorByName('types', $this->t('There are no pokemon registred for those types'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $types = $form_state->getValue('types');
    $tids = \array_column($types, 'target_id');
    $response = $this->exportService->exportByType($tids);
    $form_state->setResponse($response);
  }

}
