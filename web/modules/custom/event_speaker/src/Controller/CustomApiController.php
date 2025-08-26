<?php

namespace Drupal\event_speaker\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Returns JSON data for all 'event' content nodes.
 */
class CustomApiController extends ControllerBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */

  protected $entityTypeManager;
  /**
   * The file URL generator service.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */

  protected $fileUrlGenerator;
  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritDoc}
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, FileUrlGeneratorInterface $fileUrlGenerator, ConfigFactoryInterface $configFactory) {
    $this->entityTypeManager = $entityTypeManager;
    $this->fileUrlGenerator = $fileUrlGenerator;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('file_url_generator'),
      $container->get('config.factory')
    );
  }

  /**
   * Returns a JSON response of all 'event' nodes if the access key is valid.
   *
   * @param Symfony\Component\HttpFoundation\Request $request
   *   The Request service.
   *
   * @return Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing event data or an error message.
   */
  public function getAllNodes(Request $request) {
    // $nodes = Node::loadMultiple();
    // Get the key param from the url.
    $key = $request->query->get('key');

    // Get the key value from the config form.
    $config = $this->configFactory->get('site_api_key.settings');
    $valid_key = $config->get('api_key');

    // Validate the key.
    if ($key !== $valid_key) {
      return new JsonResponse(['error' => 'Invalid key'], 403);
    }
    // Load all nodes of type 'event'.
    $events = $this->entityTypeManager
      ->getStorage('node')
      ->loadByProperties(['type' => 'event']);
    $node_data = [];

    foreach ($events as $event) {
      $created_time = date('Y-m-d H:i:s', $event->getCreatedTime());
      $update_time = date('Y-m-d H:i:s', $event->getChangedTime());

      // Get the start date.
      $start_date = $event->get('field_start_date')->value;
      $start_date_obj = new \DateTime($start_date);
      $formatted_start_date = $start_date_obj->format('Y-m-d H:i:s');

      // Get the end date.
      $end_date = $event->get('field_end_date')->value;
      $end_date_obj = new \DateTime($end_date);
      $formatted_end_date = $end_date_obj->format('Y-m-d H:i:s');

      // Get values of the multi-valued field 'field_event_speakers'.
      $speakers = [];
      $field_speakers = $event->get('field_event_speakers')->getValue();

      foreach ($field_speakers as $speaker) {
        // Assuming it's an entity reference field, load the referenced entity.
        $speaker_entity = $this->entityTypeManager->getStorage('node')->load($speaker['target_id']);
        if ($speaker_entity) {
          $speakers[] = [
            'name' => $speaker_entity->getTitle(),
            'id' => $speaker_entity->id(),
          ];
        }
      }

      // Get the field_banner_image URL and alt text.
      $banner_image = $event->get('field_banner_image')->entity;
      $image_url = $this->fileUrlGenerator->generateAbsoluteString($banner_image->getFileUri());
      $image_alt = $event->get('field_banner_image')->alt;

      // Get the location.
      $event_location = $event->get('field_event_location')->value;

      $node_data[] = [
        'title' => $event->getTitle(),
        'nid' => $event->id(),
        'created' => $created_time,
        'dates' => [
          'start_date' => $formatted_start_date,
          'end_date' => $formatted_end_date,
        ],
        'node_url' => $event->toUrl()->toString(),
        'speakers' => $speakers,
        'node_path' => $event->toUrl()->setAbsolute()->toString(),
        'banner_image' => [
          'url' => $image_url,
          'alt' => $image_alt,
        ],
        'location' => $event_location,
        'update_time' => $update_time,
      ];
    }
    return new JsonResponse($node_data);
  }

}
