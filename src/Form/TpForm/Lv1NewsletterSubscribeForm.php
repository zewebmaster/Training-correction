<?php

namespace Drupal\training_correction\Form\TpForm;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Correction for TP Form - Level 1.
 *
 * Class Lv1NewsletterSubscribeForm.
 */
class Lv1NewsletterSubscribeForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lv1_newsletter_subscribe_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

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
    $this->messenger()->addStatus($this->t('Your subscription have been submitted'));
  }

}
