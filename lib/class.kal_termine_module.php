<?php
/* Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2025
 */
class kal_termine_module {
#
#----------------------------------------- Methoden
#   Termine verwalten
#      kal_manage_termine()
#   Termine anzeigen
#      kal_terminmenue_intern()
#      kal_terminmenue()
#      kal_config_terminmenue_intern()
#      kal_config_terminmenue()
#      kal_show_termine()
#
#----------------------------------------- Konstanten
const this_addon=kal_termine::this_addon;   // Name des AddOns
#
#----------------------------------------- Termine verwalten
public static function kal_manage_termine() {
   echo kal_termine_menues::kal_menue(0,'');
   }
#
#----------------------------------------- Termine anzeigen
public static function kal_terminmenue_intern() {
   #   Rueckgabe des HTML-Codes zu Auswahl des Terminmenues. Die Menuenummer
   #   wird aus dem Speicher ausgelesen. Erlaubt sind nur die Menues 1, 6, 7.
   #
   $addon=self::this_addon;
   $menues=$addon::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1):
      if(strpos($menues[$i]['name'],'natsmenü')>0)   $menmom=$i;  // Monatsmenue
      if(strpos($menues[$i]['name'],'erminüber')>0)  $menueb=$i;  // Terminübersicht
      if(strpos($menues[$i]['name'],'minliste')>0)   $mentel=$i;  // Terminliste
      endfor;
   #
   # --- Auslesen der gespeicherten Menuenummer
   $param=cms_interface::read_menue_data();
   $men=$param['men'];
   if(empty($men)) $men=$menmom;
   if($men!=$menmom and $men!=$menueb and $men!=$mentel) $men=$menmom;
   #
   # --- Anzeige des Auswahlmenues
   $str='
<h4>Auswahl eines Startmenüs zur Darstellung von Terminen</h4>
<div>Der Default-Zeitraum enthält jeweils den heutigen Tag.</div>
<div class="form_empline">&nbsp;</div>
<form method="post">
<table class="kal_table">';
   #
   # --- Auswahl Kalendermenue
   for($i=1;$i<=count($menues);$i=$i+1):
      if($i!=$menmom and $i!=$menueb and $i!=$mentel) continue;
      $chk='';
      if($i==$men) $chk=' checked';
      $str=$str.'
    <tr valign="top">
        <td class="form_left2">
            <input type="radio" name="'.$addon::KAL_MENNR.'" value="'.$i.'"'.$chk.'>
            &nbsp;<b>'.$menues[$i]['name'].'</b></td>
        <td>'.$menues[$i]['titel'].'</td></tr>
    <tr><td class="form_empline" colspan="2">&nbsp;</td></tr>';
      endfor;
   $str=$str.'
    <tr valign="bottom">
        <td></td>
        <td><button class="kal_btn_save" type="submit" name="save_men" value="save_men"
                    title="Das Kalendermenü auswählen">
            &nbsp;das&nbsp;Menü&nbsp;auswählen&nbsp;</button></td></tr>
</table>
</form>';
   return $str;
   }
public static function kal_terminmenue() {
   #   Rueckgabe des HTML-Codes zu Auswahl des Terminmenues. Das ausgewaehlte
   #   Menue wird gespeichert und als gespeichert angezeigt. 
   #
   $addon=self::this_addon;
   #
   # --- Auslesen der gespeicherten Menuenummer
   $param=cms_interface::read_menue_data();
   $menalt=$param['men'];
   $men=$menalt;
   #
   # --- Erstanzeige des Menues
   $str=self::kal_terminmenue_intern();
   #
   # --- neu ausgewaehltes Menue speichern
   $save_men=$addon::kal_post_in('save_men');
   if(!empty($save_men)):
     $men=$addon::kal_post_in($addon::KAL_MENNR,'int');
     cms_interface::write_menue_data('men',$men);
     #     Zweitanzeige des Menues (mit neu ausgewaehltem Menue)
     $str=self::kal_terminmenue_intern();
     endif;
   return $str;
   }
public static function kal_config_terminmenue_intern() {
   #   Rueckgabe des HTML-Codes zu Auswahl und Konfigurierung des Terminmenues.
   #   Erlaubt sind nur die Menues 1, 6, 7.
   #
   $addon=self::this_addon;
   $menues=$addon::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1):
      if(strpos($menues[$i]['name'],'natsmenü')>0)   $menmom=$i;  // Monatsmenue
      if(strpos($menues[$i]['name'],'erminüber')>0)  $menueb=$i;  // Terminübersicht
      if(strpos($menues[$i]['name'],'minliste')>0)   $mentel=$i;  // Terminliste
      endfor;
   #
   # --- Auswahl Kalendermenue
   $str=self::kal_terminmenue();
   #
   # --- Auslesen der gespeicherten Parameter
   $param=cms_interface::read_menue_data();
   $men    =$param['men'];
   $von    =$param['datum'];
   $anztage=$param['anztage'];
   $kid    =$param['kat_id'];
   if(empty($men)) $men=$menmom;
   if($men!=$menmom and $men!=$menueb and $men!=$mentel) $men=$menmom;
   #
   if(!empty($von)) $von=kal_termine_kalender::kal_standard_datum($von);
   $heute=kal_termine_kalender::kal_heute();
   #
   # --- Schaltjahr?
   $jahr=substr($heute,6);
   $jahrestage=365;
   if(4*intval($jahr/4)>=$jahr) $jahrestage=366;
   #
   # --- Zeitraum Monatsmenue
   if($men==$menmom):
     $von='01'.substr($heute,2);
     $mon=intval(substr($heute,3,2));
     $anz=kal_termine_kalender::kal_monatstage($jahr)[$mon];
     $ab1='<i>Default: 1. Tag des Monats</i>';
     $ab2='<b>(nicht änderbar)</b>';
     $zr1='<i>Default: Anzahl Tage des Monats</i></i>';
     $zr2='<b>(nicht änderbar)</b>';
     $ro=' readonly';
     $roclass='form_readonly';
     endif;
   #
   # --- Zeitraum Terminuebersicht
   if($men==$menueb):
     $von=$heute;
     $anz='';
     $ab1='<i>Default: der heutige Tag</i>';
     $ab2='<b>(nicht änderbar)</b>';
     $zr1='<i>Default: unbegrenzt</i>';
     $zr2='<b>(nicht änderbar)</b>';
     $ro=' readonly';
     $roclass='form_readonly';
     endif;
   #
   # --- Zeitraum Terminliste
   if($men==$mentel):
     $anz=$anztage;
     if($anz<=0) $anz='';
     $ab1='<i>Default: der heutige Tag</i>';
     $ab2='(Format <tt>tt.mm.jjjj</tt>)';
     $zr1='<i>Default: Anzahl Tage des Jahres</i>';
     $zr2='<i>(ab Startdatum)</i>';
     $ro='';
     $roclass='';
     endif;
   #
   $str=$str.'
<form method="post">
<table class="kal_table">';
   #
   # --- Auswahl Datum, ab wann die Termine ausgegeben werden sollen
   $str=$str.'
    <tr valign="top">
        <td colspan="4">
            <h4>'.$menues[$men]['name'].':</h4></td></tr>
    <tr><td colspan="4" class="form_empline">&nbsp;</td></tr>
    <tr valign="top">
        <th class="kal_indent form_left2">
            Termine ab:</th>
        <td class="form_left2">
            <input type="text" name="'.$addon::KAL_AB.'" value="'.$von.'"
                   class="form_date '.$roclass.'"'.$ro.'></td>
        <td class="form_pad form_left2">
            '.$ab1.'</td>
        <td class="form_pad">
            '.$ab2.'</td></tr>';
   #
   # --- Auswahl Zeitraum in Anzahl Tage
   $str=$str.'
    <tr><td colspan="4" class="form_empline">&nbsp;</td></tr>
    <tr valign="top">
        <th class="kal_indent form_left2">
            Anzahl Tage:</th>
        <td class="form_left2">
            <input type="text" name="'.$addon::KAL_ANZTAGE.'" value="'.$anz.'"
                   class="form_int '.$roclass.'"'.$ro.'></td>
        <td class="form_pad form_left2">
            '.$zr1.'</td>
        <td class="form_pad">
            '.$zr2.'</td></tr>';
   #
   # --- Auswahl der Kategorie(n)
   $katids=cms_interface::allowed_terminkategorien();
   $str=$str.'
    <tr><td colspan="4" class="form_empline">&nbsp;</td></tr>
    <tr valign="top">
        <th class="kal_indent form_left2">
            Kategorien:</th>
        <td class="form_left2">
            '.$addon::kal_select_kategorie($addon::KAL_KATEGORIE,$kid,$katids,TRUE).'</td>
        <td class="form_pad" colspan="2">
            Es können die Termine aller erlaubten Terminkategorien ausgewählt werden
            oder aber nur die Termine aus einer dieser Kategorien.</td></tr>';
   #
   # --- Speichern-Button
   $str=$str.'
    <tr><td colspan="4" class="form_empline">&nbsp;</td></tr>
    <tr valign="top">
        <td></td>
        <td><button class="kal_btn_save" type="submit" name="save" value="save"
                    title="alle Parameter speichern">
            &nbsp;Speichern&nbsp;</button></td>
        <td class="form_pad" colspan="2">
            <i>Falls Parameter geändert werden, muss (noch einmal) gespeichert werden.</i></td></tr>
</table>
</form>';
   #
   return $str;
   }
public static function kal_config_terminmenue() {
   #   Rueckgabe des HTML-Codes zu Auswahl und Konfigurierung des Terminmenues.
   #   Erlaubt sind nur die Menues 1, 6, 7.
   #
   $addon=self::this_addon;
   #
   # --- Menue anzeigen
   $str=self::kal_config_terminmenue_intern();
   #
   # --- Auslesen der gespeicherten Parameter
   $param=cms_interface::read_menue_data();
   $men    =$param['men'];
   $von    =$param['datum'];
   $anztage=$param['anztage'];
   $kid    =$param['kat_id'];
   #
   # --- ggf. alle Parameter neu speichern
   $save=$addon::kal_post_in('save');
   if(!empty($save)):
     $von    =$addon::kal_post_in($addon::KAL_AB);
     $anztage=$addon::kal_post_in($addon::KAL_ANZTAGE,'int');
     $kid    =$addon::kal_post_in($addon::KAL_KATEGORIE,'int');
     if(!empty($von)) $von=kal_termine_kalender::kal_standard_datum($von);
     cms_interface::write_menue_data('datum',  $von);
     cms_interface::write_menue_data('anztage',$anztage);
     cms_interface::write_menue_data('kat_id', $kid);
     #     Menue erneut anzeigen
     $str=self::kal_config_terminmenue_intern();
     endif;
   #
   return $str;
   }
public static function kal_show_termine() {
   #   Ausgabe des HTML-Codes zur Ausgabe von Terminen gemaess einem Terminmenue.
   #
   $addon=self::this_addon;
   $menues=$addon::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1):
      if(strpos($menues[$i]['name'],'natsmenü')>0)   $menmom=$i;  // Monatsmenue
      if(strpos($menues[$i]['name'],'erminüber')>0)  $menueb=$i;  // Terminübersicht
      if(strpos($menues[$i]['name'],'minliste')>0)   $mentel=$i;  // Terminliste
      endfor;
   $heute=kal_termine_kalender::kal_heute();
   #
   # --- im Backend: Auswahlmenue zur Speicherung der Parameter
   #                 Menuenummer, Startdatum, Anzahl Tage, Terminkategorie-Id
   if(cms_interface::backend())
     echo self::kal_config_terminmenue();
   #
   # --- Auslesen der gespeicherten Parameter
   $param=cms_interface::read_menue_data();
   if(!empty($param)):
     $men    =$param['men'];       // Menuenummer (1, 6, 7)
     $von    =$param['datum'];     // Startdatum (ggf. auch leer)
     $anztage=$param['anztage'];   // Anzahl Tage (ggf. auch leer)
     $kid    =$param['kat_id'];    // Id der Terminkategorie (=0: alle erlaubten Kategorien)
     else:
     $men    =$menmom;   // Monatsmenue
     $von    ='';
     $anztage=0;
     $kid    =0;
     endif;
   #
   # --- ggf. Parameter-Interpretation und -Auswertung
   #     Zeitraum $von - $bis (aufbereitet fuer kal_get_termine)
   if(!empty($von)) $von=kal_termine_kalender::kal_standard_datum($von);
   if($men==$menmom):   // Monatsmenue
     $von='01.'.substr($heute,3);
     $mon=intval(substr($von,3,2));
     $jah=substr($von,6);
     $mons=kal_termine_kalender::kal_monatstage($jah);
     $anztage=$mons[$mon];
     $bis=kal_termine_kalender::kal_datum_vor_nach($von,intval($anztage-1));
     endif;
   if($men==$menueb):   // Terminuebersicht
     $von='';
     $bis='';
     endif;
   if($men==$mentel):   // Terminliste
     if(empty($von)) $von=$heute;
     if($anztage<=0) $anztage=365;
     $bis=kal_termine_kalender::kal_datum_vor_nach($von,intval($anztage-1));
     endif;
   #     alle oder genau eine Kategorie
   if($kid<=0):
     $kids=cms_interface::allowed_terminkategorien();
     else:
     $kids=array(1=>$kid);
     endif;
   #
   # --- im Backend nur eine Zusammenfassung anzeigen
   if(cms_interface::backend()):
     if($kid>0):
       $kateg='Kategorie '.$addon::kal_kategorie_name($kid);
       else:
       $kateg='alle Kategorien';
       endif;
     $term=kal_termine_tabelle::kal_get_termine($von,$bis,$kids,'',1);
     #     explizite Defaultwerte bestimmen
     $expvon=$von;
     $expbis=$bis;
     if($men==$menueb):
       $expvon='01'.substr($heute,2);
       $expbis='(unbegrenzt)';
       endif;
     #     Ausgabe
     echo '
<br>
<div class="kal_basecol kal_overline">'.$menues[$men]['name'].', &nbsp; 
'.$expvon.' - '.$expbis.', &nbsp; '.$kateg.': &nbsp; &nbsp; '.count($term).' Termine</div>
';
   #
   # --- im Frontend alles anzeigen
     else:
     if($men==$mentel)   // Terminliste
       echo kal_termine_tabelle::kal_terminliste($von,$bis,$kids);
     if($men==$menmom or $men==$menueb)   // Monatsmenue bzw. Terminuebersicht
       echo kal_termine_menues::kal_menue($kid,$men);
     endif;
   }
}
?>