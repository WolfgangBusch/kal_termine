<?php
/* Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2024
 */
$addon=$this->getPackageId();
$basedir=rex_path::addon($addon);
require_once $basedir.'lib/class.kal_termine.php';
require_once $basedir.'lib/class.kal_termine_install.php';
#
# --- Erzeugen der Termintabelle, falls sie nicht schon existiert
kal_termine_install::create_table();
#     mit Version 3.5 wird eine Spalte 'monate' in rex_kal_termine ergaenzt
      kal_termine_install::add_column_monate();
#
# --- Erzeugen/Aktualisieren der Module
kal_termine_install::build_modules();
#
# --- ggf. zu Anfang eine Default-Konfiguration einrichten
$settings=kal_termine::kal_get_config();
if(count($settings)<=0):
  $settings=$addon::kal_default_config();
  $addon::kal_set_config($settings);
  endif;
#     zugehoeriges CSS-File schreiben
$config=$addon.'_config';
$config::kal_write_css();
?>