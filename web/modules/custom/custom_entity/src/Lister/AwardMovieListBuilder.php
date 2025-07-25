<?php

namespace Drupal\custom_entity\Lister;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Award Winning Movie entities.
 */
class AwardMovieListBuilder extends EntityListBuilder {

  /**
   * {@inheritDoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['award_name'] = $this->t('Award Name');
    $header['year'] = $this->t('Year');
    $header['movie_title'] = $this->t('Movie Title');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritDoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['id'] = $entity->id();
    $row['award_name'] = Link::createFromRoute($entity->label(), 'entity.award_movie.canonical', ['award_movie' => $entity->id()]);
    $row['year'] = $entity->get('year');
    $row['movie_title'] = $entity->get('movie_title');
    return $row + parent::buildRow($entity);
  }

}
