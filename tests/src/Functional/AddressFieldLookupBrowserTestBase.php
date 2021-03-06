<?php

namespace Drupal\Tests\addressfield_lookup\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Provides a base class for Addressfield lookup functional tests.
 */
abstract class AddressFieldLookupBrowserTestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['addressfield_lookup', 'addressfield_lookup_example'];

  /**
   * Retrieves a path making sure a set of permissions is required to access it.
   *
   * After calling this method, a user with the given permissions is logged in
   * and the retrieved page is loaded into the internal browser.
   *
   * @param array $permissions
   *   An array of permission names to assign to user. Note that the user always
   *   has the default permissions derived from the "authenticated users" role.
   * @param string $path
   *   Drupal path or URL to load into the internal browser.
   * @param array $options
   *   Options to be forwarded to url().
   * @param array $headers
   *   An array containing additional HTTP request headers, each formatted as
   *   "name: value".
   *
   * @see \Drupal\Tests\BrowserTestBase::drupalGet()
   * @see \Drupal\Tests\user\Traits\UserCreationTrait::createUser()
   */
  protected function getWithPermissions(array $permissions, $path, array $options = array(), array $headers = array()) {
    $this->drupalGet($path, $options, $headers);
    $this->assertResponse(403);

    $this->drupalLogin($this->drupalCreateUser($permissions));
    $this->drupalGet($path, $options, $headers);
    $this->assertResponse(200);
  }

  /**
   * Creates an address field (with the lookup handler) and attaches it.
   *
   * The entity and bundle to attach the field to are passed as parameters.
   *
   * @param string $name
   *   The field name of the address field to create.
   * @param string $entity_type
   *   The name of the entity type to attach the field to.
   * @param string $bundle
   *   The name of the entity bundle to attach the field to.
   */
  protected function createAttachAddressLookupField($name, $entity_type, $bundle) {
    // Create the field.
    $field = array(
      'active' => 1,
      'cardinality' => 1,
      'deleted' => 0,
      'entity_types' => array(),
      'field_name' => $name,
      'indexes' => array(),
      'locked' => 0,
      'module' => 'addressfield',
      'settings' => array(),
      'translatable' => 0,
      'type' => 'addressfield',
    );
    // @FIXME
// Fields and field instances are now exportable configuration entities, and
// the Field Info API has been removed.
// 
// 
// @see https://www.drupal.org/node/2012896
// field_create_field($field);


    // Attach an instance of it.
    $instance = array(
      'bundle' => $bundle,
      'default_value' => NULL,
      'deleted' => 0,
      'description' => '',
      'display' => array(),
      'entity_type' => $entity_type,
      'field_name' => $name,
      'label' => 'Address',
      'required' => 0,
      'settings' => array(
        'user_register_form' => FALSE,
      ),
      'widget' => array(
        'active' => 1,
        'module' => 'addressfield',
        'settings' => array(
          'available_countries' => array(),
          'default_country' => 'GB',
          'format_handlers' => array(
            'address' => 'address',
            'address-hide-postal-code' => 0,
            'address-hide-street' => 0,
            'address-hide-country' => 0,
            'organisation' => 0,
            'name-full' => 0,
            'name-oneline' => 0,
            'address-optional' => 0,
            'addressfield_lookup' => 'addressfield_lookup',
          ),
        ),
        'type' => 'addressfield_standard',
        'weight' => 7,
      ),
    );
    // @FIXME
// Fields and field instances are now exportable configuration entities, and
// the Field Info API has been removed.
// 
// 
// @see https://www.drupal.org/node/2012896
// field_create_instance($instance);

  }

}
