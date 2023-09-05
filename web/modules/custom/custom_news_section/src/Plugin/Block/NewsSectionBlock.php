<?php 

namespace Drupal\custom_news_section\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom block to show on all anchor pages.
 *
 * @Block(
 *   id = "custom_news_section_block",
 *   admin_label = @Translation("Custom News Section Block"),
 *   category = @Translation("Custom")
 * )
 */
class NewsSectionBlock extends BlockBase implements ContainerFactoryPluginInterface{

  /**
   * Stores object of EntityTypeManagerInterface.
   */
  protected $entityTypeManager;
  /**
   * Stores object of PathCurrent.
   */
  protected $pathCurrent;
  /**
   * Stores object of RequestStack.
   */
  protected $request;

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
    public function __construct(array $configuration, $plugin_id, $plugin_definition, RequestStack $request, CurrentPathStack $path_current, EntityTypeManagerInterface $entity_type_manager,) {
      $this->request = $request;
      $this->entityTypeManager = $entity_type_manager;
      $this->pathCurrent = $path_current;
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
        $container->get('request_stack'),
        $container->get('path.current'),
        $container->get('entity_type.manager')
      );
    }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Getting username of the current display page.
    $current_path = $this->pathCurrent->getPath();
    $path_args = explode('/', $current_path);
    $account_name = end($path_args);
    if (count($path_args) > 1) {
      $account_name = $path_args[count($path_args) - 1];
    }
    $request = \Drupal::request();
    $path_info = $request->getPathInfo();
    $path_args = explode('/', $path_info);
    // The account-name is the last element in the path.
    $account_name = end($path_args);
    $query = $this->entityTypeManager->getStorage('user')->getQuery()
              ->condition('name', $account_name)
              ->accessCheck(TRUE);
    $uids = $query->execute();
    // Getting the first element of the array.
    $uid = reset($uids);
    // Loading the user entity by id.
    $user = $this->entityTypeManager->getStorage('user')->load($uid);
    // Fetching the genre field of the user.
    $genre_value = $user->get('field_genre')->getValue();
    foreach ($genre_value as $value) {
      foreach ($value as $genre) {
        $genre_id = $genre;
      }
    }
    $term = Term::load($genre_id);
    // Fetching all users who has the same genre field value as the current 
    // displayed user.
    $query = $this->entityTypeManager->getStorage('user')->getQuery()
              ->condition('field_genre', $term->id())
              ->accessCheck(TRUE);
    $user_ids = $query->execute();
    // Fethcing the users by their ids.
    $users = $this->entityTypeManager->getStorage('user')->loadMultiple($user_ids);
    // Fetching ids of the above fetched users.
    foreach ($users as $user) {
      $uids[] = $user->id();
    }
    // Fetching the first 5 nodes created by the first user.
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
            ->condition('type', 'news')
            ->condition('uid', $uid[0])
            ->range(0,5)
            ->accessCheck(TRUE);
    $nids = $query->execute();
    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
    // Fetching the titles of the nodes.
    foreach ($nodes as $node) {
        $node_titles[] = $node->getTitle();
    }
    $titles = implode(', ', $node_titles);
    // Displaying the titles of the nodes in the block.
    return [
      '#markup' => $titles,
      '#cache' => [
        'max-age' => 0
      ]
    ];
  }
}
