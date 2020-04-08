<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.0.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\View;

use Cake\Core\Configure;
use Cake\View\View;

/**
 * Application View
 *
 * Your application’s default view class
 *
 * @link https://book.cakephp.org/3.0/en/views.html#the-app-view
 */
class AppView extends View
{

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading helpers.
     *
     * e.g. `$this->loadHelper('Html');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadHelper('Html', [
            'className' => 'MyHtml'
        ]);
        
        $timeHelperClass = 'MyTime';
        if (Configure::read('appDb.FCS_MAIN_DELIVERY_RHYTHM') == 'daily') {
            $timeHelperClass = 'MyTimeDaily';
        }
        $this->loadHelper('Time', [
            'className' => $timeHelperClass
        ]);
        
        $this->loadHelper('Number', [
            'className' => 'MyNumber'
        ]);
        $this->loadHelper('Form', [
            'widgets' => [
                '_default' => ['MyBasic']
            ]
        ]);
        $this->loadHelper('Menu');
        $this->loadHelper('Slug');
        $this->loadHelper('Text');
        $this->loadHelper('AssetCompress.AssetCompress');
        $this->loadHelper('TimebasedCurrency');
        $this->loadHelper('PricePerUnit');
    }
}
