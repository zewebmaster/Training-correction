<?php

namespace Drupal\training_correction\Controller\TpForm;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Correction for TP Form.
 *
 * Class FormRedirectionController.
 */
class FormRedirectionController extends ControllerBase {

  /**
   * The request manager service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestManager;

  /**
   * The private tempstore factory services.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * The temp store.
   *
   * @var \Drupal\user\PrivateTempStore
   */
  protected $store;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('request_stack'),
        $container->get('tempstore.private'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory) {
    $this->tempStoreFactory = $temp_store_factory;
    $this->store = $this->tempStoreFactory->get('training_correction');
  }

  /**
   * Manage redirection with query parameters.
   *
   * TP Form Lv2.
   */
  public function formRedirect(Request $request) {

    $name = $request->query->get('name', '');
    $mail = $request->query->get('mail', '');
    $topic = $request->query->get('topic', '');

    $list = [
      $this->t("Name: @string.", ['@string' => $name]),
      $this->t("Email: @string.", ['@string' => $mail]),
      $this->t("Topic: @string.", ['@string' => $topic]),
    ];

    return [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => $list,
      '#prefix' => $this->t('Thank you for your subscription'),
    ];
  }

  /**
   * Manage redirection using a temporary storage.
   *
   * TP Form Lv2.
   */
  public function formRedirectUsingTempStore() {

    $list = [];
    $datas = $this->store->get('newsletters_subscription_form_data');
    $this->store->delete('newsletters_subscription_form_data');

    if (!\is_null($datas)) {
      $name = \array_key_exists('name', $datas) ? $datas['name'] : '';
      $mail  = \array_key_exists('mail', $datas) ? $datas['mail'] : '';
      $topic = \array_key_exists('topic', $datas) ? $datas['topic'] : '';
      $list = [
        $this->t("Name: @string.", ['@string' => $name]),
        $this->t("Email: @string.", ['@string' => $mail]),
        $this->t("Topic: @string.", ['@string' => $topic]),
      ];
    }

    return [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => $list,
    ];
  }

  /**
   * Redirection Controller.
   *
   * TP Form Lv 3.
   */
  public function redirectionNewsletter() {
    return [
      '#markup' => $this->t('Thank you for your subscription'),
    ];
  }

}
