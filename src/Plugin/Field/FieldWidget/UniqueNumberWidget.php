<?php

namespace Drupal\training_correction\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Correction for TP Plugin.
 *
 * This widget provides validation to ensure that a number is unique.
 *
 * @FieldWidget(
 *   id = "unique_number",
 *   label = @Translation("Unique number"),
 *   field_types = {
 *     "integer"
 *   }
 * )
 */
class UniqueNumberWidget extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * The entityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager'),
    );
  }

  /**
   * UniqueNumberWidget constructor.
   *
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   Field definition.
   * @param array $settings
   *   Settings.
   * @param array $third_party_settings
   *   Third party settings.
   * @param \Drupal\Core\Cache\EntityTypeManagerInterface $entity_manager
   *   Entity Type Manager data.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    array $third_party_settings,
    EntityTypeManagerInterface $entity_manager
  ) {
    parent::__construct(
      $plugin_id,
      $plugin_definition,
      $field_definition,
      $settings,
      $third_party_settings
    );
    $this->entityTypeManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(
    FieldItemListInterface $items,
    $delta,
    array $element,
    array &$form,
    FormStateInterface $form_state
  ) {
    $element = +[
      '#type' => 'number',
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : '',
      '#placeholder' => $this->getSetting('placeholder'),
      '#element_validate' => [
        [$this, 'validate'],
      ],
    ];

    return ['value' => $element];
  }

  /**
   * Make sure that the pokedex id is unique.
   */
  public function validate($element, FormStateInterface $form_state) {

    $form = $form_state->getFormObject();
    $entity = $form->getEntity();
    $entity_type = $entity->getEntityTypeId();
    $field_name = self::$fieldDefinition->getName();
    $value = $element['#value'];

    // Looking for a pokemon with the same pockedex id.
    $query = $this->entityTypeManager
      ->getStorage($entity_type)
      ->getQuery()
      ->condition($field_name, $value);
    // Exclude the Pokemon entity that is being edited.
    if (!$entity->isNew()) {
      $query->condition('uuid', $entity->uuid(), '!=');
    }
    $count = $query->count()->execute();

    if ($count != 0) {
      $form_state->setErrorByName($field_name, t('This id is already registred'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = [];

    $form['placeholder'] = [
      '#type' => 'textfield',
      '#title' => t('Placeholder'),
      '#default_value' => $this->getSetting('placeholder'),
      '#description' => t('Number that will be shown inside the field until a value is entered. This hint is usually a sample value or a brief description of the expected format.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $placeholder = $this->getSetting('placeholder');
    if (!empty($placeholder)) {
      $summary[] = t('Placeholder: @placeholder', ['@placeholder' => $placeholder]);
    }
    else {
      $summary[] = t('No placeholder');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return ['placeholder' => ''] + parent::defaultSettings();
  }

}
