<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Oktober 2020
*/
#
define ('PACKAGE',         $this->getPackageId());
define ('TAB_NAME',        'rex_'.PACKAGE);
define ('MODUL_MANAGE',    'Termine verwalten ('.PACKAGE.')');  // Modulbezeichnung 
define ('MODUL_DISPLAY',   'Termine anzeigen ('.PACKAGE.')');   // Modulbezeichnung 
define ('FIRST_CATEGORY',  'Allgemein');        // einzige Kategorie der Default-Konfiguration
define ('COL_PID',         'pid');
define ('COL_NAME',        'name');
define ('COL_DATUM',       'datum');
define ('COL_BEGINN',      'beginn');
define ('COL_TAGE',        'tage');
define ('COL_ENDE',        'ende');
define ('COL_WOCHEN',      'wochen');
define ('COL_AUSRICHTER',  'ausrichter');
define ('COL_ORT',         'ort');
define ('COL_LINK',        'link');
define ('COL_KOMM',        'komm');
define ('COL_KATID',       'kat_id');
define ('COL_ZEIT2',       'zeit2');
define ('COL_TEXT2',       'text2');
define ('COL_ZEIT3',       'zeit3');
define ('COL_TEXT3',       'text3');
define ('COL_ZEIT4',       'zeit4');
define ('COL_TEXT4',       'text4');
define ('COL_ZEIT5',       'zeit5');
define ('COL_TEXT5',       'text5');            // 19 REX_VALUE-Werte (COL_NAME bis COL_TEXT5)
define ('COL_ANZAHL',      20);                 // REX_VALUE-Wert fuer COL_PID bzw. fuer die Aktionen
define ('STD_BEG_UHRZEIT', 'stauhrz');
define ('STD_END_UHRZEIT', 'enduhrz');
define ('STD_ANZ_PIXEL',   'pixel');
define ('MIN_ANZ_PIXEL',   100);
define ('MAX_ANZ_PIXEL',   1000);
define ('KAL_DEFAULT_COL', 'rgb(5,90,28)');     // Default-Grundfarbe (dunkelgruen)
define ('KAL_COL',         'col');              // Name Keys der Grundfarbe
define ('KAL_KAT',         'kat');              // Namensstamm der Kategorie-Keys ('kat1', 'kat2', ...)
define ('RGB_GREY',        'rgb(150,150,150)'); // Farbe fuer Tage ausserhalb des aktuellen Monats
define ('RGB_DIFF',        25);                 // RGB-Werte-Differenz 
define ('RGB_MAX',         255-6*RGB_DIFF);
define ('RGB_BLACK_WHITE', 128);                // Schwellwert fuer schwarze/weisse Beschriftung
#
class kal_termine_config {
#
#----------------------------------------- Inhaltsuebersicht
#   Tabellenstruktur
#         kal_define_tabellenspalten()
#         kal_ausgabe_tabellenstruktur()
#   Definition der Default-Daten
#         kal_default_stundenleiste()
#         kal_default_config()
#   Setzen/Lesen der konfigurierten Daten
#         kal_set_config($settings)
#         kal_get_config()
#   Erzeugen der Stylesheet-Datei
#         kal_split_color($color)
#         kal_farben()
#         kal_hatch_gen($dif,$bgcolor)
#         kal_define_css()
#         kal_write_css()
#   Einlesen der Konfigurationsdaten
#         kal_config()
#         kal_read_conf_def($post,$config,$default)
#         kal_config_form($readsett)
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
      COL_PID   =>array('int(11) NOT NULL auto_increment', 'Termin-Id', 'Primärschlüssel', 'auto_increment'),
      COL_NAME  =>array('varchar(255) NOT NULL',      'Veranstaltung',  '',            'nicht leer'),
      COL_DATUM =>array('date NOT NULL',              'Datum',          'tt.mm.yyyy',  'nicht leer'),
      COL_BEGINN=>array('time NOT NULL',              'Uhrzeit Beginn', 'hh:mm',       ''),
      COL_TAGE  =>array('int(11) NOT NULL DEFAULT 1', 'Dauer in Tagen', '',            '>=1'),
      COL_ENDE  =>array('time NOT NULL',              'Uhrzeit Ende',   'hh:mm',       ''),
      COL_WOCHEN=>array('int(11) NOT NULL DEFAULT 0', 'Anz. wöchentl. Wiederholungen', '', ''),
      COL_AUSRICHTER=>array('varchar(500) NOT NULL',  'Ausrichter',     '',            ''),
      COL_ORT   =>array('varchar(255) NOT NULL',      'Ort',            '',            ''),
      COL_LINK  =>array('varchar(500) NOT NULL',      'Link',           '',            ''),
      COL_KOMM  =>array('text NOT NULL',              'Hinweise',       '',            ''),
      COL_KATID =>array('int(11) NOT NULL DEFAULT 1', 'Kategorie-Id',   '',            '>=1'),
      COL_ZEIT2 =>array('time NOT NULL',              'Beginn 2',       'hh:mm',       ''),
      COL_TEXT2 =>array('varchar(255) NOT NULL',      'Ereignis 2',     '',            ''),
      COL_ZEIT3 =>array('time NOT NULL',              'Beginn 3',       'hh:mm',       ''),
      COL_TEXT3 =>array('varchar(255) NOT NULL',      'Ereignis 3',     '',            ''),
      COL_ZEIT4 =>array('time NOT NULL',              'Beginn 4',       'hh:mm',       ''),
      COL_TEXT4 =>array('varchar(255) NOT NULL',      'Ereignis 4',     '',            ''),
      COL_ZEIT5 =>array('time NOT NULL',              'Beginn 5',       'hh:mm',       ''),
      COL_TEXT5 =>array('varchar(255) NOT NULL',      'Ereignis 5',     '',            ''));
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
            <h3>Tabelle \''.TAB_NAME.'\'</h3></td></tr>
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
      if($form=='date') $beme='(1)';
      if($form=='time') $beme='(2)';
      if($keys[$i]==COL_KATID) $beme='(3)';
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
    <tr valign="top">
        <td class="kal_config_pad" colspan="2">
            Texte (<tt>varchar</tt> bzw. <tt>text</tt>)
            können keine (HTML-)Formatierung enthalten.</td></tr>
    <tr valign="top">
        <td class="kal_config_pad" colspan="2">
            Mit <tt>'.COL_ZEIT2.'/'.COL_TEXT2.', ... , '.COL_ZEIT5.'/'.COL_TEXT5.'</tt>
            kann die Veranstaltung zeitlich untergliedert werden.</td></tr>
    <tr valign="top">
        <td class="kal_config_pad">(1)</td>
        <td class="kal_config_pad">Datumsformat: <tt>tt.mm.yyyy</tt>
            (wird für MySQL in das Format <tt>yyyy-mm-tt</tt> gewandelt)</td></tr>
    <tr valign="top">
        <td class="kal_config_pad">(2)</td>
        <td class="kal_config_pad">Zeitformat: <tt>hh:mm</tt>
            (wird für MySQL in das Format <tt>hh:mm:ss</tt> gewandelt)</td></tr>
    <tr valign="top">
        <td class="kal_config_pad">(3)</td>
        <td class="kal_config_pad"><tt>'.COL_KATID.'</tt> ist der Schlüssel für die
            Kategorie gemäß Konfiguration</td></tr>
</table>
';
   return $string;
   }
#
#----------------------------------------- Definition der Default-Daten
public static function kal_default_stundenleiste() {
   #   Rueckgabe der Default-Werte zur Stundenleiste
   #
   $stl=array();
   $stl[1]=array('stl'=>  0, 'name'=>'Start-Uhrzeit (ganze Zahl)');
   $stl[2]=array('stl'=> 24, 'name'=>'End-Uhrzeit (ganze Zahl)');
   $stl[3]=array('stl'=>500, 'name'=>'Gesamtbreite Zeitleiste (Anzahl Pixel)');
   return $stl;
   }
public static function kal_default_config() {
   #   Rueckgabe der Default-Konfigurationswerte
   #   benutzte functions:
   #      self::kal_default_stundenleiste()
   #
   #
   $sett=array();
   # --- Grundfarbe
   $sett[KAL_COL]=KAL_DEFAULT_COL;
   #
   # --- Stundenleiste
   $daten=self::kal_default_stundenleiste();
   $sett[STD_BEG_UHRZEIT]=$daten[1]['stl'];
   $sett[STD_END_UHRZEIT]=$daten[2]['stl'];
   $sett[STD_ANZ_PIXEL]  =$daten[3]['stl'];
   #
   # --- Terminkategorien
   $sett[KAL_KAT.'1']=FIRST_CATEGORY;
   return $sett;
   }
#
#----------------------------------------- Setzen/Lesen der konfigurierten Daten
public static function kal_set_config($settings) {
   #   Setzen der Konfigurationsparamter gemaess gegebenem Array
   #   $settings       assoziatives Array der Konfigurationsparameter
   #                   (die Keys sind wegen der Default-Konfiguration in der
   #                   richtigen Reihenfolge vorgegeben)
   #
   # --- Zunaechst alle Konfigurationsparameter loeschen
   rex_config::removeNamespace(PACKAGE);
   rex_config::save();
   #
   # --- Setzen der Parameter gemaess Vorgabe
   $lkc=strlen(KAL_COL);
   $lkk=strlen(KAL_KAT);
   $keys=array_keys($settings);
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      $set=$settings[$key];
      if($key!=KAL_COL and substr($key,0,$lkk)!=KAL_KAT) $set=intval($set);
      $bool=rex_config::set(PACKAGE,$key,$set);
      endfor;
   rex_config::save();
   }
public static function kal_get_config() {
   #   Rueckgabe der gesetzten Konfigurationsparameter als assoziatives Array
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
#
#----------------------------------------- Erzeugen der Stylesheet-Datei
public static function kal_split_color($color) {
   #   Rueckgabe der RGB-Komponenten eines RGB-Farbstrings in Form eines
   #   assoziativen Arrays mit den Keys 'red', 'green', 'blue'.
   #   $color            RGB-String der Farbe in der Form 'rgb(r,g,b)'
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
public static function kal_farben() {
   #   Rueckgabe der 9 RGB-Farben fuer die Kalendermenues als nummeriertes Array,
   #   wobei jedes Array-Element ein assoziatives Array aus RGB-Wert und
   #   Farbcharakterisierung ist:
   #      [1]             die konfigurierte Grundfarbe
   #      [2], ..., [6]   systematisch aufgehellte Variationen der Grundfarbe
   #      [7],[8]         komplementaere Farbtoene fuer den aktuellen Tag
   #      [9]             =RGB_GREY: Schrift-/Rahmenfarbe fuer Tage ausserhalb
   #                      des aktuellen Monats
   #   Die Grundfarbe ist als dunkle Schriftfarbe zu konfigurieren (RGB-Werte
   #   nicht groesser als RGB_MAX).
   #   Beispiele fuer die Farbgebung durch die Wahl der Grundfarbe:
   #      roetlich:   rgb(R,g,b)   (R ist deutlich groesser als g und b)
   #      gruenlich:  rgb(r,G,b)   (G ist deutlich groesser als r und b)
   #      blaeulich:  rgb(r,g,B)   (B ist deutlich groesser als r und g)
   #      gelblich:   rgb(R,G,b)   (R und G sind deutlich groesser als b)
   #      violett:    rgb(R,g,B)   (R und B sind deutlich groesser als g)
   #      tuerkis:    rgb(r,G,B)   (G und B sind deutlich groesser als r)
   #      grau:       rgb(R,G,B)   (R = G = B)
   #   benutzte functions:
   #      self::kal_split_color($color)
   #
   # --- Grundfarbe
   $sett=rex_config::get(PACKAGE,NULL);
   $keys=array_keys($sett);
   $base_col=KAL_DEFAULT_COL;
   for($i=0;$i<count($keys);$i=$i+1)
      if(substr($keys[$i],0,3)=='col'):
        $base_col=$sett[$keys[$i]];
        break;
        endif;
   #
   # --- Charakterisierung/Einsatz der Farben
   $cnam=array(
      1=>'dunkle Schrift-/Rahmenfarbe',
      2=>'helle Schriftfarbe, Stundenleiste',
      3=>'Hintergrundfarbe (Termine im Tages-/Wochen-/Monatsblatt)',
      4=>'Hintergrundfarbe (Sonn- und Feiertage, Such-Button)',
      5=>'Hintergrundfarbe (Suchformular), Schraffurfarbe',
      6=>'Hintergrundfarbe (Wochentage, Terminblatt, Suchformular)',
      7=>'Schrift-/Rahmenfarbe (heutiger Tag)',
      8=>'Hintergrundfarbe (heutiger Tag)',
      9=>'Schrift-/Rahmenfarbe außerhalb des aktuellen Monats');
   $anz=6;   // Anzahl der Farbtoene in der Farbe der Grundfarbe
   #
   # --- RGB-Werte der Farben
   $dol=self::kal_split_color($base_col);
   $col=array();
   $col[1]=array('rgb'=>$base_col, 'name'=>$cnam[1]);
   for($i=2;$i<=$anz;$i=$i+1):
      $dif=$i*RGB_DIFF;
      $red=intval($dol['red']  +$dif);
      $gre=intval($dol['green']+$dif);
      $blu=intval($dol['blue'] +$dif);
      $rgb='rgb('.$red.','.$gre.','.$blu.')';
      $col[$i]=array('rgb'=>$rgb, 'name'=>$cnam[$i]);
      endfor;
   #
   # --- RGB-Werte der Farben fuer den heutigen Tag
   $dif=$anz*RGB_DIFF;
   $red=intval(255-$dol['red']  -$dif);
   $gre=intval(255-$dol['green']-$dif);
   $blu=intval(255-$dol['blue'] -$dif);
   $rgb='rgb('.strval($red).','.strval($gre).','.strval($blu).')';
   $col[$anz+1]=array('rgb'=>$rgb, 'name'=>$cnam[7]);   // Rahmen
   $red=intval(255-$dol['red']);
   $gre=intval(255-$dol['green']);
   $blu=intval(255-$dol['blue']);
   $rgb='rgb('.strval($red).','.strval($gre).','.strval($blu).')';
   $col[$anz+2]=array('rgb'=>$rgb, 'name'=>$cnam[8]);   // Hintergrund
   #
   # --- RGB-Wert der Schrift-/Rahmenfarbe fuer Tage ausserhalb des aktuellen Monats
   $col[$anz+3]=array('rgb'=>RGB_GREY, 'name'=>$cnam[9]);
   return $col;
   }   
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
   #      self::kal_farben()
   #      self::kal_hatch_gen($dif,$bgcolor)
   #
   # --- Bemassung der Stundenleiste (Anzahl Pixel fuer 1 bzw. 2 Stunden)
   $daten=self::kal_define_stundenleiste();
   $stdsiz=$daten[4];
   $stdsi2=2*$stdsiz;
   #
   # --- Farben
   $farben=self::kal_farben();
   for($i=1;$i<=count($farben);$i=$i+1) $kalcol[$i]=$farben[$i]['rgb'];
   #
   # --- Streifenmuster fuer Monatstage, an denen Termine liegen
   $dif=10;     // Streifenbreite: 10%
   $hatch=self::kal_hatch_gen($dif,$kalcol[5]);
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
.kal_config_small { font-size:smaller; }
.kal_config_bgcol { width:400px; padding-left:5px; white-space:nowrap; }
.kal_form_input_text { width:450px; padding-left:2px; padding-right:2px; }
.kal_form_input_date { width:80px; padding-left:2px; padding-right:2px; }
.kal_form_input_time { width:60px; padding-left:2px; padding-right:2px; }
.kal_form_input_int  { width:40px; padding-left:2px; padding-right:2px; text-align:right; }
.kal_form_th { vertical-align:top; text-decoration:underline; font-weight:bold;
    white-space:nowrap; width:110px; }
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
#----------------------------------------- Einlesen der Konfigurationsdaten
public static function kal_read_conf_def($post,$config,$default) {
   #   Eingegebenen Parameter auflesen, und zwar so, dass eine per $_POST[...][.]
   #   eingelesene '0' als 0 und nicht als 'empty' (=undefiniert) interpretiert wird.
   #   $post           Parameterwert der Form $_POST[value][$k]
   #   $config         Konfigurierter Wert
   #   $default        Default-Wert
   #          Bestimmung des Rueckgabe-Wertes:
   #          $post:   Rueckgabe-Wert:
   #          '0'      Zahl 0
   #          leer     konfigurierter Wert oder
   #                   Zahl 0       (konfigurierter Wert == 0) oder
   #                   Default-Wert (konfigurierter Wert leer)
   #          sonst    eingelesener Wert
   #
   if($post==='0')      return 0;
   if(empty($post)):
     if($config===0)    return 0;
     if(empty($config)) return $default;
                        return $config;
     else:
                        return $post;
     endif;
   }
public static function kal_config_form($readsett) {
   #   Anzeige des Formulars zur Eingabe der Konfigurationsdaten
   #   $readsett         Array der Formulardaten im Format der Default-Konfiguration
   #   benutzte functions:
   #      self::kal_farben()
   #      self::kal_split_color($color)
   #
   # --- Formular, Farben
   $restr='<span class="kal_config_indent kal_config_small" style="font-weight:normal;">(R,G,B < '.intval(RGB_MAX+1).')</span>';
   $string='
<form method="post">
<table class="kal_table">
    <tr><td class="kal_config_th">
            Grundfarbe in den Kalendermenüs (RGB): '.$restr.'</td>
        <td class="kal_config_indent" align="center">R</td>
        <td class="kal_config_indent" align="center">G</td>
        <td class="kal_config_indent" align="center">B</td></tr>';
   # --- Formular, Erlaeuterungstexte zu den Farben
   $farben=self::kal_farben();
   $colrgb=array();
   $coltxt=array();
   for($i=1;$i<=count($farben);$i=$i+1):
      $colrgb[$i]=$farben[$i]['rgb'];
      $coltxt[$i]=$farben[$i]['name'];
      endfor;
   $col=self::kal_split_color($colrgb[1]);
   $string=$string.'
    <tr><td class="kal_config_indent">
            <input class="form-control kal_config_bgcol"
                   style="color:'.$colrgb[1].'; border:solid 1px '.$colrgb[1].';" type="text" value="'.$coltxt[1].':" /></td>
        <td class="kal_config_indent">
            <input class="form-control kal_config_number" type="text" name="red"   value="'.$col['red'].'" /></td>
        <td class="kal_config_indent">
            <input class="form-control kal_config_number" type="text" name="green" value="'.$col['green'].'" /></td>
        <td class="kal_config_indent">
            <input class="form-control kal_config_number" type="text" name="blue"  value="'.$col['blue'].'" /></td></tr>
    <tr><td class="kal_config_indent" colspan="4"><span class="kal_config_indent">daraus abgeleitete Farbtöne:</span></td></tr>';
   for($i=2;$i<=count($farben);$i=$i+1):
      $tcol=$colrgb[1];
      $col=self::kal_split_color($colrgb[$i]);
      if($i==2 or $i==7 or $i==9):
        if($i==7 or $i==9) $tcol=$colrgb[$i];
        $string=$string.'
    <tr><td class="kal_config_indent">
            <div class="kal_config_pad kal_config_small"
                 style="margin-bottom:1px; background-color:transparent; color:'.$colrgb[$i].'; border:solid 1px '.$tcol.';">'.$coltxt[$i].'</div></td>
        <td class="kal_config_number kal_config_small">'.$col['red'].' &nbsp; &nbsp; </td>
        <td class="kal_config_number kal_config_small">'.$col['green'].' &nbsp; &nbsp; </td>
        <td class="kal_config_number kal_config_small">'.$col['blue'].' &nbsp; &nbsp; </td></tr>';
        else:
        if($i==8) $tcol=$colrgb[7];
        $string=$string.'
    <tr><td class="kal_config_indent">
            <div class="kal_config_pad kal_config_small"
                 style="margin-bottom:1px; background-color:'.$colrgb[$i].'; color:'.$tcol.'; border:solid 1px '.$tcol.';">'.$coltxt[$i].'</div></td>
        <td class="kal_config_number kal_config_small">'.$col['red'].' &nbsp; &nbsp; </td>
        <td class="kal_config_number kal_config_small">'.$col['green'].' &nbsp; &nbsp; </td>
        <td class="kal_config_number kal_config_small">'.$col['blue'].' &nbsp; &nbsp; </td></tr>';
        endif;
      endfor;
   #
   # --- Formular, Stundenleiste
   $string=$string.'
    <tr><td class="kal_config_th" colspan="4"><br/>
            Darstellung des Uhrzeit-Bereichs bei Tagesterminen:</td></tr>
    <tr><td class="kal_config_indent">Start-Uhrzeit &nbsp;
            <span class="kal_config_small">(ganze Zahl)</span>:</td>
        <td class="kal_config_indent">
            <input class="form-control kal_config_number" type="text" name="'.STD_BEG_UHRZEIT.'" value="'.$readsett[STD_BEG_UHRZEIT].'" /></td>
        <td class="kal_config_small" colspan="2"> &nbsp; : 00 Uhr</td></tr>
    <tr><td class="kal_config_indent">End-Uhrzeit &nbsp;
            <span class="kal_config_small">(ganze Zahl)</span>:</td>
        <td class="kal_config_indent">
            <input class="form-control kal_config_number" type="text" name="'.STD_END_UHRZEIT.'" value="'.$readsett[STD_END_UHRZEIT].'" /></td>
        <td class="kal_config_small" colspan="2"> &nbsp; : 00 Uhr</td></tr>
    <tr><td class="kal_config_indent">Gesamtbreite &nbsp;
            <span class="kal_config_small">(ganze Zahl, zwischen '.MIN_ANZ_PIXEL.' und '.MAX_ANZ_PIXEL.')</span>:</td>
        <td class="kal_config_indent">
            <input class="form-control kal_config_number" type="text" name="'.STD_ANZ_PIXEL.'" value="'.$readsett[STD_ANZ_PIXEL].'" /></td>
        <td class="kal_config_small" colspan="2"> &nbsp; Pixel</td></tr>';
   #
   # --- Formular, Terminkategorien
   $string=$string.'
    <tr><td class="kal_config_th" colspan="4">Terminkategorien:</td></tr>';
   $anz=0;
   for($i=1;$i<=count($readsett)-4;$i=$i+1):
      $key=KAL_KAT.strval($i);
      $set=$readsett[$key];
      if(!empty($set)):
        $string=$string.'
    <tr><td class="kal_config_indent">'.$i.':</td>
        <td class="kal_config_indent" colspan="3">
            <input class="form-control kal_config_kat" type="text" name="'.$key.'" value="'.$set.'" /></td></tr>';
        $anz=$anz+1;
        endif;
      endfor;
   #
   # --- Formular, leeres Feld fuer eine neue Terminkategorie
   $i=$anz+1;
   $key=KAL_KAT.strval($i);
   $string=$string.'
    <tr><td class="kal_config_indent">
            '.$i.' <span class="kal_config_indent kal_config_small">(hier im Feld '.$i.' kann eine neue Kategorie angefügt werden)</span>:</td>
        <td class="kal_config_indent" colspan="3">
            <input class="form-control kal_config_kat" type="text" name="'.$key.'" value="" /></td></tr>
    <tr><td class="kal_config_indent kal_config_small">
            Zum Entfernen der letzten Kategorien (ab Kategorie <u>m</u>):</td>
        <td class="kal_config_indent kal_config_small" colspan="3">
            entsprechende Felder leeren (ab Feld <u>m</u>)</td></tr>';
   #
   # --- Formular, Abschluss
   $rebut='auf Defaultwerte zurücksetzen';
   $retit='Parameter '.$rebut.', Parameter und css-Stylesheet speichern';
   $spbut='speichern';
   $sptit='Parameter und css-Stylesheet '.$spbut;
   $string=$string.'
    <tr><td><br/>
            <button class="btn btn-update" type="submit" name="reset" value="reset"
                    title="'.$retit.'">'.$rebut.' und speichern</button></td>
        <td class="kal_config_indent" colspan="3"><br/>
            <button class="btn btn-save"   type="submit" name="save"  value="save"
                    title="'.$sptit.'">'.$spbut.'</button></td></tr>
</table>
</form>';
   echo $string;
   }
public static function kal_config() {
   #   Einlesen und Setzen der Konfigurationsdaten
   #   benutzte functions:
   #      self::kal_default_config()
   #      self::kal_get_config()
   #      self::kal_split_color($color)
   #      self::kal_read_conf_def($post,$config,$default)
   #      self::kal_set_config($settings)
   #      self::kal_write_css()
   #      self::kal_config_form($readsett)
   #
   # --- Konfigurationsdaten
   $defsett=self::kal_default_config();
   $nzdefs=count($defsett);
   $confsett=self::kal_get_config();
   $keys=array_keys($confsett);
   #
   # --- Anzahl der Kategorien
   $anzkat=count($keys)-4;
   #
   # --- Auslesen der Daten, leere Werte werden durch die gegebenen Daten ersetzt
   $readsett=array();
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      if($key==KAL_COL):
        # --- Farbe
        $defcol =self::kal_split_color($defsett[$key]);
        $confcol=self::kal_split_color($confsett[$key]);
        if(!empty($_POST)):
          $red=$_POST['red'];
          $gre=$_POST['green'];
          $blu=$_POST['blue'];
          else:
          $red='';
          $gre='';
          $blu='';
          endif;
        if($red<=RGB_MAX and $gre<=RGB_MAX and $blu<=RGB_MAX):
          $red=self::kal_read_conf_def($red,$confcol['red'],  $defcol['red']);
          $gre=self::kal_read_conf_def($gre,$confcol['green'],$defcol['green']);
          $blu=self::kal_read_conf_def($blu,$confcol['blue'], $defcol['blue']);
          else:
          #     zuruecksetzen, falls RGB-Wert zu gross
          if($red>RGB_MAX) $red=$confcol['red'];
          if($gre>RGB_MAX) $gre=$confcol['green'];
          if($blu>RGB_MAX) $blu=$confcol['blue'];
          echo rex_view::warning('Keiner dieser RGB-Werte darf größer als <code>'.RGB_MAX.'</code> sein!');
          endif;
        $readsett[$key]='rgb('.$red.','.$gre.','.$blu.')';
        else:
        # --- Sonstige
        $defs='';
        if($i<$nzdefs) $defs=$defsett[$key];
        $conf=$confsett[$key];
        $post='';
        if(!empty($_POST[$key])) $post=$_POST[$key];
        $sett=self::kal_read_conf_def($post,$conf,$defs);
        #     ggf. Entfernen von Kategorien
        if(substr($key,0,3)==KAL_KAT and substr($key,3)>1 and
           empty($post) and !empty($conf) and !empty($_POST['save'])) $sett=$post;
        #     Ueberpruefen der integer-Werte
        if($key==STD_BEG_UHRZEIT and ($sett<0 or $sett>23 or $sett>=$confsett[STD_END_UHRZEIT])):
          $sett=$conf;
          echo rex_view::warning('Die Start-Uhrzeit muss <code>zwischen 0 und 23 Uhr</code> und <code>vor der End-Uhrzeit</code> liegen!');
          endif;
        if($key==STD_END_UHRZEIT and ($sett<1 or $sett>24 or $sett<=$confsett[STD_BEG_UHRZEIT])):
          $sett=$conf;
          echo rex_view::warning('Die End-Uhrzeit muss <code>zwischen 1 und 24 Uhr</code> und <code>nach der Start-Uhrzeit</code> liegen!');
          endif;
        if($key==STD_ANZ_PIXEL and ($sett<MIN_ANZ_PIXEL or $sett>MAX_ANZ_PIXEL)):
          $sett=$conf;
          echo rex_view::warning('Die Gesamtbreite muss <code>zwischen '.MIN_ANZ_PIXEL.' und '.MAX_ANZ_PIXEL.' Pixel</code> liegen!');
          endif;          
        $readsett[$key]=$sett;
        endif;
      endfor;
   #
   # --- (bisher leere) neue Kategorie einlesen
   $neu=intval($anzkat+1);
   $key=KAL_KAT.strval($neu);
   $post='';
   if(!empty($_POST[$key])) $post=$_POST[$key];
   if(!empty($post)) $readsett[$key]=self::kal_read_conf_def($post,'','');
   #
   # --- Konfigurationsparameter zuruecksetzen
   $reset='';
   if(!empty($_POST['reset'])) $reset=$_POST['reset'];
   if(!empty($reset)) $readsett=$defsett;
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
#
#----------------------------------------- Auslesen der konfigurierten Daten
public static function kal_get_terminkategorien() {
   #   Rueckgabe der konfigurierten Terminkategorien als nummeriertes Array
   #   (Nummerierung ab 0). Jede Kategorie ist ein assoziatives Array mit Id und
   #   Bezeichnung der Kategorie. Die Ids bilden eine fortlaufende Zahlenfolge,
   #   beginnend mit 1.
   #   benutzte functions:
   #      self::kal_get_config()
   #
   $settings=self::kal_get_config();
   $keys=array_keys($settings);
   $k=0;
   $kat=array();
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      if(substr($key,0,3)!=KAL_KAT) continue;
      $kat[$k]['id']  =substr($key,3);
      $kat[$k]['name']=$settings[$key];
      $k=$k+1;
      endfor;
   return $kat;
   }
public static function kal_get_stundenleiste() {
   #   Rueckgabe der konfigurierten Daten zur Stundenleiste als assoziatives Array
   #   mit diesen Keys und Werten:
   #      [STD_BEG_UHRZEIT]    Start-Uhrzeit (integer)
   #      [STD_END_UHRZEIT]    End-Uhrzeit (integer)
   #      [STD_ANZ_PIXEL]      Laenge der Stundenleiste in Anzahl Pixel
   #   Die Gesamtlaenge der Stundenleiste ist die Summe der Netto-Inhalte der
   #   Tabellenzellen ohne Border- und Padding-Breiten.
   #   benutzte functions:
   #      self::kal_get_config()
   #
   $settings=self::kal_get_config();
   $stl=array();
   $stl[STD_BEG_UHRZEIT]=$settings[STD_BEG_UHRZEIT];
   $stl[STD_END_UHRZEIT]=$settings[STD_END_UHRZEIT];
   $stl[STD_ANZ_PIXEL]  =$settings[STD_ANZ_PIXEL];
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
   if($sizeuhr*$stdsize<$pixel) $pixel=intval($sizeuhr*$stdsize);
   $daten=array(1=>$stauhrz, 2=>$enduhrz, 3=>$pixel, 4=>$stdsize);
   return $daten;
   }
}
?>
