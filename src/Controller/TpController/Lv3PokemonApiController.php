<?php

namespace Drupal\training_correction\Controller\TpController;

use GuzzleHttp\Client;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Correction for TP Controller - Level 3.
 *
 * Class Lv3PokemonApiController.
 */
class Lv3PokemonApiController extends ControllerBase {

  /**
   * The http client service.
   *
   * @var GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * The serialization service.
   *
   * @var \Drupal\Component\Serialization\Json
   */
  protected $serializer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
      $container->get('serialization.json'),
    );
  }

  /**
   * {@inheritdoc}
   *
   * @param \GuzzleHttp\Client $http_client
   *   The http_client.
   * @param \Drupal\Component\Serialization\Json $serializer
   *   The serializer service.
   */
  public function __construct(Client $http_client, Json $serializer) {
    $this->httpClient = $http_client;
    $this->serializer = $serializer;
  }

  /**
   * Render a list of pokemons from PockeApi.
   */
  public function render() {
    // @see Documentation https://pokeapi.co/
    $response = $this->httpClient
      ->request('GET', 'https://pokeapi.co/api/v2/pokemon', [
        'query' => [
          'limit' => 20,
        ],
      ]);

    if ($response->getStatusCode() != 200) {
      return;
    }

    $pokemons = $this->serializer->decode($response->getBody()->getContents());
    $items = [];
    foreach ($pokemons['results'] as $pokemon) {
      $items[] = $pokemon['name'];
    }

    // Return items list.
    return [
      '#theme' => 'item_list',
      '#items' => $items,
    ];
  }

}
