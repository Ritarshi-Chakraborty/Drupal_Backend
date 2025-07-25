<?php

namespace Drupal\custom_entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form for editing an existing movie.
 */
class MovieEditForm extends ContentEntityForm {

  /**
   * {@inheritDoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = $entity->save();

    if ($status == SAVED_NEW) {
      $this->messenger()->addMessage($this->t('New movie %title has been created.', ['%title' => $entity->label()]));
    }
    else {
      $this->messenger()->addMessage($this->t('Movie %title has been updated.', ['%title' => $entity->label()]));
    }

    $form_state->setRedirect('entity.movie.canonical', ['movie' => $entity->id()]);
  }

}
