<?php

namespace Drupal\training_correction\Form\TpForm;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Correction for TP Form - Level 2.
 *
 * Class Lv2NewsletterSubscribeForm.
 */
class Lv2NewsletterSubscribeForm extends FormBase {

  /**
   * The entityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The tempStore factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * The store.
   *
   * @var \Drupal\user\PrivateTempStore
   */
  protected $store;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('tempstore.private'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
    PrivateTempStoreFactory $temp_store_factory
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->tempStoreFactory = $temp_store_factory;
    $this->store = $this->tempStoreFactory->get('training_correction');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lv2_newsletter_subscribe_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Process current user.
    if (!$this->currentUser()->isAnonymous()) {

      $user = $this->entityTypeManager
        ->getStorage('user')
        ->load($this->currentUser()->id());

      if (!$user->get('field_newsletter')->isEmpty()) {
        $field_newsletter = $user->get('field_newsletter')->first()->getValue();
        if ($field_newsletter['value']) {
          $this->messenger()->addStatus($this->t('You are already registred'));

          return $this->redirect('entity.user.edit_form', ['user' => $this->currentUser()->id()]);
        }
      }
    }

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your name'),
      '#required' => TRUE,
    ];

    $form['mail'] = [
      '#type' => 'email',
      '#title' => $this->t('Your email address'),
      '#required' => TRUE,
    ];

    if (!$this->currentUser()->isAnonymous()) {
      $form['name']['#default_value'] = $user->getAccountName();
      $form['mail']['#default_value'] = $user->getEmail();
    }

    $form['topic'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Center of interest'),
      '#required' => TRUE,
      '#target_type' => 'taxonomy_term',
      '#selection_settings' => [
        'target_bundles' => [
          'news',
        ],
      ],
    ];

    $form['postal_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your postal code'),
      '#maxlength' => 5,
    ];

    $form['locality'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your locality'),
      '#states' => [
        'invisible' => [
          ':input[name="postal_code"]' => ['filled' => FALSE],
        ],
      ],
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $postal_code = $form_state->getValue('postal_code');
    if ($postal_code != '') {
      if (\strlen($postal_code) < 5) {
        $form_state->setErrorByName('postal_code', $this->t('Enter a valid postal code !'));
        return;
      }
      if (!\preg_match('/^[0-9]*$/', $postal_code)) {
        $form_state->setErrorByName('postal_code', $this->t('Only number are allowed !'));
        return;
      }
      $locality = $form_state->getValue('locality');
      if ($locality == '') {
        $form_state->setErrorByName('locality', $this->t('You must provide your locality'));
        return;
      }
      if (\preg_match('/\d+/', $locality)) {
        $form_state->setErrorByName('locality', $this->t('Only character are allowed !'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get all datas from form.
    $values = $form_state->getValues();
    $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($values['topic']);
    $parameters = [
      'name' => $values['name'],
      'mail' => $values['mail'],
      'topic' => $term->getName(),
    ];

    if (!$this->currentUser()->isAnonymous()) {
      $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser()->id());
      $user->set('field_newsletter', ['value' => 1]);
      $user->save();
    }

    // Redirection.
    $form_state->setRedirect('training_correction.newsletters_form_redirection', $parameters);

    // Redirection using tempStore.
    // $this->store->set('newsletters_subscription_form_data', $parameters);
    // $form_state->setRedirect('training_correction.newsletters_form_redirection_ts');

    $this->messenger()->addStatus($this->t('Your subscription have been submitted'));
  }

}
