<?php 

namespace Drupal\about_us_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom block to show 3 news of the anchor displayed on the about us page.
 *
 * @Block(
 *   id = "about_us_block",
 *   admin_label = @Translation("About Us Block"),
 *   category = @Translation("Custom")
 * )
 */
class AboutUsBlock extends BlockBase implements ContainerFactoryPluginInterface{

  /**
   * Stores object of EntityTypeManagerInterface.
   */
  protected $entityTypeManager;
  /**
   * Stores object of RouteMatchInterface.
   */
  protected $routeMatch;

   /**
    * Constructs a Drupalist object.
    *
    * @param array $configuration
    *   A configuration array containing information about the plugin instance.
    * @param string $plugin_id
    *   The plugin_id for the plugin instance.
    * @param mixed $plugin_definition
    *   The plugin implementation definition.
    * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
    *   Stores the instance of Entity Type Manager Interface.
    */
    public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager,) {
      $this->routeMatch = $route_match;
      $this->entityTypeManager = $entity_type_manager;
      parent::__construct($configuration, $plugin_id, $plugin_definition);
    }
  
    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
      return new static(
        $configuration,
        $plugin_id,
        $plugin_definition,
        $container->get('current_route_match'),
        $container->get('entity_type.manager')
      );
    }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Fetch the current node.
    $current_node = $this->routeMatch->getParameter('node');
    if ($current_node && $current_node instanceof NodeInterface) {
      // Getting the anchor field from the node.
      $field_value = $current_node->get('field_best_anchor_of_this_week')->getValue();
      // Getting id of the anchor.
      $anchor_id = $field_value[0]['target_id'];
      // Fetching latest 3 nodes created by the anchor.
      $query = $this->entityTypeManager->getStorage('node')->getQuery()
              ->condition('type', 'news')
              ->condition('uid', $anchor_id)
              ->sort('created', 'DESC')
              ->range(0, 3)
              ->accessCheck(TRUE);

      $nids = $query->execute();
      $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
      // Stroing the titles of the fetched nodes.
      foreach ($nodes as $node) {
        $node_titles[] = $node->getTitle();
      }
      // Using implode to convert array to string.
      $titles = implode(', ', $node_titles);
      $content = 'Latest 3 news : ' . $titles;
    }
    else {
      $content = 'No valid content found.';
    }
    return [
      '#markup' => $content,
      '#cache' => [
        'max-age' => 0
      ]
    ];
  }
}
