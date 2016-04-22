<?php

/**
 * @file
 * Contains \Drupal\chessthink\Form\ChessThinkForm.
 */

namespace Drupal\chessthink\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class ChessThinkForm extends ConfigFormBase {

  /**
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'chessthink_form';
  }
  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Form constructor
    $form = parent::buildForm($form, $form_state);
    // Default settings
    $config = $this->config('chessthink.settings');
    // Page title field
    $form['page_title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Chess think generator page title:'),
      '#default_value' => $config->get('chessthink.page_title'),
      '#description' => $this->t('Give your chess think generator page a title.'),
    );
    // Source text field
    $form['source_text'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Source text for chess think generation:'),
      '#default_value' => $config->get('chessthink.source_text'),
      '#description' => $this->t('Write one sentence per line. Those sentences will be used to generate random text.'),
    );

    return $form;
  }
  /**
   * {@inheritdoc}.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('chessthink.settings');
    $config->set('chessthink.source_text', $form_state->getValue('source_text'));
    $config->set('chessthink.page_title', $form_state->getValue('page_title'));
    $config->save();
    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}.
   */
  protected function getEditableConfigNames() {
    return [
      'chessthink.settings',
    ];
  }

}
