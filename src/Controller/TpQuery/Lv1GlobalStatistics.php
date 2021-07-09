<?php

namespace Drupal\training_correction\Controller\TpQuery;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Driver\mysql\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Correction for TP query - Level 1.
 *
 * Class Lv1GlobalStatistics.
 */
class Lv1GlobalStatistics extends ControllerBase {

  /**
   * The database connection service.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * Render a table with differents statistics.
   */
  public function render() {

    // Manages tables.
    $rows = [];
    $header = [
      $this->t('Description of data analyzed'),
      $this->t('Total'),
      $this->t('Percent'),
    ];

    // Process users.
    $rows[] = [
      $label = $this->t('Users'),
      $count_users = $this->countUsers(),
      $ratio = $this->getPercent($count_users, $count_users),
    ];

    // Process newsletter subscriptions.
    $rows[] = [
      $label = $this->t('Subscribers for newsletter'),
      $count_newsletter_subscription = $this->countNewsletterSubscriptions(),
      $ratio = $this->getPercent($count_newsletter_subscription, $count_users),
    ];

    // Process articles.
    $rows[] = [
      $label = $this->t('Articles'),
      $count_articles = $this->countArticles(),
      $ratio = $this->getPercent($count_articles, $count_articles),
    ];

    // Process new articles.
    $rows[] = [
      $label = $this->t('New articles'),
      $count_new_articles = $this->countNewArticles(),
      $ratio = $this->getPercent($count_new_articles, $count_articles),
    ];

    // Process authors.
    $rows[] = [
      $label = $this->t('Authors'),
      $count_authors = $this->countAuthors(),
      $ratio = $this->getPercent($count_authors, $count_users),
    ];

    // Render table.
    return [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];
  }

  /**
   * Return count users.
   */
  private function countUsers() {

    // Counts users with database api dynamic query.
    $count_db = $this->database->select('users', 'u')
      ->countQuery()
      ->execute()
      ->fetchField();

    // Counts users with entity field query.
    $count_eq = $this->entityTypeManager()
      ->getStorage('user')
      ->getQuery()
      ->count()
      ->execute();

    return $count_eq;
  }

  /**
   * Return count of subscribers for newsletter.
   */
  private function countNewsletterSubscriptions() {
    return $this->entityTypeManager()
      ->getStorage('user')
      ->getQuery()
      ->condition('field_newsletter', 1)
      ->count()
      ->execute();
  }

  /**
   * Return count articles.
   */
  private function countArticles() {

    // Counts articles with database api dynamic query.
    $count_db = $this->database->select('node', 'n')
      ->condition('n.type', 'article', '=')
      ->condition('n.langcode', 'en', '=')
      ->countQuery()
      ->execute()
      ->fetchField();

    // Counts articles with entity field query.
    $count_eq = $this->entityTypeManager()
      ->getStorage('node')
      ->getQuery()
      ->condition('type', 'article', '=', 'en')
      ->count()
      ->execute();

    return $count_db;
  }

  /**
   * Return count new articles.
   */
  private function countNewArticles() {
    // It's a possibility !
    $month = [
      \strtotime(\date("Y-m-d", \time()) . 'first day of this month'),
      \strtotime(\date("Y-m-d", \time()) . 'last day of this month'),
    ];
    // Another one.
    $first_day_of_month = \strtotime(\date("Y-m-d", \time()) . 'first day of this month');

    return $this->entityTypeManager()
      ->getStorage('node')
      ->getQuery()
      ->condition('type', 'article', '=', 'en')
      ->condition('created', $month, 'BETWEEN')
      // ->condition('created', $first_day_of_month, '>')
      ->count()
      ->execute();
  }

  /**
   * Return count distinct authors.
   */
  private function countAuthors() {
    $query = $this->database->select('node_field_data', 'nfd')
      ->fields('nfd', ['uid'])
      ->condition('nfd.type', 'article', '=')
      ->condition('nfd.langcode', 'en', '=');

    return $query->distinct()
      ->countQuery()
      ->execute()
      ->fetchField();
  }

  /**
   * Return formatted string for percentage number.
   */
  private function getPercent($number, $total) {
    return \number_format((float) ($number / $total * 100), 2, '.', '') . '%';
  }

}
