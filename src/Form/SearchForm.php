<?php

namespace Drupal\tvmaze\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * The search form.
 */
class SearchForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tvmaze_search';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $keywords = $this->getRequest()->get('keywords');
    $form['keywords'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Keywords'),
      '#default_value' => $keywords,
    ];
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['search'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('tvmaze.search', [
      'keywords' => $form_state->getValue('keywords'),
    ]);
  }

}
