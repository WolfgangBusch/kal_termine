<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2020
 */
#
define ('PACKAGE',     $this->getPackageId());
define ('TAB_NAME',    'rex_'.$this->getPackageId());
define ('COL_PID',        'pid');
define ('COL_NAME',       'name');
define ('COL_DATUM',      'datum');
define ('COL_BEGINN',     'beginn');
define ('COL_ENDE',       'ende');
define ('COL_AUSRICHTER', 'ausrichter');
define ('COL_ORT',        'ort');
define ('COL_LINK',       'link');
define ('COL_KOMM',       'komm');
define ('COL_KATEGORIE',  'kategorie');
define ('COL_ZEIT2',      'zeit2');
define ('COL_TEXT2',      'text2');
define ('COL_ZEIT3',      'zeit3');
define ('COL_TEXT3',      'text3');
define ('COL_ZEIT4',      'zeit4');
define ('COL_TEXT4',      'text4');
define ('COL_ZEIT5',      'zeit5');
define ('COL_TEXT5',      'text5');
define ('COL_ANZAHL',     18);            // Anzahl der Konstanten mit Namen 'COL_...'
define ('STD_BEG_UHRZEIT',   'stauhrz');
define ('STD_END_UHRZEIT',   'enduhrz');
define ('STD_ANZ_PIXEL',     'pixel');
define ('STD_ANZ_KATEG',     'anzkat');
define ('KAL_COL',           'col');      // Namensstamm der Farbe-Keys ('col1', 'col2', ...)
define ('KAL_KAT',           'kat');      // Namensstamm der Kategorie-Keys ('kat2', 'kat2', ...)
#
class kal_termine_config {
#
#----------------------------------------- Inhaltsuebersicht
#   Tabellenstruktur
#         kal_define_tabellenspalten()
#         kal_ausgabe_tabellenstruktur()
#   Definition der Default-Daten
#         kal_default_terminkategorien()
#         kal_default_stundenleiste()
#         kal_default_farben()
#         kal_get_default_config()
#   Setzen/Lesen der konfigurierten Daten
#         kal_set_config($settings)
#         kal_get_config()
#   Einlesen der Konfigurationsdaten
#         kal_config()
#         kal_split_color($color)
#         kal_read_conf_def($post,$config,$default)
#         kal_config_form($readsett)
#   Erzeugen der Stylesheet-Datei
#         kal_hatch_gen($dif,$bgcolor)
#         kal_define_css()
#         kal_write_css()
#   Auslesen der konfigurierten Daten
#         kal_get_terminkategorien()
#         kal_get_stundenleiste()
#         kal_define_stundenleiste()
#
#----------------------------------------- Tabellenstruktur
public static function kal_define_tabellenspalten() {
   #   Rueckgabe der Daten zu den Kalender-Tabellenspalten als Array,
   #   - Keys und Typen der Spalten (zur Einrichtung der Tabelle)
   #   - Beschreibung und Hinweise zu den Tabellenspalten
   #
   $cols=array(
      COL_PID=>array('int(11) NOT NULL auto_increment',
         'Termin-Id', 'auto_increment', 'Primärschlüssel'),
      COL_NAME=>array('varchar(255) NOT NULL',
         'Veranstaltung', 'Kurztext', 'nicht leer'),
      COL_DATUM=>array('date NOT NULL',
         'Datum', 'tt.mm.yyyy', 'nicht leer'),
      COL_BEGINN=>array('time NOT NULL',
         'Beginn', 'hh:mm', ''),
      COL_ENDE=>array('time NOT NULL',
         'Ende', 'hh:mm', ''),
      COL_AUSRICHTER=>array('varchar(500) NOT NULL',
         'Ausrichter', 'Kurztext', ''),
      COL_ORT=>array('varchar(255) NOT NULL',
         'Ort', 'Kurztext', ''),
      COL_LINK=>array('varchar(500) NOT NULL',
         'Link', 'Kurztext', ''),
      COL_KOMM=>array('text NOT NULL',
         'Hinweise', 'Text', ''),
      COL_KATEGORIE=>array('varchar(255) NOT NULL',
         'Kategorie', 'Kurztext', 'nicht leer'),
      COL_ZEIT2=>array('time NOT NULL',
         'Beginn 2', 'hh:mm', ''),
      COL_TEXT2=>array('varchar(255) NOT NULL',
         'Ereignis 2', 'Kurztext', ''),
      COL_ZEIT3=>array('time NOT NULL',
         'Beginn 3', 'hh:mm', ''),
      COL_TEXT3=>array('varchar(255) NOT NULL',
         'Ereignis 3', 'Kurztext', ''),
      COL_ZEIT4=>array('time NOT NULL',
         'Beginn 4', 'hh:mm', ''),
      COL_TEXT4=>array('varchar(255) NOT NULL',
         'Ereignis 4', 'Kurztext', ''),
      COL_ZEIT5=>array('time NOT NULL',
         'Beginn 5', 'hh:mm', ''),
      COL_TEXT5=>array('varchar(255) NOT NULL',
         'Ereignis 5', 'Kurztext', ''));
   ###         create table:     'PRIMARY KEY (pid)'
   return $cols;
   }
public static function kal_ausgabe_tabellenstruktur() {
   #   Rueckgabe der Tabellenstrukturen
   #   benutzte functions:
   #      self::kal_define_tabellenspalten()
   #
   # --- Schleife ueber die Tabellenspalten
   $cols=self::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   $string='
<table class="kal_table">
    <tr><td colspan="5" align="center">
            <h4>Tabelle \''.TAB_NAME.'\'</h4></td></tr>
    <tr><th class="kal_config_pad kal_config_border">Spaltenname</th>
        <th class="kal_config_pad kal_config_border">Spalteninhalt</th>
        <th class="kal_config_pad kal_config_border">Format</th>
        <th class="kal_config_pad kal_config_border">Restriktionen</th>
        <th class="kal_config_pad kal_config_border">Bem.</th></tr>
';
   for($i=0;$i<count($cols);$i=$i+1):
      $inha=$cols[$keys[$i]][1];
      $arr=explode(' ',$cols[$keys[$i]][0]);
      $form=$arr[0];
      $arr=explode('(',$form);
      $form=$arr[0];
      $bedg=$cols[$keys[$i]][3];
      $beme='';
      if($form=='date') $beme='*';
      if($form=='time') $beme='**';
      $string=$string.'
    <tr><td class="kal_config_pad kal_config_border"><tt>'.$keys[$i].'</tt></td>
        <td class="kal_config_pad">'.$inha.'</td>
        <td class="kal_config_pad"><tt>'.$form.'</tt></td>
        <td class="kal_config_pad"><i>'.$bedg.'</i></td>
        <td class="kal_config_pad"><i>'.$beme.'</i></td></tr>
';
     endfor;
   $string=$string.'
</table><br/>
<table class="kal_table">
    <tr><td class="kal_config_pad">&nbsp;(*)</td>
        <td class="kal_config_pad">Datumsformat: <tt>tt.mm.yyyy</tt>
            (wird für MySQL in <tt>yyyy-mm-tt</tt> gewandelt)</td></tr>
    <tr><td class="kal_config_pad">(**)</td>
        <td class="kal_config_pad">Zeitformat: <tt>hh:mm</tt>
            (wird ins MySQL-Format <tt>hh:mm:ss</tt> gewandelt)</td></tr>
    <tr><td class="kal_config_pad"> </td>
        <td class="kal_config_pad">Kurz-/Langtexte (<tt>varchar</tt> bzw. <tt>text</tt>)
            müssen ohne HTML-Tags formuliert werden</td></tr>
    <tr><td class="kal_config_pad"> </td>
        <td class="kal_config_pad">Mit <tt>'.COL_ZEIT2.'/'.COL_TEXT2.', ... , '.COL_ZEIT5.'/'.COL_TEXT5.'</tt>
            können 4 Teilereignisse genauer beschrieben werden</td></tr>
</table>
';
   return $string;
   }
#
#----------------------------------------- Definition der Default-Daten
public static function kal_default_terminkategorien() {
   #   Rueckgabe der Default-Terminkategorien
   #
   $kat=array();
   $kat[1]='Allgemein';
   $kat[2]='Verbandsvorstand';
   $kat[3]='Leistungsförderung';
   $kat[4]='Breitensport';
   return $kat;
   }
public static function kal_default_stundenleiste() {
   #   Rueckgabe der Default-Werte zur Stundenleiste
   #
   $stl=array();
   $stl[1]=array('stl'=>  8, 'name'=>'Start-Uhrzeit (ganze Zahl)');
   $stl[2]=array('stl'=> 24, 'name'=>'End-Uhrzeit (ganze Zahl)');
   $stl[3]=array('stl'=>500, 'name'=>'Gesamtbreite Zeitleiste (Anzahl Pixel)');
   return $stl;
   }
public static function kal_default_farben() {
   #   Rueckgabe der Default-Werte der RGB-Farben fuer die Kalendermenues
   #
   $rgb=array();
   $rgb[1]=array('rgb'=>'rgb(5,90,28)',     'name'=>'dunkle Schriftfarbe');
   $rgb[2]=array('rgb'=>'rgb(70,130,110)',  'name'=>'helle Schriftfarbe, Rahmenfarbe');
   $rgb[3]=array('rgb'=>'rgb(100,160,140)', 'name'=>'dunkle Hintergrundfarbe (Termine)');
   $rgb[4]=array('rgb'=>'rgb(130,190,170)', 'name'=>'mittlere Hintergrundfarbe (Sonntage)');
   $rgb[5]=array('rgb'=>'rgb(160,220,190)', 'name'=>'helle Hintergrundfarbe (Suchformular, Schraffur)');
   $rgb[6]=array('rgb'=>'rgb(200,255,220)', 'name'=>'sehr helle Hintergrundfarbe (Wochentage, Suchformular)');
   $rgb[7]=array('rgb'=>'rgb(130,70,110)',  'name'=>'Rahmenfarbe (heutiger Tag)');
   $rgb[8]=array('rgb'=>'rgb(255,200,220)', 'name'=>'Hintergrundfarbe (heutiger Tag)');
   $rgb[9]=array('rgb'=>'rgb(150,150,150)', 'name'=>'Schrift- und Rahmenfarbe');
   return $rgb;
   }
public static function kal_get_default_config() {
   #   Rueckgabe der Default-Konfigurationswerte
   #   benutzte functions:
   #      self::kal_default_farben()
   #      self::kal_default_stundenleiste()
   #      self::kal_default_terminkategorien()
   #
   #
   $sett=array();
   # --- Farben
   $farben=self::kal_default_farben();
   for($i=1;$i<=count($farben);$i=$i+1):
      $key=KAL_COL.$i;
      $sett[$key]=$farben[$i]['rgb'];
      endfor;
   #
   # --- Stundenleiste
   $daten=self::kal_default_stundenleiste();
   $sett[STD_BEG_UHRZEIT]=$daten[1]['stl'];
   $sett[STD_END_UHRZEIT]=$daten[2]['stl'];
   $sett[STD_ANZ_PIXEL]  =$daten[3]['stl'];
   #
   # --- Terminkategorien
   $kat=self::kal_default_terminkategorien();
   $sett[STD_ANZ_KATEG]=count($kat);
   for($i=1;$i<=count($kat);$i=$i+1):
      $key=KAL_KAT.$i;
      $sett[$key]=$kat[$i];
      endfor;
   return $sett;
   }
#
#----------------------------------------- Setzen/Lesen der konfigurierten Daten
public static function kal_set_config($settings) {
   #   Setzen der Konfigurationsparamter gemaess gegebenem Array
   #   $settings       assoziatives Array der Konfigurationsparameter
   #                   gemaess Funktion kal_get_default_config()
   #                   (also die Keys auch in der richtigen Reihenfolge)
   #
   # --- Zunaechst alle Konfigurationsparameter loeschen
   rex_config::removeNamespace(PACKAGE);
   rex_config::save();
   #
   # --- Setzen der Parameter gemaess Vorgabe
   $keys=array_keys($settings);
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      $set=$settings[$key];
      if(substr($key,0,3)!=KAL_COL and substr($key,0,3)!=KAL_KAT) $set=intval($set);
      $bool=rex_config::set(PACKAGE,$key,$set);
      endfor;
   rex_config::save();
   }
public static function kal_get_config() {
   #   Rueckgabe der gesetzten Konfigurationsparameter als
   #   assoziatives Array analog Funktion kal_get_default_config()
   #
   $sett=rex_config::get(PACKAGE,NULL);
   $keys=array_keys($sett);
   $settings=array();
   for($i=0;$i<count($sett);$i=$i+1):
      $key=$keys[$i];
      if(rex_config::has(PACKAGE,$key)) $settings[$key]=$sett[$key];
      endfor;
   return $settings;
   }
public static function kal_config_keys() {
   #   Rueckgabe der Keys der Konfigurationsparameter als nummeriertes Array
   #   (Nummerierung ab 0)
   #   benutzte functions:
   #      self::kal_get_default_config()
   #      self::kal_get_config()
   #
   $defsett=self::kal_get_default_config();
   $confsett=self::kal_get_config();
   $keys=array_keys($defsett);
   $keyt=array_keys($confsett);
   $keyw=array();
   if(count($keyt)<=count($keys)):
     #     hoechstens 4 (=Default) Kategorien
     for($i=0;$i<count($keyt);$i=$i+1) $keyw[$i]=$keys[$i];
     else:
     #     mehr als 4 (=Default) Kategorien
     for($i=0;$i<count($keys);$i=$i+1) $keyw[$i]=$keys[$i];
     for($i=count($keys);$i<count($keyt);$i=$i+1) $keyw[$i]=$keyt[$i];
     endif;
   return $keyw;
   }
#
#----------------------------------------- Einlesen der Konfigurationsdaten
public static function kal_config() {
   #   Einlesen und Setzen der Konfigurationsdaten
   #   benutzte functions:
   #      self::kal_get_default_config()
   #      self::kal_get_config()
   #      self::kal_config_keys()
   #      self::kal_split_color($color)
   #      self::kal_read_conf_def($post,$config,$default)
   #      self::kal_set_config($settings)
   #      self::kal_write_css()
   #      self::kal_config_form($readsett)
   #
   # --- Konfigurationsdaten
   $defsett=self::kal_get_default_config();
   $confsett=self::kal_get_config();
   #
   # --- Keys der Konfigurationsdaten
   $keys=self::kal_config_keys();
   #
   # --- Nummer des key STD_ANZ_KATEG
   for($i=0;$i<count($keys);$i=$i+1) if($keys[$i]==STD_ANZ_KATEG) $ianzk=$i;
   #
   # --- Auslesen der Daten, leere Werte werden durch die gegebenen Daten ersetzt
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      if(substr($key,0,3)==KAL_COL):
        # --- Farben
        $kt=substr($key,3);
        $k=intval(substr($kt,0,strlen($kt)));
        $defcol =self::kal_split_color($defsett[$key]);
        $confcol=self::kal_split_color($confsett[$key]);
        if(!empty($_POST)):
          $red  =$_POST['r'][$k];
          $green=$_POST['g'][$k];
          $blue =$_POST['b'][$k];
          else:
          $red  ='';
          $green='';
          $blue ='';
          endif;
        $red  =self::kal_read_conf_def($red,  $confcol['red'],  $defcol['red']);
        $green=self::kal_read_conf_def($green,$confcol['green'],$defcol['green']);
        $blue =self::kal_read_conf_def($blue ,$confcol['blue'], $defcol['blue']);
        $readsett[$key]='rgb('.$red.','.$green.','.$blue.')';
        else:
        # --- Sonstige
        $post='';
        if(!empty($_POST)) $post=$_POST[$key];
        $readsett[$key]=self::kal_read_conf_def($post,$confsett[$key],$defsett[$key]);
        if($i==$ianzk) $anzkat=$readsett[$key];
        endif;
      endfor;
   #
   # --- nicht mehr benoetigte Kategorien entfernen
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      if(substr($key,0,3)==KAL_KAT):
        if(intval(substr($key,3))>$anzkat) break;
        endif;
      $rs[$key]=$readsett[$key];
      endfor;
   unset($readsett);
   $readsett=$rs;
   #
   # --- ggf. weitere Kategorien erzeugen
   $ke=$keys[$ianzk];
   $anzkat=0;
   if(!empty($_POST)) $anzkat=$_POST[$ke];
   $confanzkat=$confsett[$ke];
   if($anzkat>0 and $confanzkat>0):
     for($j=$confanzkat+1;$j<=$anzkat;$j=$j+1):
        $k=$ianzk+$j;
        $ke=KAL_KAT.$j;
        $keys[$k]=$ke;
        $readsett[$ke]='('.$ke.')';
        endfor;
     endif;
   #
   # --- Konfigurationsparameter zuruecksetzen
   $reset='';
   if(!empty($_POST['reset'])) $reset=$_POST['reset'];
   if(!empty($reset)) $readsett=self::kal_get_default_config();
   #
   # --- Konfigurationsparameter speichern
   $save='';
   if(!empty($_POST['save'])) $save=$_POST['save'];
   if(!empty($save) or !empty($reset)):
     self::kal_set_config($readsett);
     #
     # --- Stylesheet-Datei ueberschreiben und nach /assets/addons/ kopieren
     self::kal_write_css();
     endif;
   #
   # --- Eingabeformular anzeigen
   self::kal_config_form($readsett);
   }
public static function kal_split_color($color) {
   #   Rueckgabe der RGB-Komponenten eines RGB-Farbstrings
   #   in Form eines assoziativen Arrays mit diesen Keys:
   #      ['red']    rote Komponente
   #      ['green']  gruene Komponente
   #      ['blue']   blaue Komponente
   #   $color            RGB-String der Farbe
   #
   $arr=explode(',',$color);
   if(count($arr)>1):
     $red  =trim(substr($arr[0],4));
     $green=trim($arr[1]);
     $blue =trim(substr($arr[2],0,strlen($arr[2])-1));
     else:
     $red  ='';
     $green='';
     $blue ='';
     endif;
   return array('red'=>$red, 'green'=>$green, 'blue'=>$blue);
   }
public static function kal_read_conf_def($post,$config,$default) {
   #   Eingegebene Parameter auflesen, und zwar so,
   #   dass eine per $_POST[...][.] eingelesene '0' als 0
   #   und nicht als 'empty' interpretiert wird.
   #      '0' eingelesen:           Zahl 0 zurueck gegeben
   #      Kein Wert eingelesen:     konfigurierter Wert zurueck gegeben
   #      Konfig. Wert (auch) '0':  Zahl 0 zurueck gegeben
   #      Konfig. Wert (auch) leer: Default-Wert zurueck gegeben
   #   $post           Parameterwert der Form $_POST[value][$k]
   #   $config         Konfigurierter Wert
   #   $default        Default-Wert
   #
   $ret=$post;
   if(empty($ret)):
     # --- Parameter leer: $ret=$config/0
     if($ret==='0'):
       $ret=0;
       else:
       $ret=$config;
       endif;
     endif;
   if(empty($ret) and $ret!=0):
     # --- Parameter und $config leer: $ret=$default/0
     if($ret==='0'):
       $ret=0;
       else:
       $ret=$default;
       endif;
     endif;
     # --- Parameter und $config und $default leer: $ret=leer/0
   if($ret==='0') $ret=0;
   return $ret;
   }
public static function kal_config_form($readsett) {
   #   Anzeige des Formulars zur Eingabe der Konfigurationsdaten
   #   $readsett         Array der Formulardaten
   #                     Format gemaess Funktion kal_get_default_config()
   #   benutzte functions:
   #      self::kal_config_keys()
   #      self::kal_default_farben()
   #      self::kal_split_color($color)
   #
   $keys=self::kal_config_keys();
   #
   # --- Formular, Farben
   $string='
<form method="post">
<table class="kal_table">
    <tr><td class="kal_config_th">Farben in den Kalendermenüs (RGB):</td>
        <td class="kal_config_indent" align="center">R</td>
        <td class="kal_config_indent" align="center">G</td>
        <td class="kal_config_indent" align="center">B</td></tr>';
   # --- Formular, Erlaeuterungstexte zu den Farben
   $df=self::kal_default_farben();
   $anzcol=count($df);
   for($i=1;$i<=$anzcol;$i=$i+1) $coltext[$i]=$df[$i]['name'];
   #
   for($i=1;$i<=$anzcol;$i=$i+1):
      $k=$i-1;
      $kalcol=$readsett[$keys[$k]];
      $col=self::kal_split_color($kalcol);
      $stcol='black';
      if(max($col['red'],$col['green'],$col['blue'])<=130) $stcol='white';
      $string=$string.'
    <tr><td class="kal_config_indent">
            <input class="form-control kal_config_bgcol" style="background-color:'.$kalcol.'; color:'.$stcol.';"
                   type="text" value="'.$coltext[$i].':" /></td>
        <td class="kal_config_indent">
            <input class="form-control kal_config_number" type="text" name="r['.$i.']" value="'.$col['red'].'" /></td>
        <td class="kal_config_indent">
            <input class="form-control kal_config_number" type="text" name="g['.$i.']" value="'.$col['green'].'" /></td>
        <td class="kal_config_indent">
            <input class="form-control kal_config_number" type="text" name="b['.$i.']" value="'.$col['blue'].'" /></td></tr>';
      endfor;
   #
   # --- Formular, Stundenleiste
   $k=$anzcol;
   $l=$k+1;
   $m=$l+1;
   $string=$string.'
    <tr><td class="kal_config_th" colspan="4"><br/>
            Darstellung des Uhrzeit-Bereichs bei Tagesterminen:</td></tr>
    <tr><td class="kal_config_indent">Start-Uhrzeit (ganze Zahl):</td>
        <td class="kal_config_indent">
            <input class="form-control kal_config_number" type="text" name="'.$keys[$k].'" value="'.$readsett[$keys[$k]].'" /></td>
        <td colspan="2"> &nbsp; : 00 Uhr</td></tr>
    <tr><td class="kal_config_indent">End-Uhrzeit (ganze Zahl):</td>
        <td class="kal_config_indent">
            <input class="form-control kal_config_number" type="text" name="'.$keys[$l].'" value="'.$readsett[$keys[$l]].'" /></td>
        <td colspan="2"> &nbsp; : 00 Uhr</td></tr>
    <tr><td class="kal_config_indent">Gesamtbreite (ganze Zahl):</td>
        <td class="kal_config_indent">
            <input class="form-control kal_config_number" type="text" name="'.$keys[$m].'" value="'.$readsett[$keys[$m]].'" /></td>
        <td colspan="2"> &nbsp; Pixel</td></tr>';
   #
   # --- Formular, Anzahl Terminkategorien
   $k=$anzcol+3;
   $string=$string.'
       <tr><td class="kal_config_th" colspan="4">Terminkategorien:</td></tr>
       <tr><td class="kal_config_indent">Anzahl der Terminkategorien:</td>
           <td class="kal_config_indent" colspan="3">
               <input class="form-control kal_config_number" type="text" name="'.$keys[$k].'" value="'.$readsett[$keys[$k]].'" /></td></tr>';
   #
   # --- Formular, Terminkategorien
   $anzkat=$readsett[$keys[$k]];
   for($i=1;$i<=$anzkat;$i=$i+1):
      $lkat='';
      if($i==1) $lkat='Bezeichnung der Terminkategorien:';
      $m=$k+$i;
      $key=$keys[$m];
      # --- bei erhoehter Anzahl Kategorien
      if(empty($key)):
        $key=KAL_KAT.$i;
        $keys[$m]=$key;
        endif;
      $set=$readsett[$key];
      $string=$string.'
       <tr><td class="kal_config_indent">'.$lkat.'</td>
           <td class="kal_config_indent" colspan="3">
               <input class="form-control kal_config_kat" type="text" name="'.$key.'" value="'.$set.'" /></td></tr>';
      endfor;
   #
   # --- Formular, Abschluss
   $sptit='Parameter und css-Stylesheet speichern';
   $str='auf Defaultwerte zurücksetzen und ';
   $title=$str.'speichern';
   $retit='Parameter '.$str."\n".$sptit;
   $string=$string.'
    <tr><td><br/>
            <button class="btn btn-update" type="submit" name="reset" value="reset" title="'.$retit.'">'.$title.'</button></td>
        <td class="kal_config_indent" colspan="3"><br/>
            <button class="btn btn-save"   type="submit" name="save"  value="save"  title="'.$sptit.'"> speichern </button></td></tr>
</table>
</form>';
   echo $string;
   }
#
#----------------------------------------- Erzeugen der Stylesheet-Datei
public static function kal_hatch_gen($dif,$bgcolor) {
   #   Rueckgabe eines Style-Elementes zur 45 Grad-Schraffur
   #   $dif              Streifenbreite in %
   #   $bgcolor          Hintergrundfarbe
   #
   $ii=0;
   $hatch='#hatch { background-image:linear-gradient(-45deg,';
   $n=0;
   for($i=0;$i<100;$i=$i+$dif):
      $kk=$ii+$dif;
      if($n==0):
        $col='transparent';
        $n=1;
        else:
        $col=$bgcolor;
        $n=0;
        endif;
      $hatch=$hatch.'
   '.$col.' '.$i.'%, '.$col.' '.$kk.'%,';
      $ii=$kk;
      endfor;
   $hatch=substr($hatch,0,strlen($hatch)-1).'); }';
   return $hatch;
   }
public static function kal_define_css() {
   #   Rueckgabe der Quelle einer Stylesheet-Datei
   #   basierend auf den konfigurierten Farben fuer die Kalendermenues
   #   benutzte functions:
   #      self::kal_define_stundenleiste()
   #      self::kal_get_config()
   #      self::kal_config_keys()
   #      self::kal_hatch_gen($dif,$bgcolor)
   #
   # --- Bemassung der Stundenleiste
   $daten=self::kal_define_stundenleiste();
   $stauhr=$daten[1];
   $enduhr=$daten[2];
   $size  =$daten[3];
   $stdsiz=$daten[4];
   #
   # --- Konfigurations-Daten und -Keys
   $sett=self::kal_get_config();
   $keys=self::kal_config_keys();
   $k=0;
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      $val=$sett[$key];
      if(substr($val,0,4)!='rgb(') continue;
      $k=$k+1;
      $kalcol[$k]=$sett[$key];
      endfor;
   #
   # --- Streifenmuster fuer Monatstage, an denen Termine liegen
   $dif=10;     // Streifenbreite: 10%
   $hatch=self::kal_hatch_gen($dif,$kalcol[5]);
   #
   # --- Anzahl Pixel fuer 1 bzw. 2 Stunden
   $daten=self::kal_define_stundenleiste();
   $stdsiz=$daten[4];
   $stdsi2=2*$stdsiz;
   #
   $string='/*   K a l e n d e r   */

/*   Tabelle, Rahmen   */
.kal_table { background-color:inherit; }
.kal_border { padding:1px; background-color:inherit; border-spacing:2px;
   border-collapse:separate; border-radius:0.25em; border:solid 1px '.$kalcol[1].'; }

/*   Tag im Monatsmenue   */
.kal_tag { width:20px; line-height:1.5em; padding:2px; text-align:right;
   border-radius:0.25em; }
.kal_wotag { background-color:'.$kalcol[6].'; border:solid 1px '.$kalcol[1].';
   color:'.$kalcol[1].'; }
.kal_sotag { background-color:'.$kalcol[4].'; border:solid 1px '.$kalcol[1].';
   color:'.$kalcol[1].'; }
.kal_hetag { background-color:'.$kalcol[8].'; border:solid 1px '.$kalcol[7].';
   color:'.$kalcol[7].'; }
.kal_vntag { background-color:transparent;    border:solid 1px '.$kalcol[9].';
   color:'.$kalcol[9].'; }
'.$hatch.'

/*   Tagesstreifen im Monats-/Wochen-/Tagesblatt   */
.kal_pix { max-width:'.$stdsiz.'px; min-width:'.$stdsiz.'px; line-height:0.2em;
   padding-left:1px; padding-right:1px; border-spacing:2px; border-width:1px; }
.kal_pixn { border-left:solid 1px '.$kalcol[2].'; }
.kal_pixr { border-left:solid 1px '.$kalcol[2].'; border-right:solid 1px '.$kalcol[2].'; }
.kal_std1 { max-width:'.$stdsiz.'px; min-width:'.$stdsiz.'px; padding:2px;
   text-align:right;  color:'.$kalcol[2].'; }
.kal_std2 { max-width:'.$stdsi2.'px; min-width:'.$stdsi2.'px; padding:2px;
   text-align:center; color:'.$kalcol[2].'; }
.kal_strtag { line-height:1.5em; padding-left:1px; padding-right:1px;
   border-spacing:2px; border-width:1px; border-radius:0.25em; }
.kal_womtag { background-color:'.$kalcol[6].'; border:solid 1px '.$kalcol[1].'; }
.kal_somtag { background-color:'.$kalcol[4].'; border:solid 1px '.$kalcol[1].'; }
.kal_hemtag { background-color:'.$kalcol[8].'; border:solid 1px '.$kalcol[7].'; }
.kal_termintag { white-space:nowrap; font-size:smaller; overflow-x:hidden;
   background-color:'.$kalcol[3].'; color:'.$kalcol[1].'; }

/*   Termin-Parameter im Terminblatt   */
.kal_termv { max-width:400px; line-height:1.5em; padding-left:5px; padding-right:5px;
   color:'.$kalcol[1].'; }
.kal_termval { background-color:'.$kalcol[6].'; border:solid 1px '.$kalcol[1].';
   border-radius:0.25em; }

/*   Kopf des Suchmenues   */
.kal_search { line-height:1.5em; padding:1px 5px 1px 5px; font-size:smaller;
   white-space:nowrap; text-align:center; color:'.$kalcol[1].';
   border-radius:0.25em; border:solid 1px '.$kalcol[1].'; }
.kal_search_td { width:100px; background-color:'.$kalcol[6].'; }
.kal_search_th { width:10px; font-weight:bold; background-color:'.$kalcol[5].'; }
.kal_option { padding:1px; font-size:1.0em; color:'.$kalcol[1].';
   background-color:'.$kalcol[5].'; }
.kal_select { border-radius:0.25em; border:solid 1px '.$kalcol[1].'; }
.kal_form { font-size:1.0em; color:'.$kalcol[1].';
   border-radius:0.25em; border:solid 1px '.$kalcol[1].'; }
.kal_input { padding:2px 5px 3px 5px; background-color:'.$kalcol[5].'; }
.kal_submit { padding:0px 5px 0px 5px; font-weight:bold; background-color:'.$kalcol[4].'; }
.kal_transparent { margin:0px; padding:0px; border:none; font-size:inherit;
   font-weight:inherit; color:inherit; background-color:transparent; }
.kal_linkbutton { cursor:pointer; }

/*   Text-Charakteristika und -farben   */
.kal_txt_titel { min-width:150px; padding:2px; white-space:nowrap; font-weight:bold;
   font-size:1.2em; text-align:center; color:'.$kalcol[1].'; }
.kal_txt1 { padding:2px; color:'.$kalcol[1].'; }
.kal_txtb1 { padding:2px; font-weight:bold; color:'.$kalcol[1].'; }
.kal_txt2 { padding:2px; color:'.$kalcol[2].'; }
.kal_txtb2 { padding:2px; font-weight:bold; color:'.$kalcol[2].'; }
.kal_success { color:blue; }
.kal_fail { color:red; }

/*   Formulare im Backend   */
.kal_install_menue { padding-left:20px; vertical-align:top; }
.kal_install_number { width:50px; padding-right:5px; text-align:right; }
.kal_install_termlist { padding-left:20px; white-space:nowrap; }
.kal_config_pad { padding-left:5px; padding-right:5px; white-space:nowrap; }
.kal_config_border { border:solid 1px grey; }
.kal_config_indent { padding-left:20px; }
.kal_config_th { height:2.5em; font-weight:bold; }
.kal_config_kat { width:100%; padding-left:5px; }
.kal_config_number { width:50px; padding-right:5px; text-align:right; }
.kal_config_bgcol { width:400px; padding-left:5px; white-space:nowrap; }
.kal_form_input_text { width:450px; padding-left:2px; padding-right:2px; }
.kal_form_input_date { width:80px; padding-left:2px; padding-right:2px; }
.kal_form_input_time { width:60px; padding-left:2px; padding-right:2px; }
.kal_form_th { vertical-align:top; text-decoration:underline; font-weight:bold; white-space:nowrap; width:110px; }
.kal_form_pad { padding-left:10px; }
.kal_form_nowrap { white-space:nowrap; }     
.kal_form_td450 { padding-left:5px; width:450px; white-space:nowrap; }
.kal_form_prom { white-space:nowrap; color:blue; }
.kal_form_msg { white-space:nowrap; color:blue; background-color:yellow; }
.kal_form_search { width:150px; padding-left:2px; padding-right:2px; }
.kal_form_block { color:green; white-space:nowrap; font-style:italic; }
.kal_form_fail { color:red; white-space:nowrap; }
.kal_form_list_th { vertical-align:top; padding-left:15px; padding-right:10px;
    text-align:right; font-weight:bold; white-space:nowrap; }
';
   return $string;
   }
public static function kal_write_css() {
   #   Schreiben der Stylesheet-Datei in den assets-Ordner des AddOns
   #   und Kopieren derselben in den Ordner /assets/addons/AddOn/
   #   benutzte functions:
   #      self::kal_define_css()
   #
   # --- Erzeugen des Stylesheet-Textes
   $buffer=self::kal_define_css();
   #
   # --- ggf. Erzeugen des Ordners
   $dir=rex_path::addon(PACKAGE).'assets';
   if(!file_exists($dir)) mkdir($dir);
   #
   # --- Schreiben der Datei
   $file=$dir.'/'.PACKAGE.'.css';
   $handle=fopen($file,'w');
   fwrite($handle,$buffer);
   fclose($handle);
   #
   # --- Kopieren in den Ordner /assets/addons/AddOn/
   $copydir=rex_path::addonAssets(PACKAGE);
   if(!file_exists($copydir)) mkdir($copydir);
   $copyfile=$copydir.PACKAGE.'.css';
   copy($file,$copyfile);
   }
#
#----------------------------------------- Auslesen der konfigurierten Daten
public static function kal_get_terminkategorien() {
   #   Rueckgabe der konfigurierten Terminkategorien
   #   als nummeriertes Array (Nummerierung ab 1)
   #   benutzte functions:
   #      self::kal_config_keys()
   #      self::kal_config_keys()
   #
   $settings=self::kal_get_config();
   $keys=self::kal_config_keys();
   $k=0;
   $kat=array();
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      if(substr($key,0,3)!=KAL_KAT) continue;
      $k=$k+1;
      $kat[$k]=$settings[$key];
      endfor;
   return $kat;
   }
public static function kal_get_stundenleiste() {
   #   Rueckgabe der konfigurierten Daten zur Stundenleiste als assoziatives Array:
   #      $stl[STD_BEG_UHRZEIT]    Start-Uhrzeit (integer)
   #      $stl[STD_END_UHRZEIT]    End-Uhrzeit (integer)
   #      $stl[STD_ANZ_PIXEL]      Laenge der Stundenleiste in Anzahl Pixel
   #   Die Gesamtlaenge der Stundenleiste ist die Summe der Netto-Inhalte der
   #   Tabellenzellen ohne Border- und Padding-Breiten.
   #   benutzte functions:
   #      self::kal_get_default_config()
   #      self::kal_get_config()
   #
   $defconf=self::kal_get_default_config();
   $keys=array_keys($defconf);
   $settings=self::kal_get_config();
   $stl=array();
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      if($key==STD_BEG_UHRZEIT) $stl[STD_BEG_UHRZEIT]=$settings[$key];
      if($key==STD_END_UHRZEIT) $stl[STD_END_UHRZEIT]=$settings[$key];
      if($key==STD_ANZ_PIXEL  ) $stl[STD_ANZ_PIXEL]  =$settings[$key];
      endfor;
   return $stl;
   }
public static function kal_define_stundenleiste() {
   #   Rueckgabe der erweiterten und modifizierten Daten fuer die konfigurierte
   #   Stundenleiste in Form eines nummerierten Arrays (Nummerierung ab 1).
   #   Die Gesamtlaenge der Leiste wird soweit reduziert, dass die Pixelanzahl
   #   fuer 1 Std. ganzzahlig ist (intval, vergl. unten). Die Gesamtlaenge ergibt
   #   sich als Vielfaches der Stundenlaenge. Die Stundenlaenge ist der Netto-Inhalt
   #   der Stunden-Tabellenzelle ohne Border- und Padding-Pixel. Die Brutto-Laenge
   #   der Stundenleiste ist daher deutlich groesser als $daten[3] angibt.
   #      $daten[1]    Startpunkt der Tagestermine (Uhrzeit, ganze Zahl)
   #      $daten[2]    Endpunkt der Tagestermine (Uhrzeit, ganze Zahl)
   #      $daten[3]    Anzahl Pixel (ganze Zahl) fuer die Gesamtlaenge der Tageszeile,
   #                   falls 1 Stunde einer gebrochenen Pixel-Anzahl entspricht,
   #                   wird die gemaess Konfiguration vorgegebene Gesamtlaenge
   #                   so verkleinert, dass 1 Stunde einer ganzzahligen
   #                   Pixelanzahl entspricht
   #                   Beispiel: Tagestermine von 8 - 24 Uhr
   #                             mit max 500 Pixel
   #                             (d.h. max. 500 Pixel fuer 16 Stunden)
   #                             1 Stunde: intval(500/16)=intval(31.4=31
   #                             16 Stunden: 16*31=496 Pixel
   #      $daten[4]    Anzahl Pixel fuer 1 Stunde in der Tageszeile
   #   benutzte functions:
   #      self::kal_get_stundenleiste()
   #
   $stl=self::kal_get_stundenleiste();
   $stauhrz=$stl[STD_BEG_UHRZEIT];
   $enduhrz=$stl[STD_END_UHRZEIT];
   $pixel  =$stl[STD_ANZ_PIXEL];
   #
   # --- falls 1 Stunde einer gebrochenen Pixel-Anzahl entspricht, wird auf die
   #     naechst kleinere ganze Zahl reduziert; die Gesamtlaenge ergibt sich
   #     als ganzzahliges Vielfaches der Anzahl Pixel, die 1 Stunde entsprechen
   $sizeuhr=intval($enduhrz-$stauhrz);
   $stdsize=intval($pixel/$sizeuhr);
   if($sizeuhr*$stdsize<$pixel) $pixel=$sizeuhr*$stdsize;
   $daten=array(1=>$stauhrz, 2=>$enduhrz, 3=>$pixel, 4=>$stdsize);
   return $daten;
   }
}
?>
