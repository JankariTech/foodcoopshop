<?php

namespace App\Test\TestCase\Traits;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Swoichha Adhikari
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
trait LoginTrait
{

    protected $loggedInUserId;

    public function getLoggedInUserId() {
        return $this->loggedInUserId;
    }

    protected function login($userId)
    {

        $customerTable = TableRegistry::getTableLocator()->get('Customers');
        $loggedUser = $customerTable->find('all', [
            'conditions' => [
                'Customers.id_customer' => $userId
            ],
            'contain' => [
                'AddressCustomers',
            ]
        ]);
        $loggedUser = $loggedUser->first()->toArray();

        $this->session([
            'Auth' => [
                'User' => $loggedUser
            ]
        ]);
        $this->loggedInUserId = $userId;
    }

    protected function loginAsSuperadmin()
    {
        return $this->login(Configure::read('test.superadminId'));
    }

    protected function loginAsAdmin()
    {
        return $this->login(Configure::read('test.adminId'));
    }

    protected function loginAsCustomer()
    {
        return $this->login(Configure::read('test.customerId'));
    }

    protected function loginAsMeatManufacturer()
    {
        return $this->login(Configure::read('test.meatManufacturerId'));
    }

    protected function loginAsVegetableManufacturer()
    {
        return $this->login(Configure::read('test.vegetableManufacturerId'));
    }

    protected function logout()
    {
        $this->get($this->Slug->getLogout());
    }
}
