<?php
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
$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();".
    Configure::read('app.jsNamespace').".AppFeatherlight.initLightboxForImages('.first-column a.lightbox');"
]);
?>

<h1>Hersteller
<?php
if (Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') || $appAuth->user()) {
    echo '<span>'.count($manufacturers) . ' gefunden</span>';
}
?>
</h1>

<?php

foreach ($manufacturers as $manufacturer) {
    echo '<div class="manufacturer-wrapper">';

        echo '<div class="first-column">';
            $srcLargeImage = $this->Html->getManufacturerImageSrc($manufacturer['Manufacturers']['id_manufacturer'], 'large');
            $largeImageExists = preg_match('/de-default/', $srcLargeImage);
    if (!$largeImageExists) {
        echo '<a class="lightbox" href="'.$srcLargeImage.'">';
    }
            echo '<img src="' . $this->Html->getManufacturerImageSrc($manufacturer['Manufacturers']['id_manufacturer'], 'medium'). '" />';
    if (!$largeImageExists) {
        echo '</a>';
    }
        echo '</div>';

        echo '<div class="second-column">';
            echo '<h4>'.$this->Html->link(
                $manufacturer['Manufacturers']['name'],
                $this->Slug->getManufacturerDetail($manufacturer['Manufacturers']['id_manufacturer'], $manufacturer['Manufacturers']['name'])
            ).'</h4>';
            echo $manufacturer['Manufacturers']['short_description'];

            $manufacturerHolidayString = $this->Html->getManufacturerHolidayString($manufacturer['Manufacturers']['holiday_from'], $manufacturer['Manufacturers']['holiday_to'], $manufacturer[0]['IsHolidayActive'], true, $manufacturer['Manufacturers']['name']);
    if ($manufacturerHolidayString != '') {
        echo '<h2 class="info">'.$manufacturerHolidayString.'</h2>';
    }

        echo '</div>';

        echo '<div class="third-column">';
            echo $this->Html->link(
                'Alle Produkte anzeigen' . ($appAuth->user() || Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') ? ' (' . $manufacturer['product_count'] .')' : ''),
                $this->Slug->getManufacturerDetail($manufacturer['Manufacturers']['id_manufacturer'], $manufacturer['Manufacturers']['name']),
                ['class' => 'btn btn-success']
            );
        echo '</div>';

    echo '</div>';

    echo '<div class="sc"></div>';
}

?>
