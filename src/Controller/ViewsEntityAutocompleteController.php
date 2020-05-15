<?php

namespace Drupal\toolkit\Controller;

use Drupal\system\Controller\EntityAutocompleteController;
use Drupal\Component\Utility\Tags;
use Drupal\Component\Utility\Unicode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for views autocomplete filter requests.
 */
class ViewsEntityAutocompleteController extends EntityAutocompleteController {

  /**
   * {@inheritdoc}
   */
  public function handleAutocomplete(Request $request, $target_type, $selection_handler = 'default', $selection_settings_key = NULL) {
    $matches = [];
    // Get the typed string from the URL, if it exists.
    if ($input = $request->query->get('q')) {
      $typed_string = Tags::explode($input);
      $typed_string = Unicode::strtolower(array_pop($typed_string));

      $matches = $this->matcher->getMatches($target_type, $selection_handler, ['match_operator' => 'CONTAINS'], $typed_string);
    }

    return new JsonResponse($matches);
  }

}
