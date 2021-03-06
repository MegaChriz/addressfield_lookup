<?php

namespace Drupal\addressfield_lookup;

/**
 * Tests the base functionality of the Address Field Lookup module.
 */
class AddressFieldLookupWebTest extends AddressFieldLookupWebTestBase {

  /**
   * A valid test search term to use for address lookups.
   *
   * @var string
   */
  protected $validSearchTerm = 'TS1 1ST';

  /**
   * An invalid test search term to use for address lookups.
   *
   * @var string
   */
  protected $invalidSearchTerm = 'FK4 4KE';

  /**
   * An invalid country code to use for address lookups.
   *
   * @var string
   */
  protected $invalidCountryCode = 'XX';

  /**
   * Returns a service definition array for the example address lookup service.
   *
   * @return array
   *   Address field lookup service definition array.
   */
  protected function getExampleServiceDefinition() {
    // Get the service array from the example module.
    $example_service_info = \Drupal::moduleHandler()->invoke('addressfield_lookup_example', 'addressfield_lookup_service_info');

    // Set the module and service name.
    $example_service_info['example']['module'] = 'addressfield_lookup_example';
    $example_service_info['example']['machine_name'] = 'example';

    return $example_service_info;
  }

  /**
   * Returns a service definition array for a fake address lookup service.
   *
   * @return array
   *   Address field lookup service definition array.
   */
  protected function getFakeServiceDefinition() {
    return array(
      'fake' => array(
        'name' => t('Fake'),
        'class' => 'AddressFieldLookupFake',
        'description' => t('Provides a fake address field lookup service.'),
        'test data' => 'FK4 4KE',
      ),
    );
  }

  /**
   * Get a list of array keys expected in an address lookup result.
   *
   * @return array
   *   Array of array keys.
   */
  protected function getAddressResultKeys() {
    return array(
      'id',
      'street',
      'place',
    );
  }

  /**
   * Get a list of array keys expected in an address details result.
   *
   * @return array
   *   Array of array keys.
   */
  protected function getAddressDetailKeys() {
    return array(
      'id',
      'sub_premise',
      'premise',
      'thoroughfare',
      'dependent_locality',
      'locality',
      'postal_code',
      'administrative_area',
      'organisation_name',
    );
  }

  /**
   * Test address field lookup service discovery.
   *
   * Ensure services are detected and loaded correctly.
   */
  public function testAddressFieldLookupServices() {
    // Get the list of services.
    $services = \Drupal::service('plugin.manager.address_lookup')->getDefinitions();

    // Test the list of services.
    $this->assertTrue(is_array($services) && !empty($services));

    // Get the example service details.
    $example_service = $this->getExampleServiceDefinition();
    $example_service_machine_name = key($example_service);

    // Test the example service is present.
    $this->assertTrue(isset($services[$example_service_machine_name]));
    $this->assertEqual($services[$example_service_machine_name], reset($example_service));

    // Get the fake service details.
    $fake_service = $this->getFakeServiceDefinition();
    $fake_service_machine_name = key($fake_service);

    // Test the fake service is not present.
    $this->assertFalse(isset($services[$fake_service_machine_name]));

    // Test warning is shown if no service available.
    module_disable(array('addressfield_lookup_example'));

    // Get the status page to test the contents.
    $this->getWithPermissions(array('administer site configuration'), 'admin/reports/status');
    $this->assertText(t('None Available'));
    $this->assertText(t('There is no default address field lookup service available. All address field lookup functionality will be disabled.'));
  }

  /**
   * Test the default address field lookup service.
   */
  public function testDefaultAddressFieldLookupService() {
    // Get the example and fake service definitions.
    $example_service = $this->getExampleServiceDefinition();
    $fake_service = $this->getFakeServiceDefinition();

    // Get the default service.
    $default_service = \Drupal::service('plugin.manager.address_lookup')->getDefaultId();

    // Test the default service is what we expect.
    $this->assertEqual($default_service, reset($example_service));
    $this->assertNotEqual($default_service, reset($fake_service));
  }

  /**
   * Test the address details fetch functionality.
   */
  public function testAddressFieldLookupGetAddressDetails() {
    // Get some address results.
    $addresses = \Drupal::service('plugin.manager.address_lookup')->getAddresses($this->validSearchTerm);

    // Check there is a result.
    $this->assertTrue(is_array($addresses) && !empty($addresses));

    // Test the format of the result.
    foreach ($addresses as $address) {
      foreach ($this->getAddressResultKeys() as $key) {
        $this->assertTrue(isset($address[$key]) && !empty($address[$key]));
      }
    }

    // Get the first result.
    $first_address = reset($addresses);

    // Get the address details for a valid ID.
    $address_details = \Drupal::service('plugin.manager.address_lookup')->getAddressDetails($first_address['id']);

    // Check there are some details.
    $this->assertTrue(is_array($address_details) && !empty($address_details));

    // Check the address details are in the expected format.
    foreach ($this->getAddressDetailKeys() as $key) {
      $this->assertTrue(isset($address_details[$key]));
    }

    // Try to get address details for an invalid address.
    $address_details = \Drupal::service('plugin.manager.address_lookup')->getAddressDetails(9999);

    // Check there are no details.
    $this->assertFalse($address_details);
  }

}
