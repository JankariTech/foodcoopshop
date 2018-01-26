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

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();" . Configure::read('app.jsNamespace') . ".Helper.initCkeditorBig('PageContent');" . Configure::read('app.jsNamespace') . ".Admin.initForm('" . (isset($this->request->data['Pages']['id_page']) ? $this->request->data['Pages']['id_page'] : "") . "', 'Pages');
    "
]);

?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa fa-check"></i> Speichern</a> <a href="javascript:void(0);"
            class="btn btn-default cancel"><i class="fa fa-remove"></i> Abbrechen</a>
    </div>
</div>

<div id="help-container">
    <ul>
        <li>Auf dieser Seite kannst du die Seite ändern.</li>
    </ul>
</div>

<div class="sc"></div>

<?php

echo $this->Form->create('Pages', [
    'class' => 'fcs-form'
]);

echo '<input type="hidden" name="data[referer]" value="' . $referer . '" id="referer">';
echo $this->Form->hidden('Pages.id_page');
echo $this->Form->input('Pages.title', [
    'label' => 'Seitentitel',
    'required' => true
]);

echo $this->Form->input('Pages.menu_type', [
    'type' => 'select',
    'label' => 'In welchem Menü<br /><span class="small">soll die Seite angezeigt werden?</span>',
    'options' => $this->Html->getMenuTypes()
]);
echo $this->Form->input('Pages.id_parent', [
    'type' => 'select',
    'label' => 'Übergeordneter Menüpunkt<br /><span class="small">Hauptmenü: Auswahl leer lassen</span>',
    'empty' => 'Übergeordneten Menüpunkt auswählen...',
    'options' => $mainPagesForDropdown
]);
echo $this->Form->input('Pages.position', [
    'div' => [
        'class' => 'short text input'
    ],
    'label' => 'Reihenfolge im Menü<br /><span class="small">Zahl von 0 bis 100</span>',
    'type' => 'text',
    'after' => '<span class="after small">"0" zeigt die Seite nicht im Menü an, sie bleibt aber über den Link erreichbar.</span>'
]);

echo $this->Form->input('Pages.full_width', [
    'label' => 'Ganze Breite?',
    'type' => 'checkbox',
    'after' => '<span class="after small">Inhalt der Seite wird verbreitert, indem das linke Menü ausgeblendet wird.</span>'
]);
echo $this->Form->input('Pages.extern_url', [
    'placeholder' => 'z.B. http://www.foodcoopshop.com, kann auch leer bleiben',
    'label' => 'Link auf externe Seite?<br /><span class="small">Menüpunkt führt auf diese Webseite (der Inhalt der Seite wird nicht angezeigt).</span>',
    'div' => [
        'class' => 'long text input'
    ]
]);

if ($this->request->here != $this->Slug->getPageAdd()) {
    echo $this->Form->input('Pages.delete_page', [
        'label' => 'Seite löschen?',
        'type' => 'checkbox',
        'after' => '<span class="after small">Anhaken und dann auf <b>Speichern</b> klicken.</span>'
    ]);
}

echo $this->Form->input('Pages.is_private', [
    'label' => 'Nur für Mitglieder sichtbar?',
    'type' => 'checkbox'
]);
echo $this->Form->input('Pages.active', [
    'label' => 'Aktiv?',
    'type' => 'checkbox'
]);

echo $this->Form->input('Pages.content', [
    'class' => 'ckeditor',
    'type' => 'textarea',
    'label' => 'Text<br /><br /><span class="small"><a href="https://foodcoopshop.github.io/de/wysiwyg-editor" target="_blank">Wie verwende ich den Editor?</a></span>'
]);

?>

</form>

<div class="sc"></div>
