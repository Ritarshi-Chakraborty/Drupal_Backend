<?php

namespace Drupal\custom_entity\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\custom_entity\Entity\Movie;
use Drupal\custom_entity\Entity\AwardWinningMovie;
use Drupal\Core\Render\Markup;

/**
 * Returns the canonical page for a Movie entity.
 *
 * @param Drupal\custom_entity\Entity\Movie $movie
 *   Takes the custom content entity as a parameter.
 *
 * @return string
 *   Returns the label of the movies.
 */
class MovieController extends ControllerBase {

  /**
   * Returns the page title for a Movie entity.
   */
  public function movieDetails(Movie $movie) {
    return $movie->label();
  }

  /**
   * Returns the canonical page for a Award Winning Movie entity.
   *
   * @param Drupal\custom_entity\Entity\AwardWinningMovie $award_movie
   *   Takes the custom config entity as a parameter.
   *
   * @return array
   *   Returns the details of the movies.
   */
  public function awardMovieDetails(AwardWinningMovie $award_movie) {
    // Get field values.
    $title = $award_movie->label();
    $year = $award_movie->get('year');
    $movie_title = $award_movie->get('movie_title');

    // Build a render array.
    return [
      '#title' => $title,
      '#markup' => Markup::create(
        "<div style='font-size: 2.5rem;'>
          <p>Year: <i>$year</i></p>
          <p>Movie Title: <i>$movie_title</i></p>
        </div>"
      ),
    ];
  }

}
