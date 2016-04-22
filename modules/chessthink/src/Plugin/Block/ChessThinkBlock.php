<?php

namespace Drupal\chessthink\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a Chess think block with which you can generate dummy text anywhere
 *
 * @Block(
 *   id = "chessthink_block",
 *   admin_label = @Translation("Chess think block"),
 * )
 */

class ChessThinkBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Return the form @ Form/ChessThinkBlockForm.php
    return \Drupal::formBuilder()->getForm('Drupal\chessthink\Form\ChessThinkBlockForm');
  }
  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'generate chess think');
  }
  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    return $form;
  }
  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('chessthink_block_settings', $form_state->getValue('chessthink_block_settings'));
  }

}
