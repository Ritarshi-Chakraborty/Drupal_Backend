<?php

namespace Drupal\product_cart\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\File\FileUrlGeneratorInterface;

/**
 * Exposes the product details in a JSON format.
 */
class ProductApiController extends ControllerBase {

  /**
   * The entity type manager service.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The file URL generator service.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * {@inheritDoc}
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, FileUrlGeneratorInterface $fileUrlGenerator) {
    $this->entityTypeManager = $entityTypeManager;
    $this->fileUrlGenerator = $fileUrlGenerator;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('file_url_generator')
    );
  }

  /**
   * Function which returns the product details.
   *
   * @return Symfony\Component\HttpFoundation\JsonResponse
   *   The product details as a Json Response.
   */
  public function productList() {
    $product_list = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'product')
      ->accessCheck(TRUE)
      ->execute();

    $products = $this->entityTypeManager->getStorage('node')->loadMultiple($product_list);
    $product_data = [];

    foreach ($products as $product) {
      $image_data = [];
      // $images = $product->get('field_product_images')->referencedEntities();
      foreach ($product->get('field_product_images') as $image_item) {
        $file = $image_item->entity;
        if ($file) {
          $url = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
          $alt = $image_item->alt;

          $image_data[] = [
            'url' => $url,
            'alt' => $alt,
          ];
        }
      }

      $product_data[] = [
        'title' => $product->getTitle(),
        'id' => $product->id(),
        'price' => $product->get('field_product_price')->value,
        'images' => $image_data,
        'description' => $product->get('field_product_description')->value,
      ];
    }

    return new JsonResponse($product_data, 200);
  }

}
