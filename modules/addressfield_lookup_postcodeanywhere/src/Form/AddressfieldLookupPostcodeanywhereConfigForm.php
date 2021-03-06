<?php

/**
 * @file
 * Contains \Drupal\addressfield_lookup_postcodeanywhere\Form\AddressfieldLookupPostcodeanywhereConfigForm.
 */

namespace Drupal\addressfield_lookup_postcodeanywhere\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class AddressfieldLookupPostcodeanywhereConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'addressfield_lookup_postcodeanywhere_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('addressfield_lookup_postcodeanywhere.settings');

    foreach (Element::children($form) as $variable) {
      $config->set($variable, $form_state->getValue($form[$variable]['#parents']));
    }
    $config->save();

    if (method_exists($this, '_submitForm')) {
      $this->_submitForm($form, $form_state);
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['addressfield_lookup_postcodeanywhere.settings'];
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $form['addressfield_lookup_postcodeanywhere_login'] = [
      '#title' => t('Login'),
      '#type' => 'textfield',
      '#default_value' => \Drupal::config('addressfield_lookup.settings')->get('addressfield_lookup_postcodeanywhere_login'),
      '#description' => t('The login associated with the Royal Mail license (not required for click licenses).'),
    ];

    $form['addressfield_lookup_postcodeanywhere_license'] = [
      '#title' => t('License'),
      '#type' => 'textfield',
      '#default_value' => \Drupal::config('addressfield_lookup.settings')->get('addressfield_lookup_postcodeanywhere_license'),
      '#description' => t('API key to use to authenticate to Postcode Anywhere.'),
    ];

    $form['addressfield_lookup_postcodeanywhere_country_quality'] = [
      '#title' => t('Minimum addressing quality'),
      '#type' => 'select',
      '#options' => array_combine(range(1, 5), range(1, 5)),
      '#default_value' => \Drupal::config('addressfield_lookup.settings')->get('addressfield_lookup_postcodeanywhere_country_quality'),
      '#description' => t('The minimum quality value (1 least to 5 most) of address data required. This quality value is used to filter supported countries pulled from <a href="@country_url" target="_blank">PCA Predict</a>', [
        '@country_url' => 'https://www.pcapredict.com/support/webservice/extras/lists/countrydata/3'
        ]),
      '#element_validate' => ['element_validate_integer_positive'],
    ];

    // Add a custom submit function.
    $form['#submit'][] = 'addressfield_lookup_postcodeanywhere_config_form_submit';

    return parent::buildForm($form, $form_state);
  }

  public function _submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    // Show warning if supported countries data quality level is below 3.
    if ($form_state->getValue(['addressfield_lookup_postcodeanywhere_country_quality']) < 3) {
      drupal_set_message(t('You have selected an addressing quality below 4. This is not recommended and could result in poor quality address lookups.'), 'warning');
    }

    $form_state->set(['redirect'], 'admin/config/regional/addressfield-lookup');
  }

}
