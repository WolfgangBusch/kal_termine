<?php
/* Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2025
 */
#
# --- Stylesheet-Dateien und Javascript-Datei auch im Backend einbinden
$addon=$this->getPackageId();
$file=rex_url::addonAssets($addon).$addon.'.css';
rex_view::addCssFile($file);
$file=rex_url::addonAssets($addon).'fontawesome/css/all.css';
rex_view::addCssFile($file);
$file=rex_url::addonAssets($addon).$addon.'.js';
rex_view::addJsFile($file);
#
# --- Terminkategorien als Permissions einrichten
cms_interface::register_perm_kategorien();
?>
