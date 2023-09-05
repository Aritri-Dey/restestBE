<?php 

namespace Drupal\about_us_block\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class to implement controller of about_us_block.
 */
class HomePageController extends ControllerBase {
/**
   * Function to return given message.
   * 
   *  @return array
   *    Returns message.
   */
  public function homePage() {
    return [
      '#type' => 'markup',
      '#markup' => t(string: 'Hello, we hope you enjoy visiting our site!!'),
    ];
  }
}
