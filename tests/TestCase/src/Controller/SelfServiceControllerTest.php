<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AssertPagesForErrorsTrait;
use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\ORM\TableRegistry;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Http\ServerRequest;

class SelfServiceControllerTest extends AppCakeTestCase
{

    use AssertPagesForErrorsTrait;
    use IntegrationTestTrait;
    use LoginTrait;

    public function testBarCodeLoginAsSuperadminIfNotEnabled()
    {
        $this->doBarCodeLogin();
        $this->assertFlashMessage(__('Signing_in_failed_account_inactive_or_password_wrong?'));
    }

    public function testPageSelfService()
    {
        $this->loginAsSuperadmin();
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $testUrls = [
            $this->Slug->getSelfService()
        ];
        $this->assertPagesForErrors($testUrls);
    }

    public function testBarCodeLoginAsSuperadminValid()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->doBarCodeLogin();
        $this->assertArrayNotHasKey('Flash',$_SESSION);
    }

    public function testSelfServiceAddProductPricePerUnitWrong()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->doBarCodeLogin();
        $this->addProductToSelfServiceCart(351, 1);
        $this->assertResponseContains('Bitte trage das entnommene Gewicht ein.');
        $this->assertJsonError();
    }

    public function testSelfServiceAddAttributePricePerUnitWrong()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->doBarCodeLogin();
        $this->addProductToSelfServiceCart('350-15', 1, 'bla bla');
        $this->assertResponseContains('Bitte trage das entnommene Gewicht ein.');
        $this->assertJsonError();
    }

    public function testSelfServiceOrderWithoutCheckboxes() {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->doBarCodeLogin();
        $this->addProductToSelfServiceCart(349, 1);
        $this->finishSelfServiceCart(0, 0);
        $this->assertRegExpWithUnquotedString('Bitte akzeptiere die AGB.', $this->httpClient->getContent());
        $this->assertRegExpWithUnquotedString('Bitte akzeptiere die Information über das Rücktrittsrecht und dessen Ausschluss.', $this->httpClient->getContent());
    }

    public function testSelfServiceRemoveProductWithPricePerUnit()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->doBarCodeLogin();
        $this->addProductToSelfServiceCart(351, 1, '0,5');
        $this->removeProductFromSelfServiceCart(351);
        $this->assertJsonOk();
        $this->CartProductUnit = TableRegistry::getTableLocator()->get('CartProductUnits');
        $cartProductUnits = $this->CartProductUnit->find('all')->toArray();
        $this->assertEmpty($cartProductUnits);
    }

    public function testSelfServiceOrderWithoutPricePerUnit()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->doBarCodeLogin();
        $this->addProductToSelfServiceCart(346, 1, 0);
        $this->finishSelfServiceCart(1, 1);

        $this->Cart = TableRegistry::getTableLocator()->get('Carts');
        $cart = $this->Cart->find('all', [
            'order' => [
                'Carts.id_cart' => 'DESC'
            ],
        ])->first();

        $cart = $this->getCartById($cart->id_cart);

        $this->assertEquals(1, count($cart->cart_products));

        foreach($cart->cart_products as $cartProduct) {
            $orderDetail = $cartProduct->order_detail;
            $this->assertEquals($orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database')), Configure::read('app.timeHelper')->getCurrentDateForDatabase());
        }

        $this->EmailLog = TableRegistry::getTableLocator()->get('EmailLogs');
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(1, count($emailLogs));

        $this->assertEmailLogs(
            $emailLogs[0],
            'Dein Einkauf',
            [
                'Artischocke'
            ],
            [
                Configure::read('test.loginEmailSuperadmin')
            ]
        );
    }

    public function testSelfServiceOrderWithPricePerUnit()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->doBarCodeLogin();
        $this->addProductToSelfServiceCart('350-15', 1, '1,5');
        $this->addProductToSelfServiceCart(351, 1, '0,5');
        $this->finishSelfServiceCart(1, 1);

        $this->Cart = TableRegistry::getTableLocator()->get('Carts');
        $cart = $this->Cart->find('all', [
            'order' => [
                'Carts.id_cart' => 'DESC'
            ],
        ])->first();

        $cart = $this->getCartById($cart->id_cart);

        $this->assertEquals(2, count($cart->cart_products));

        foreach($cart->cart_products as $cartProduct) {
            $orderDetail = $cartProduct->order_detail;
            $this->assertEquals($orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database')), Configure::read('app.timeHelper')->getCurrentDateForDatabase());
        }

        $this->EmailLog = TableRegistry::getTableLocator()->get('EmailLogs');
        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEquals(1, count($emailLogs));

        $this->assertEmailLogs(
            $emailLogs[0],
            'Dein Einkauf',
            [
                'Lagerprodukt mit Varianten : 1,5 kg',
                'Lagerprodukt 2 : 0,5 kg',
                '15,00 €',
                '5,00 €'
            ],
            [
                Configure::read('test.loginEmailSuperadmin')
            ]
        );
    }

    public function testSelfServideOrderWithDeliveryBreak()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->changeConfiguration('FCS_NO_DELIVERY_DAYS_GLOBAL', Configure::read('app.timeHelper')->getDeliveryDateByCurrentDayForDb());
        $this->doBarCodeLogin();
        $this->addProductToSelfServiceCart('350-15', 1, '1,5');
        $this->finishSelfServiceCart(1, 1);
        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
        $actionLogs = $this->ActionLog->find('all', [])->toArray();
        $this->assertRegExpWithUnquotedString('Demo Superadmin hat eine neue Bestellung getätigt (15,00 €).', $actionLogs[0]->text);
    }

    private function addProductToSelfServiceCart($productId, $amount, $orderedQuantityInUnits = -1)
    {
        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
            ],
            'environment'=>[
                'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
                'HTTP_REFERER' => Configure::read('app.cakeServerName') . '/' . __('route_self_service')
            ]
        ]);

        $this->post(
            '/warenkorb/ajaxAdd/',
            [
                'productId' => $productId,
                'amount' => $amount,
                'orderedQuantityInUnits' => $orderedQuantityInUnits
            ],
        );
        var_dump(json_decode($this->_getBodyAsString()));
        return json_decode($this->_getBodyAsString());
    }

    private function removeProductFromSelfServiceCart($productId)
    {
        $this->httpClient->ajaxPost(
            '/warenkorb/ajaxRemove/',
            [
                'productId' => $productId
            ],
            $this->getSelfServicePostOptions()
            );
        return $this->httpClient->getJsonDecodedContent();
    }

    private function getSelfServicePostOptions()
    {
        return [
            'headers' => [
                'X-Requested-With' => 'XMLHttpRequest',
                'REFERER' => Configure::read('app.cakeServerName') . '/' . __('route_self_service')
            ],
            'type' => 'json'
        ];
    }

    private function finishSelfServiceCart($general_terms_and_conditions_accepted, $cancellation_terms_accepted)
    {
        $data = [
            'Carts' => [
                'general_terms_and_conditions_accepted' => $general_terms_and_conditions_accepted,
                'cancellation_terms_accepted' => $cancellation_terms_accepted
            ],
        ];
        $this->httpClient->post(
            $this->Slug->getSelfService(),
            $data,
            [
                'headers' => [
                    'REFERER' => Configure::read('app.cakeServerName') . '/' . __('route_self_service')
                ]
            ]
        );
    }

    private function doBarCodeLogin()
    {

        $this->configRequest([
           'headers' => [
               'Accept' => 'application/json',
           ],
       ]);
        $this->_retainFlashMessages = true;

        $this->post($this->Slug->getLogin(), [
           'barCode' => Configure::read('test.superadminBarCode'),
       ]);
    }

    protected function changeConfiguration($configKey, $newValue)
    {
        $this->Configuration = TableRegistry::getTableLocator()->get('Configurations');

        $query = 'UPDATE fcs_configuration SET value = :newValue WHERE name = :configKey;';
        $params = [
            'newValue' => $newValue,
            'configKey' => $configKey
        ];
        $statement = $this->dbConnection->prepare($query);
        $statement->execute($params);
        $this->Configuration->loadConfigurations();
        $this->logout();
    }

    public function assertJsonError()
    {
        $response = json_decode($this->_getBodyAsString());
        $this->assertEquals(0, $response->status, 'json status should be "0"');
    }

}
