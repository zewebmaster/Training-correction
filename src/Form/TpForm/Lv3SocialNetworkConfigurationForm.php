<?php

namespace Drupal\training_correction\Form\TpForm;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Correction for TP Form - Level 3.
 *
 * Class Lv3SocialNetworkConfigurationForm.
 */
class Lv3SocialNetworkConfigurationForm extends ConfigFormBase {

  /**
   * Configuration filename.
   *
   * @var string
   */
  const SETTINGS = 'training_correction.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lv3_social_network_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $form['social_network'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Social network'),
    ];
    $form['social_network']['facebook'] = [
      '#type' => 'url',
      '#title' => $this->t('Facebook'),
      '#default_value' => $config->get('facebook'),
    ];
    $form['social_network']['twitter'] = [
      '#type' => 'url',
      '#title' => $this->t('Twitter'),
      '#default_value' => $config->get('twitter'),
    ];
    $form['social_network']['instagram'] = [
      '#type' => 'url',
      '#title' => $this->t('Instagram'),
      '#default_value' => $config->get('instagram'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable(static::SETTINGS)
      ->set('facebook', $form_state->getValue('facebook'))
      ->set('twitter', $form_state->getValue('twitter'))
      ->set('instagram', $form_state->getValue('instagram'))
      ->save();

    $this->messenger()->addStatus($this->t('Your configuration have been saved'));
  }

}
