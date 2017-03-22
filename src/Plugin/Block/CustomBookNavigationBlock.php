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
      // ksm($this);

    if ($node = $this->requestStack->getCurrentRequest()->get('node')) {
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
        ->condition('status', NODE_PUBLISHED)
        ->execute();

      // Only show the block if the user has view access for the top-level node.
      if ($nid) {
        // We want to show the whole tree, by just removing '$node->book'
        // $tree = $this->bookManager->bookTreeAllData($node->book['bid'], $node->book);
        $tree = $this->bookManager->bookTreeAllData($node->book['bid']);
        return $this->bookManager->bookTreeOutput($tree);
      }
    }
    return array();
  }

}