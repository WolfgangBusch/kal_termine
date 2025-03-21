<?php
/* Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2025
 */
#
# --- Erzeugen der Termintabelle, falls sie nicht schon existiert
kal_termine_install::create_table();
#     mit Version 3.5 wird eine Spalte 'monate' in der Termintabelle ergaenzt
      kal_termine_install::add_column_monate();
#
# --- Erzeugen/Aktualisieren der Module
kal_termine_install::build_modules();
#
# --- ggf. zu Anfang eine Default-Konfiguration einrichten
$settings=kal_termine::kal_get_config();
if(count($settings)<=0):
  $settings=kal_termine::kal_default_config();
  kal_termine::kal_set_config($settings);
  kal_termine_config::kal_write_css();   // zugehoerige CSS-Datei schreiben
  endif;
?>