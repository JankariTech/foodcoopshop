<?php

namespace App\Model\Table;
use Cake\Core\Configure;

/**
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
class TaxesTable extends AppTable
{

    public $useTable = 'tax';

    public $primaryKey = 'id_tax';
 // sic! for binding from taxroulesgroup
    public $validate = [
        'rate' => [
            'range' => [
                'rule' => [
                    'range',
                    0,
                    100
                ],
                'message' => 'Bitte gibt eine Zahl von 0,01 bis 99,99 an'
            ],
            'unique' => [
                'rule' => 'isUnique',
                'message' => 'Dieser Steuersatz wird bereits verwendet.'
            ]
        ]
    ];

    public function getForDropdown()
    {
        $taxes = $this->find('all', [
            'conditions' => [
                'Tax.active' => APP_ON
            ],
            'order' => [
                'Tax.rate' => 'ASC'
            ]
        ]);
        $preparedTaxes = [
            0 => '0 %'
        ];
        foreach ($taxes as $tax) {
            $preparedTaxes[$tax['Tax']['id_tax']] = Configure::read('AppConfig.htmlHelper')->formatAsPercent($tax['Tax']['rate']);
        }
        return $preparedTaxes;
    }
}
