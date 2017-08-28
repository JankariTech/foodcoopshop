INSERT INTO `fcs_configuration` (`id_configuration`, `id_shop_group`, `id_shop`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES ('0', NULL, NULL, '1', 'FCS_USE_VARIABLE_MEMBER_FEE', 'Variablen Mitgliedsbeitrag verwenden?<br /><div class=\"small\">Den variablen Mitgliedsbeitrag bei den Hersteller-Rechnungen abziehen? Die Produkt-Preise müssen entsprechend höher eingegeben werden.</div>', '0', 'readonly', '40', '2017-08-02 00:00:00', '2017-08-02 00:00:00');
INSERT INTO `fcs_configuration` (`id_configuration`, `id_shop_group`, `id_shop`, `active`, `name`, `text`, `value`, `type`, `position`, `date_add`, `date_upd`) VALUES ('0', NULL, NULL, '1', 'FCS_DEFAULT_VARIABLE_MEMBER_FEE_PERCENTAGE', 'Standardwert für variablen Mitgliedsbeitrag<br /><div class=\"small\">Der Prozentsatz kann in den Hersteller-Einstellungen auch individuell angepasst werden.</div>', '0', 'readonly', '50', '2017-08-02 00:00:00', '2017-08-02 00:00:00');
ALTER TABLE `fcs_manufacturer` CHANGE `compensation_percentage` `variable_member_fee` INT(8) UNSIGNED NULL DEFAULT NULL;