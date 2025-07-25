<?php

namespace Drupal\custom_entity\Lister;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Movie entities.
 */
class MovieListBuilder extends EntityListBuilder {

  /**
   * {@inheritDoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['title'] = $this->t('Title');
    $header['price'] = $this->t('Price');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritDoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['id'] = $entity->id();
    $row['title'] = Link::createFromRoute($entity->label(), 'entity.movie.canonical', ['movie' => $entity->id()]);
    $row['price'] = $entity->get('price')->value . ' INR';
    return $row + parent::buildRow($entity);
  }

}
