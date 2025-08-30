<?php

namespace Drupal\product_cart\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller for handling the "Buy Now" functionality.
 */
class BuyNowController extends ControllerBase {

  /**
   * Handles the Buy Now request.
   *
   * This method retrieves the 'product' query parameter (the node ID),
   * validates it, stores it in the user's session under the 'buy_now' key,
   * and then redirects the user to a thank you page.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The incoming HTTP request object.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
   *   A RedirectResponse to the thank you page if the product ID is valid,
   *   or a render array with an error message if invalid.
   */
  public function buyNow(Request $request) {
    $nid = $request->query->get('product');

    if (!$nid || !is_numeric($nid)) {
      return [
        '#markup' => $this->t('Invalid product.'),
      ];
    }

    // Use Symfony session.
    $session = $request->getSession();
    $session->set('buy_now', (int) $nid);
    $session->save();

    // Redirect to Thank You page.
    return new RedirectResponse('/thank-you');
  }

}
