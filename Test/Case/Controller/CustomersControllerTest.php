<?php

App::uses('AppCakeTestCase', 'Test');

/**
 * CustomersControllerTest
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class CustomersControllerTest extends AppCakeTestCase
{

    // called only after the first test method of this class
    public static function setUpBeforeClass()
    {
        self::initTestDatabase();
    }
    
    public function testRegistration()
    {
        $data = array(
            'Customer' => array(
                'email' => '',
                'firstname' => '',
                'lastname' => '',
                'newsletter' => 1
            ),
            'antiSpam' => 0,
            'AddressCustomer' => array(
                'address1' => '',
                'address2' => '',
                'postcode' => '',
                'city' => '',
                'phone_mobile' => '',
                'phone' => ''
            )
        );
        
        // 1) check for spam protection
        $response = $this->addCustomer($data);
        $this->assertRegExpWithUnquotedString('S-p-a-m-!', $response);
        
        
        // 2) check for missing required fields
        $data['antiSpam'] = 4;
        $response = $this->addCustomer($data);
        $this->checkForMainErrorMessage($response);
        $this->assertRegExpWithUnquotedString('Bitte gib deine E-Mail-Adresse an.', $response);
        $this->assertRegExpWithUnquotedString('Bitte gib deinen Vornamen an.', $response);
        $this->assertRegExpWithUnquotedString('Bitte gib deinen Nachnamen an.', $response);
        $this->assertRegExpWithUnquotedString('Bitte gib deine Straße an.', $response);
        $this->assertRegExpWithUnquotedString('Bitte gib deinen Ort an.', $response);
        $this->assertRegExpWithUnquotedString('Bitte gib deine Handynummer an.', $response);
        
        
        // 3) check for wrong data
        $data['Customer']['email'] = 'fcs-demo-mitglied@mailinator.com';
        $data['AddressCustomer']['postcode'] = 'ABCDEF';
        $data['AddressCustomer']['phone_mobile'] = 'adsfkjasfasfdasfajaaa';
        $data['AddressCustomer']['phone'] = '897++asdf+d';
        $response = $this->addCustomer($data);
        $this->checkForMainErrorMessage($response);
        $this->assertRegExpWithUnquotedString('Ein anderes Mitglied oder ein anderer Hersteller verwendet diese E-Mail-Adresse bereits.', $response);
        $this->assertRegExpWithUnquotedString('Die PLZ ist nicht gültig.', $response);
        $this->assertRegExpWithUnquotedString('Die Handynummer ist nicht gültig.', $response);
        $this->assertRegExpWithUnquotedString('Die Telefonnummer ist nicht gültig.', $response);
        
        
        // 4) save user and check record
        $this->saveAndCheckValidCustomer($data, 'new-foodcoopshop-member-1@mailinator.com');
        
        // 5) register again with changed configuration
        $this->changeConfiguration('FCS_DEFAULT_NEW_MEMBER_ACTIVE', 1);
        $this->changeConfiguration('FCS_CUSTOMER_GROUP', 4);
        $this->saveAndCheckValidCustomer($data, 'new-foodcoopshop-member-2@mailinator.com');
        
    }
    
    private function saveAndCheckValidCustomer($data, $email) {
        
        $customerEmail = $email;
        $customerFirstname = 'John';
        $customerLastname = 'Doe';
        $customerCity = 'Scharnstein';
        $customerAddress1 = 'Mainstreet 1';
        $customerAddress2 = 'Door 4';
        $customerPostcode = '4644';
        $customerPhoneMobile = '+436989898';
        $customerPhone = '07659856565';
        
        $data['Customer']['email'] = $customerEmail;
        $data['Customer']['firstname'] = $customerFirstname;
        $data['Customer']['lastname'] = $customerLastname;
        $data['AddressCustomer']['city'] = $customerCity;
        $data['AddressCustomer']['address1'] = $customerAddress1;
        $data['AddressCustomer']['address2'] = $customerAddress2;
        $data['AddressCustomer']['postcode'] = $customerPostcode;
        $data['AddressCustomer']['phone_mobile'] = $customerPhoneMobile;
        $data['AddressCustomer']['phone'] = $customerPhone;
        
        $response = $this->addCustomer($data);
        $this->assertRegExpWithUnquotedString('Deine Registrierung war erfolgreich.', $response);
        $this->assertUrl($this->browser->getUrl(), '/registrierung/abgeschlossen');
        
        $customer = $this->Customer->find('first', array(
            'conditions' => array(
                'Customer.email' => $customerEmail
            )
        ));
        
        // check customer record
        $this->assertEquals(Configure::read('app.db_config_FCS_DEFAULT_NEW_MEMBER_ACTIVE'), $customer['Customer']['active'], 'saving field active failed');
        $this->assertEquals(Configure::read('app.db_config_FCS_CUSTOMER_GROUP'), $customer['Customer']['id_default_group'], 'saving user group failed');
        $this->assertEquals($customerEmail, $customer['Customer']['email'], 'saving field email failed');
        $this->assertEquals($customerFirstname, $customer['Customer']['firstname'], 'saving field firstname failed');
        $this->assertEquals($customerLastname, $customer['Customer']['lastname'], 'saving field lastname failed');
        $this->assertEquals(1, $customer['Customer']['newsletter'], 'saving field newsletter failed');
        
        // check address record
        $this->assertEquals($customerFirstname, $customer['AddressCustomer']['firstname'], 'saving field firstname failed');
        $this->assertEquals($customerLastname, $customer['AddressCustomer']['lastname'], 'saving field lastname failed');
        $this->assertEquals($customerEmail, $customer['AddressCustomer']['email'], 'saving field email failed');
        $this->assertEquals($customerAddress1, $customer['AddressCustomer']['address1'], 'saving field address1 failed');
        $this->assertEquals($customerAddress2, $customer['AddressCustomer']['address2'], 'saving field address2 failed');
        $this->assertEquals($customerCity, $customer['AddressCustomer']['city'], 'saving field city failed');
        $this->assertEquals($customerPostcode, $customer['AddressCustomer']['postcode'], 'saving field postcode failed');
        $this->assertEquals($customerPhoneMobile, $customer['AddressCustomer']['phone_mobile'], 'saving field phone_mobile failed');
        $this->assertEquals($customerPhone, $customer['AddressCustomer']['phone'], 'saving field phone failed');
        
    }
    
    private function checkForMainErrorMessage($response) {
        $this->assertRegExpWithUnquotedString('Beim Speichern sind Fehler aufgetreten!', $response);
    }
    
    /**
     * @param array $data
     * @return string
     */
    private function addCustomer($data) {
        $this->browser->post($this->Slug->getRegistration(), array(
            'data' => $data
        ));
        return $this->browser->getContent();
    }
}
?>