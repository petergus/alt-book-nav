<?php 
namespace Drupal\alt_book_nav\Plugin\Block;
use Drupal\book\Plugin\Block\BookNavigationBlock;

  /**
   * Provides a 'Book navigation' block.
   *
   * @Block(
   *   id = "custom_book_navigation",
   *   admin_label = @Translation("Book navigation - Customized"),
   *   category = @Translation("Menus")
   * )
   */
class CustomBookNavigationBlock extends BookNavigationBlock {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $current_bid = 0;

    $node = $this->routeMatch->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface && !empty($node->book['bid'])) {
      $nid = $node->id();
      $current_bid = $node->book['bid'];
    }
    elseif (isset($this->configuration['articlenid'])) {
      $nid = $this->configuration['articlenid'];
      $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
      $current_bid = empty($node->book['bid']) ? 0 : $node->book['bid'];
    }

    if ($this->configuration['block_mode'] == 'all pages') {
      return parent::build();
    }
    elseif ($current_bid) {
      // Only display this block when the user is browsing a book and do
      // not show unpublished books.
      $nid = \Drupal::entityQuery('node')
        ->condition('nid', $node->book['bid'], '=')
        ->condition('status', 1)
        ->execute();

      // Only show the block if the user has view access for the top-level node.
      if ($nid) {
        // We want to show the whole tree, by just removing '$node->book'
        // $tree = $this->bookManager->bookTreeAllData($node->book['bid'], $node->book);
        $trees = $this->bookManager->bookTreeAllData($node->book['bid']);
        $active_link = $node->book;

        $active_trail = [];
        for ($i = 1; $i < 9; $i++) {
          if (!empty($active_link["p$i"])) {
            $active_trail[] = $active_link["p$i"];
          }
        }

        function checkActiveTrail(&$link, $active_trail) {
          if (in_array($link['nid'], $active_trail)) {
            $link['in_active_trail'] = true;
          }
        }

        function recursiveCheckLinks(&$tree, $active_trail) {
          checkActiveTrail($tree['link'], $active_trail);

          foreach($tree['below'] as &$subtree) {
            if(count($subtree['below']) > 0) {
              recursiveCheckLinks($subtree, $active_trail);
            } else {
              checkActiveTrail($subtree['link'], $active_trail);
            }
          }
        }

        foreach($trees as &$tree) {
          recursiveCheckLinks($tree, $active_trail);
        }

        return $this->bookManager->bookTreeOutput($trees);

      }
    }
    return array();
  }

}
