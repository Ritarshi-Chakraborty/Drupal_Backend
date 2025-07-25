<?php

namespace Drupal\custom_entity\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Uuid\UuidInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for creating and editing AwardWinningMovie entities.
 */
class AwardMovieForm extends EntityForm {

  /**
   * The UUID service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuid;

  /**
   * Constructs a new AwardMovieForm.
   *
   * @param \Drupal\Component\Uuid\UuidInterface $uuid
   *   The UUID service.
   */
  public function __construct(UuidInterface $uuid) {
    $this->uuid = $uuid;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('uuid')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $award_movie = $this->entity;

    $form['award_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Award Name'),
      '#default_value' => $award_movie->get('award_name') ?? '',
      '#required' => TRUE,
    ];

    $form['movie_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Movie Title'),
      '#default_value' => $award_movie->get('movie_title') ?? '',
      '#required' => TRUE,
    ];

    $form['year'] = [
      '#type' => 'number',
      '#title' => $this->t('Year'),
      '#default_value' => $award_movie->get('year') ?? '',
      '#required' => TRUE,
      '#min' => 1960,
      '#max' => date('Y') + 10,
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $award_movie = $this->entity;
    $award_movie->set('award_name', $form_state->getValue('award_name'));
    $award_movie->set('movie_title', $form_state->getValue('movie_title'));
    $award_movie->set('year', $form_state->getValue('year'));

    // Set UUID as Id if new movie is getting created.
    if ($award_movie->isNew()) {
      $award_movie->set('id', $this->uuid->generate());
    }

    $status = $award_movie->save();
    if ($status == SAVED_NEW) {
      $this->messenger()->addMessage($this->t('Award winning movie %label has been created.', ['%label' => $award_movie->label()]));
    }
    else {
      $this->messenger()->addMessage($this->t('Award winning movie %label has been updated.', ['%label' => $award_movie->label()]));
    }

    $form_state->setRedirect('entity.award_movie.collection');
  }

}
