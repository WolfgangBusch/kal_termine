<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Februar 2019
 */
#
define ('PACKAGE', 'kal_termine');
#
class kal_termine_config {
#
#----------------------------------------- Inhaltsuebersicht
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
#----------------------------------------- Definition der Default-Daten
static public function kal_default_terminkategorien() {
   #   Rueckgabe der Default-Terminkategorien
   #
   $kat[1]='Allgemein';
   $kat[2]='Verbandsvorstand';
   $kat[3]=utf8_encode('Leistungsförderung');
   $kat[4]='Breitensport';
   return $kat;
   }
static public function kal_default_stundenleiste() {
   #   Rueckgabe der Default-Werte zur Stundenleiste
   #
   $daten[1]=array('stl'=>  8, 'name'=>'Start-Uhrzeit (ganze Zahl)');
   $daten[2]=array('stl'=> 24, 'name'=>'End-Uhrzeit (ganze Zahl)');
   $daten[3]=array('stl'=>500, 'name'=>'Gesamtbreite Zeitleiste (Anzahl Pixel)');
   return $daten;
   }
static public function kal_default_farben() {
   #   Rueckgabe der Default-Werte der RGB-Farben fuer die Kalendermenues
   #
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
static public function kal_get_default_config() {
   #   Rueckgabe der Default-Konfigurationswerte
   #   benutzte functions:
   #      self::kal_default_farben()
   #      self::kal_default_stundenleiste()
   #      self::kal_default_terminkategorien()
   #
   # --- Farben
   $farben=self::kal_default_farben();
   for($i=1;$i<=count($farben);$i=$i+1):
      $key='col'.$i;
      $sett[$key]=$farben[$i][rgb];
      endfor;
   #
   # --- Stundenleiste
   $daten=self::kal_default_stundenleiste();
   $sett['stauhrz']=$daten[1]['stl'];
   $sett['enduhrz']=$daten[2]['stl'];
   $sett['pixel']  =$daten[3]['stl'];
   #
   # --- Terminkategorien
   $kat=self::kal_default_terminkategorien();
   $sett['anzkat']=count($kat);
   for($i=1;$i<=count($kat);$i=$i+1):
      $key='kat'.$i;
      $sett[$key]=utf8_decode($kat[$i]);
      endfor;
   return $sett;
   }
#
#----------------------------------------- Setzen/Lesen der konfigurierten Daten
static public function kal_set_config($settings) {
   #   Setzen der Konfigurationsparamter gemaess gegebenem Array
   #   $settings       assoziatives Array der Konfigurationsparameter
   #                   gemaess Funktion kal_get_default_config()
   #
   #
   # --- Zunaechst alle Konfigurationsparameter loeschen
   rex_config::removeNamespace(PACKAGE);
   rex_config::save();
   #
   # --- Setzen der Parameter gemaess Vorgabe
   $keys=array_keys($settings);
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      $set=utf8_encode($settings[$key]);
      if(substr($key,0,3)!='col' and substr($key,0,3)!='kat') $set=intval($set);
      $bool=rex_config::set(PACKAGE,$key,$set);
      endfor;
   rex_config::save();
   }
static public function kal_get_config() {
   #   Rueckgabe der gesetzten Konfigurationsparameter als
   #   assoziatives Array analog Funktion kal_get_default_config()
   #
   $sett=rex_config::get(PACKAGE,NULL);
   $keys=array_keys($sett);
   for($i=0;$i<count($sett);$i=$i+1):
      $key=$keys[$i];
      if(rex_config::has(PACKAGE,$key)) $settings[$key]=utf8_decode($sett[$key]);
      endfor;
   return $settings;
   }
#
#----------------------------------------- Einlesen der Konfigurationsdaten
static public function kal_config() {
   #   Einlesen und Setzen der Konfigurationsdaten
   #   benutzte functions:
   #      self::kal_get_default_config()
   #      self::kal_get_config()
   #      self::kal_split_color($color)
   #      self::kal_read_conf_def($post,$config,$default)
   #      self::kal_set_config($settings)
   #      self::kal_write_css()
   #      self::kal_config_form($readsett)
   #
   # --- Default-Konfigurationsdaten
   $defsett=self::kal_get_default_config();
   $confsett=self::kal_get_config();
   if(count($confsett)<12):
     $keys=array_keys($defsett);
     else:
     $keys=array_keys($confsett);
     endif;
   #
   # --- Nummer des key 'anzkat'
   for($i=0;$i<count($keys);$i=$i+1) if($keys[$i]=='anzkat') $ianzk=$i;
   #
   # --- Auslesen der Daten, leere Werte werden durch die gegebenen Daten ersetzt
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      if(substr($key,0,3)=='col'):
        # --- Farben
        $kt=substr($key,3);
        $k=intval(substr($kt,0,strlen($kt)));
        $defcol =self::kal_split_color($defsett[$key]);
        $confcol=self::kal_split_color($confsett[$key]);
        $red  =self::kal_read_conf_def($_POST[r][$k],$confcol[red],  $defcol[red]);
        $green=self::kal_read_conf_def($_POST[g][$k],$confcol[green],$defcol[green]);
        $blue =self::kal_read_conf_def($_POST[b][$k],$confcol[blue], $defcol[blue]);
        $readsett[$key]='rgb('.$red.','.$green.','.$blue.')';
        else:
        # --- Sonstige
        $post=$_POST["$key"];
        $readsett[$key]=self::kal_read_conf_def($post,$confsett[$key],$defsett[$key]);
        if($i==$ianzk) $anzkat=$readsett[$key];
        endif;
      endfor;
   #
   # --- nicht mehr benoetigte Kategorien entfernen
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      if(substr($key,0,3)=='kat'):
        if(intval(substr($key,3))<=$anzkat):
          $rs[$key]=$readsett[$key];
          else:
          unset($readsett);
          $readsett=$rs;
          break;
          endif;
        endif;
      endfor;
   #
   # --- ggf. weitere Kategorien erzeugen
   $ke=$keys[$ianzk];
   $anzkat=$_POST["$ke"];
   $confanzkat=$confsett[$ke];
   if($anzkat>0 and $confanzkat>0):
     for($j=$confanzkat+1;$j<=$anzkat;$j=$j+1):
        $k=$ianzk+$j;
        $ke='kat'.$j;
        $keys[$k]=$ke;
        $readsett[$ke]='('.$ke.')';
        endfor;
     endif;
   #
   # --- Konfigurationsparameter zuruecksetzen
   $reset=$_POST['reset'];
   if(!empty($reset)) $readsett=self::kal_get_default_config();
   #
   # --- Konfigurationsparameter speichern
   $save =$_POST['save'];
   if(!empty($save) or !empty($reset)):
     ###
     if(empty($reset)):
       $keys=array_keys($readsett);
       for($i=0;$i<count($keys);$i=$i+1)
          if(substr($keys[$i],0,3)=='kat')
            $readsett[$keys[$i]]=utf8_decode($readsett[$keys[$i]]);
       endif;
     ###
     self::kal_set_config($readsett);
     #
     # --- Stylesheet-Datei ueberschreiben und nach /assets/addons/ kopieren
     self::kal_write_css();
     endif;
   #
   # --- Eingabeformular anzeigen
   self::kal_config_form($readsett);
   }
static public function kal_split_color($color) {
   #   Rueckgabe der RGB-Komponenten eines RGB-Farbstrings
   #   in Form eines assoziativen Arrays mit diesen Keys:
   #      [red]    rote Komponente
   #      [green]  gruene Komponente
   #      [blue]   blaue Komponente
   #   $color            RGB-String der Farbe
   #
   $arr=explode(',',$color);
   $red  =trim(substr($arr[0],4));
   $green=trim($arr[1]);
   $blue =trim(substr($arr[2],0,strlen($arr[2])-1));
   return array('red'=>$red, 'green'=>$green, 'blue'=>$blue);
   }
static public function kal_read_conf_def($post,$config,$default) {
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
static public function kal_config_form($readsett) {
   #   Anzeige des Formulars zur Eingabe der Konfigurationsdaten
   #   $readsett         Array der Formulardaten
   #                     Format gemaess Funktion kal_get_default_config()
   #   benutzte functions:
   #      self::kal_default_farben()
   #      self::kal_split_color($color)
   #
   $keys=array_keys($readsett);
   #
   # --- Eingabeformular, Styles
   $width1=50;
   $width2=400;
   $stc='class="form-control"';
   $std='style="width:'.$width1.'px; text-align:right; padding-right:5px;"';
   $ste='style="width:100%; padding-left:5px;"';
   $stf='width:'.$width2.'px; padding-left:5px; white-space:nowrap;';
   $stx='style="height:2.5em; font-weight:bold;"';
   $sty='style="padding-left:20px;"';
   #
   # --- Formular, Farben
   $string='
<form method="post">
<table style="background-color:inherit;">
    <tr><td '.$stx.'>Farben in den Kalendermen&uuml;s (RGB):</td>
        <td '.$sty.' align="center">R</td>
        <td '.$sty.' align="center">G</td>
        <td '.$sty.' align="center">B</td></tr>';
   # --- Formular, Erlaeuterungstexte zu den Farben
   $df=self::kal_default_farben();
   $anzcol=count($df);
   for($i=1;$i<=$anzcol;$i=$i+1) $coltext[$i]=$df[$i][name];
   #
   for($i=1;$i<=$anzcol;$i=$i+1):
      $k=$i-1;
      $kalcol=$readsett[$keys[$k]];
      $col=self::kal_split_color($kalcol);
      $stcol='black';
      if(max($col[red],$col[green],$col[blue])<=130) $stcol='white';
      $string=$string.'
    <tr><td '.$sty.'><input '.$stc.' style="'.$stf.' background-color:'.$kalcol.'; color:'.$stcol.';"
                            type="text" value="'.$coltext[$i].':" /></td>
        <td '.$sty.'><input '.$stc.' '.$std.' type="text" name="r['.$i.']" value="'.$col[red].'" /></td>
        <td '.$sty.'><input '.$stc.' '.$std.' type="text" name="g['.$i.']" value="'.$col[green].'" /></td>
        <td '.$sty.'><input '.$stc.' '.$std.' type="text" name="b['.$i.']" value="'.$col[blue].'" /></td></tr>';
      endfor;
   #
   # --- Formular, Stundenleiste
   $k=$anzcol;
   $l=$k+1;
   $m=$l+1;
   $string=$string.'
    <tr><td '.$stx.' colspan="4">
            halbgrafische Darstellung des Uhrzeit-Bereichs bei Tagesterminen:</td></tr>
    <tr><td '.$sty.'>Start-Uhrzeit (ganze Zahl):</td>
        <td '.$sty.'>
            <input '.$stc.' '.$std.' type="text" name="'.$keys[$k].'" value="'.$readsett[$keys[$k]].'" /></td>
        <td colspan="2"> &nbsp; : 00 Uhr</td></tr>
    <tr><td '.$sty.'>End-Uhrzeit (ganze Zahl):</td>
        <td  '.$sty.'>
            <input '.$stc.' '.$std.' type="text" name="'.$keys[$l].'" value="'.$readsett[$keys[$l]].'" /></td>
        <td colspan="2"> &nbsp; : 00 Uhr</td></tr>
    <tr><td '.$sty.'>Gesamtbreite (ganze Zahl):</td>
        <td  '.$sty.'>
            <input '.$stc.' '.$std.' type="text" name="'.$keys[$m].'" value="'.$readsett[$keys[$m]].'" /></td>
        <td colspan="2"> &nbsp; Pixel</td></tr>';
   #
   # --- Formular, Anzahl Terminkategorien
   $k=$anzcol+3;
   $string=$string.'
       <tr><td '.$stx.' colspan="4">Terminkategorien:</td></tr>
       <tr><td '.$sty.'>Anzahl der Terminkategorien:</td>
           <td '.$sty.' colspan="3">
               <input '.$stc.' '.$std.' type="text" name="'.$keys[$k].'" value="'.$readsett[$keys[$k]].'" /></td></tr>';
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
        $key='kat'.$i;
        $keys[$m]=$key;
        endif;
      $set=$readsett[$key];
      $string=$string.'
       <tr><td '.$sty.'>'.$lkat.'</td>
           <td '.$sty.' colspan="3">
               <input '.$stc.' '.$ste.' type="text" name="'.$key.'" value="'.$set.'" /></td></tr>';
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
        <td '.$sty.' colspan="3"><br/>
            <button class="btn btn-save"   type="submit" name="save"  value="save"  title="'.$sptit.'"> speichern </button></td></tr>
</table>
</form>';
   echo utf8_encode($string);
   }
#
#----------------------------------------- Erzeugen der Stylesheet-Datei
function kal_hatch_gen($dif,$bgcolor) {
   #   Rueckgabe eines Style-Elementes zur 45 Grad-Schraffur
   #   $dif              Streifenbreite in %
   #   $bgcolor          Hintergrundfarbe
   #
   $ii=0;
   $hatch="background-image:linear-gradient(-45deg,\n";
   $n=0;
   for($i=0;$i<100;$i=$i+$dif):
      $kk=$ii+$dif;
      if($n==0):
        $col="transparent";
        $n=1;
        else:
        $col=$bgcolor;
        $n=0;
        endif;
      $hatch=$hatch."   $col $i%, $col $kk%,\n";
      $ii=$kk;
      endfor;
   $hatch=substr($hatch,0,strlen($hatch)-2).");";
   return $hatch;
   }
static public function kal_define_css() {
   #   Rueckgabe der Quelle einer Stylesheet-Datei
   #   basierend auf den konfigurierten Farben fuer die Kalendermenues
   #   benutzte functions:
   #      self::kal_define_stundenleiste()
   #      self::kal_get_config()
   #      self::kal_hatch_gen($dif,$bgcolor)
   #
   # --- Bemassung der Stundenleiste
   $daten=self::kal_define_stundenleiste();
   $stauhr=$daten[1];
   $enduhr=$daten[2];
   $size  =$daten[3];
   $stdsiz=$daten[4];
   #
   # --- Konfigurationsdaten
   $sett=self::kal_get_config();
   $keys=array_keys($sett);
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
#hatch { '.$hatch.' }

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
.kal_form_th { vertical-align:top; text-decoration:underline; font-weight:bold; white-space:nowrap; width:110px; }
.kal_form_pad { padding-left:5px; }
.kal_form_padnowrap { padding-left:5px; white-space:nowrap; }
.kal_form_td450 { padding-left:5px; width:450px; white-space:nowrap; }
.kal_form_prom { white-space:nowrap; color:blue; }
.kal_form_msg { white-space:nowrap; color:blue; background-color:yellow; }
.kal_form_search { width:150px; padding-left:2px; padding-right:2px; }
.kal_form_block { color:green; white-space:nowrap; font-style:italic; }
.kal_form_fail { color:red; white-space:nowrap; }
.kal_form_list_th { vertical-align:top; padding-left:15px; padding-right:10px; text-align:right; font-weight:bold; }
';
   return $string;
   }
static public function kal_write_css() {
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
static public function kal_get_terminkategorien() {
   #   Rueckgabe der konfigurierten Terminkategorien
   #   als nummeriertes Array (Nummerierung ab 1)
   #   benutzte functions:
   #      self::kal_get_config()
   #
   $settings=self::kal_get_config();
   $keys=array_keys($settings);
   $k=0;
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      if(substr($key,0,3)!='kat') continue;
      $k=$k+1;
      $kat[$k]=$settings[$key];
      endfor;
   return $kat;
   }
static public function kal_get_stundenleiste() {
   #   Rueckgabe der konfigurierten Daten zur Stundenleiste als nummeriertes Array
   #   (Nummerierung ab 1). Die Gesamtlaenge ist die Summe der Netto-Inhalte der
   #   Tabellenzellen ohne Border- und Padding-Breiten.
   #   benutzte functions:
   #      self::kal_get_config()
   #
   $settings=self::kal_get_config();
   $keys=array_keys($settings);
   $k=0;
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      if(substr($key,0,3)=='sta' or substr($key,0,3)=='end' or substr($key,0,3)=='pix'):
        $k=$k+1;
        $stl[$k]=$settings[$key];
        endif;
      endfor;
   return $stl;
   }
static public function kal_define_stundenleiste() {
   #   Rueckgabe der erweiterten und modifizierten Daten fuer die konfigurierte
   #   Stundenleiste in Form eines nummerierten Arrays (Nummerierung ab 1).
   #   Die Gesamtlaenge der Leiste wird soweit reduziert, dass die Pixelanzahl
   #   fuer 1 Std. ganzzahlig ist (intval, vergl. unten). Die Gesamtlaenge ergibt
   #   sich als Vielfaches der Stundenlaenge. Die Stundenlaenge ist der Netto-Inhalt
   #   der Stunden-Tabellenzelle ohne Border- und Padding-Pixel. Die Brutto-Laenge
   #   der Stundenleiste ist daher deutlich groesser als $daten[3] angibt.
   #      $daten[1]    Startpunkt der Tagestermine (Uhrzeit, ganze Zahl)
   #      $daten[2]    Endpunkt der Tagestermine (Uhrzeit, ganze Zahl)
   #      $daten[3]    Anzahl Pixel fuer die Gesamtlaenge der Tageszeile,
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
   $daten=self::kal_get_stundenleiste();
   #
   # --- falls 1 Stunde einer gebrochenen Pixel-Anzahl entspricht, wird auf die
   #     naechst kleinere ganze Zahl reduziert; die Gesamtlaenge ergibt sich
   #     als ganzzahliges Vielfaches der Anzahl Pixel, die 1 Stunde entsprechen
   $pixel=$daten[3];
   $sizeuhr=$daten[2]-$daten[1];
   $stdsize=intval($pixel/$sizeuhr);
   if($sizeuhr*$stdsize<$pixel) $pixel=$sizeuhr*$stdsize;
   $daten=array(1=>$daten[1], 2=>$daten[2], 3=>$pixel, 4=>$stdsize);
   return $daten;
   }
}
?>
