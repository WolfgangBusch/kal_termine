<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Juni 2021
*/
define ('PACKAGE',         $this->getPackageId());
define ('TAB_NAME',        'rex_'.PACKAGE);
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
define ('KAL_COL',         'col');              // Name des Keys der Grundfarbe
define ('KAL_KAT',         'kat');              // Namensstamm der Kategorie-Keys ('kat1', 'kat2', ...)
define ('RGB_GREY',        'rgb(150,150,150)'); // Farbe fuer Tage ausserhalb des aktuellen Monats
define ('RGB_DIFF',        25);                 // RGB-Werte-Differenz 
define ('RGB_MAX',         255-6*RGB_DIFF);
define ('RGB_BLACK_WHITE', 128);                // Schwellwert fuer schwarze/weisse Beschriftung
define ('SPIEL_KATID',     99990);              // Kategorie-Ids der Spieldaten beginnen bei 99991
define ('KAL_MOBILE',      35);                 // Stylesheet-Variante Smartphone 'max-width:...em'
define ('CSS_TERMBLATT',   'kal_terminblatt');  // Stylesheet Terminblatt
define ('CSS_MONMENUE',    'kal_monatsmenue');  // Stylesheet Monatsmenue
define ('CSS_MWTBLATT',    'kal_mowotablatt');  // Stylesheet Mo-/Wo-/Ta-Blatt
define ('CSS_SUCH',        'kal_such');         // Stylesheet Suchmenue
define ('CSS_EINFORM',     'kal_eingabeform');  // Stylesheet Eingabeformulare
define ('CSS_CONFIG',      'kal_config');       // Stylesheet Konfigurationsformulare
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
      COL_PID   =>array('int(11) NOT NULL auto_increment', 'Termin-Id',  'Primärschlüssel', 'auto_increment'),
      COL_NAME  =>array('varchar(255) NOT NULL',      'Veranstaltung',   '',            'nicht leer'),
      COL_DATUM =>array('date NOT NULL',              'Datum',           'tt.mm.yyyy',  'nicht leer'),
      COL_BEGINN=>array('time NOT NULL',              'Uhrzeit Beginn',  'hh:mm',       ''),
      COL_TAGE  =>array('int(11) NOT NULL DEFAULT 1', 'Dauer in Tagen',  '',            '>=1'),
      COL_ENDE  =>array('time NOT NULL',              'Uhrzeit Ende',    'hh:mm',       ''),
      COL_WOCHEN=>array('int(11) NOT NULL DEFAULT 0', 'Wiederholungen',  '',            ''),
      COL_AUSRICHTER=>array('varchar(500) NOT NULL',  'Ausrichter',      '',            ''),
      COL_ORT   =>array('varchar(255) NOT NULL',      'Ort',             '',            ''),
      COL_LINK  =>array('varchar(500) NOT NULL',      'Link',            '',            ''),
      COL_KOMM  =>array('text NOT NULL',              'Hinweise',        '',            ''),
      COL_KATID =>array('int(11) NOT NULL DEFAULT 1', 'Kategorie-Id',    '',            '>=1'),
      COL_ZEIT2 =>array('time NOT NULL',              'Uhrzeit 2 Beginn','hh:mm',       ''),
      COL_TEXT2 =>array('varchar(255) NOT NULL',      'Teil 2',          '',            ''),
      COL_ZEIT3 =>array('time NOT NULL',              'Uhrzeit 3 Beginn','hh:mm',       ''),
      COL_TEXT3 =>array('varchar(255) NOT NULL',      'Teil 3',          '',            ''),
      COL_ZEIT4 =>array('time NOT NULL',              'Uhrzeit 4 Beginn','hh:mm',       ''),
      COL_TEXT4 =>array('varchar(255) NOT NULL',      'Teil 4',          '',            ''),
      COL_ZEIT5 =>array('time NOT NULL',              'Uhrzeit 5 Beginn','hh:mm',       ''),
      COL_TEXT5 =>array('varchar(255) NOT NULL',      'Teil 5',          '',            ''));
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
<div class="'.CSS_CONFIG.'">
<table class="kal_table">
    <tr><td class="indent" colspan="5"><h4>Tabelle \''.TAB_NAME.'\'</h4></td></tr>
    <tr><td class="indent head">Spaltenname</td>
        <td class="indent head">Spalteninhalt</td>
        <td class="indent head">SQL-Format</td>
        <td class="indent head">Restriktionen</td>
        <td class="indent head">Hinweis</td></tr>
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
      if($keys[$i]==COL_WOCHEN) $beme='(*)';
      if($keys[$i]==COL_KATID) $beme='(**)';
      $string=$string.'
    <tr><td class="indent"><tt>'.$keys[$i].'</tt></td>
        <td class="indent">'.$inha.'</td>
        <td class="indent"><tt>'.$form.'</tt></td>
        <td class="indent"><i>'.$bedg.'</i></td>
        <td class="indent"><i>'.$beme.'</i></td></tr>
';
     endfor;
   $string=$string.'
</table><br/>
<table class="kal_table">
    <tr><td class="indent head" colspan="2">Hinweise:</td></tr>
    <tr><td class="indent">(1)</td>
        <td class="indent">Datumsformat: <tt>tt.mm.yyyy</tt>
            (wird für MySQL in das Format <tt>yyyy-mm-tt</tt> gewandelt)</td></tr>
    <tr><td class="indent">(2)</td>
        <td class="indent">Zeitformat: <tt>hh:mm</tt>
            (wird für MySQL in das Format <tt>hh:mm:ss</tt> gewandelt)</td></tr>
    <tr><td class="indent">(*)</td>
        <td class="indent"><tt>'.COL_WOCHEN.'</tt> gibt an, wie oft der
            betreffende Termin wöchentlich wiederkehrt</td></tr>
    <tr><td class="indent">(**)</td>
        <td class="indent"><tt>'.COL_KATID.'</tt> ist der Schlüssel für die
            Kategorie gemäß Konfiguration</td></tr>
    <tr><td class="indent" colspan="2">
            Mit <tt>'.COL_ZEIT2.'/'.COL_TEXT2.', ... , '.COL_ZEIT5.'/'.COL_TEXT5.'</tt>
            kann die Veranstaltung zeitlich untergliedert werden.</td></tr>
    <tr><td class="indent" colspan="2">
            Texte (<tt>varchar</tt> bzw. <tt>text</tt>)
            können keine (HTML-)Formatierung enthalten.</td></tr>
    <tr><td class="indent" colspan="2">
            Wöchentlich wiederkehrende Termine können <tt>nicht zugleich mehrtägig</tt>
            sein.</td></tr>
</table>
</div>
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
      1=>'dunkle Schrift-/Rahmenfarbe, Grundfarbe (<b>R,G,B &le; '.RGB_MAX.'</b>)',
      2=>'helle Schriftfarbe, Stundenleiste',
      3=>'Hintergrundfarbe (Termine im Tages-/Wochen-/Monatsblatt)',
      4=>'Hintergrundfarbe (Sonn- und Feiertage, Such-Button)',
      5=>'Hintergrundfarbe (Suchformular), Schraffurfarbe',
      6=>'Hintergrundfarbe (Wochentage, Terminblatt, Suchformular)',
      7=>'Schrift-/Rahmenfarbe (heutiger Tag)',
      8=>'Hintergrundfarbe (heutiger Tag)',
      9=>'neutrale Schrift-/Rahmenfarbe (nicht abgeleitet)');
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
   # --- Farben
   $farben=self::kal_farben();
   for($i=1;$i<=count($farben);$i=$i+1) $kalcol[$i]=$farben[$i]['rgb'];
   #
   # --- Streifenmuster fuer Monatstage, an denen Termine liegen
   $dif=10;     // Streifenbreite: 10%
   $hatch=self::kal_hatch_gen($dif,$kalcol[4]);
   #
   # --- CSS-Formate
   $tl_width=intval(0.6*KAL_MOBILE);
   $monwidth  =8.5;
   $tbl_width =6;
   $such_width=5;
   $form_box='padding:0.25em; border-collapse:separate; border-spacing:0.25em;';
   $form_col1=$form_box.'
    color:'.$kalcol[1].'; background-color:transparent;
    border:solid 1px '.$kalcol[1].'; border-radius:0.25em;';
   $form_col2=$form_box.'
    color:'.$kalcol[2].'; background-color:transparent;
    border:solid 1px '.$kalcol[2].'; border-radius:0.25em;';
   $form_col3=$form_box.'
    color:'.$kalcol[1].'; background-color:'.$kalcol[3].';
    border:solid 1px '.$kalcol[1].'; border-radius:0.25em;';
   $form_col4=$form_box.'
    color:'.$kalcol[1].'; background-color:'.$kalcol[4].';
    border:solid 1px '.$kalcol[1].'; border-radius:0.25em;';
   $form_col5=$form_box.'
    color:'.$kalcol[1].'; background-color:'.$kalcol[5].';
    border:solid 1px '.$kalcol[1].'; border-radius:0.25em;';
   $form_col6=$form_box.'
    color:'.$kalcol[1].'; background-color:'.$kalcol[6].';
    border:solid 1px '.$kalcol[1].'; border-radius:0.25em;';
   $form_col7=$form_box.'
    color:'.$kalcol[7].'; background-color:transparent;
    border:solid 1px '.$kalcol[7].'; border-radius:0.25em;';
   $form_col8=$form_box.'
    color:'.$kalcol[7].'; background-color:'.$kalcol[8].';
    border:solid 1px '.$kalcol[7].'; border-radius:0.25em;';
   $form_col9=$form_box.'
    color:'.$kalcol[9].'; background-color:transparent;
    border:solid 1px '.$kalcol[9].'; border-radius:0.25em;';
   #
   # --- Stylesheet-Datei
   $string='/*   T e r m i n k a l e n d e r   */

/*   Allgemeines   */
.kal_linkbutton { cursor:pointer; text-align:left; }
.kal_boldbig { font-size:1.2em; font-weight:bold; }
.kal_transparent { margin:0; padding:0; border:none; color:inherit; background-color:transparent; }
.kal_table { background-color:inherit; }
.kal_box { '.$form_col1.' }
.kal_rotate { margin:0 0 0.1em 0; text-align:center; font-size:1.2em; font-weight:bold;
    display:inline-block; transform:rotate(120deg); }
.kal_100pro { width:100%; }
.kal_basecol { color:'.$kalcol[1].'; }
.kal_fail { color:red; }
.kal_msg { '.$form_col6.' }
.kal_block_uebernehmen { color:'.$kalcol[2].'; font-weight:bold; font-style:italic; }

/*   Farbkombinationen fuer Kalenderfelder   */
.kal_col1 { '.$form_col1.' }
.kal_col2 { '.$form_col2.' }
.kal_col3 { '.$form_col3.' }
.kal_col4 { '.$form_col4.' }
.kal_col5 { '.$form_col5.' }
.kal_col6 { '.$form_col6.' }
.kal_col7 { '.$form_col7.' }
.kal_col8 { '.$form_col8.' }
.kal_col9 { '.$form_col9.' }

/*   Terminblatt   */
.'.CSS_TERMBLATT.' { }
.'.CSS_TERMBLATT.' th { max-width:'.$tbl_width.'em; padding:0.25em; vertical-align:top; text-align:left; }
.'.CSS_TERMBLATT.' td { padding:0.25em; vertical-align:top; text-align:left; }
.'.CSS_TERMBLATT.' .kopf { font-size:1.2em; font-weight:bold; color:'.$kalcol[1].'; }
.'.CSS_TERMBLATT.' .kopf { font-size:1.2em; font-weight:bold; color:'.$kalcol[1].'; }

/*   Terminliste   */
.termlist_th { padding:0.3em 0.5em 0.3em 0; text-align:right; white-space:nowrap;
    font-weight:bold; vertical-align:top; border-top:solid 1px transparent; border-bottom:solid 1px transparent; }
.termlist_td { padding:0.3em 0 0.3em 0.5em; border-top:solid 1px transparent; border-bottom:solid 1px transparent; }
.termlist_textattr { font-weight:normal; }
.termlist_ort::before { content:" ("; }
.termlist_ort::after  { content:")"; }
.termlist_ausrichter::before { content:", Ausrichter: "; }
.termlist_komm::before { content:"\A"; white-space:pre; }
@media screen and (max-width:'.KAL_MOBILE.'em) {
    .termlist_th { float:left; padding:0.3em 0 0.3em 0; text-align:left; white-space:normal; }
    .termlist_td { float:left; padding:0.3em 0 0.3em 1em; min-width:'.$tl_width.'em; }
    }

/*   Monatsmenue   */
.'.CSS_MONMENUE.' { }
.'.CSS_MONMENUE.' .padl { padding:0 0.5em 0 0; }
.'.CSS_MONMENUE.' .padr { padding:0 0 0 0.5em; }
.'.CSS_MONMENUE.' .left  { float:left; }
.'.CSS_MONMENUE.' .right { float:right; }
.'.CSS_MONMENUE.' .center { text-align:center; }
.'.CSS_MONMENUE.' .rechts { text-align:right; }
.'.CSS_MONMENUE.' .width { min-width:'.$monwidth.'em; max-width:'.$monwidth.'em; }
.'.CSS_MONMENUE.' .wot { padding:0.25em; text-align:center; color:'.$kalcol[2].'; }
.'.CSS_MONMENUE.' .kalenderwoche  { padding:0.25em; text-align:right;  color:'.$kalcol[2].'; }
'.$hatch.'

/*   Monats-/Wochen-/Tagesblatt, Stundenleiste, Terminfeld   */
.'.CSS_MWTBLATT.' { }
.'.CSS_MWTBLATT.' hr { margin:0; padding:0; border:none; background-color:inherit; }
.'.CSS_MWTBLATT.' .vis_leiste { visibility:visible; }
.'.CSS_MWTBLATT.' .zeitenzeile { height:0; visibility:hidden; }
.'.CSS_MWTBLATT.' .left  { float:left; }
.'.CSS_MWTBLATT.' .right { float:right; }
.'.CSS_MWTBLATT.' .center { text-align:center; }
.'.CSS_MWTBLATT.' .pad0 { padding:0 0.5em 0 0.5em; color:'.$kalcol[1].'; }
.'.CSS_MWTBLATT.' .pad1 { padding:0 0.25em 0 0.25em; vertical-align:top; text-align:right;
    white-space:nowrap; font-weight:bold; color:'.$kalcol[2].'; }
.'.CSS_MWTBLATT.' .tag { width:100%; padding:0; line-height:1.5em; }
@media screen and (max-width:'.KAL_MOBILE.'em) {
    .'.CSS_MWTBLATT.' hr { margin:0.25em; padding:1px; border:solid 1px inherit; 
        background-color:'.$kalcol[3].'; }
    .'.CSS_MWTBLATT.' .vis_leiste { visibility:collapse; }
    .'.CSS_MWTBLATT.' .zeitenzeile { height:inherit; visibility:visible; }
    .'.CSS_MWTBLATT.' .pad1 { padding:0 0.25em 0 0.25em; vertical-align:top; text-align:right; white-space:normal;
        white-space:normal; font-weight:normal; color:'.$kalcol[2].'; }
    }
/*   Stundenleiste   */
.'.CSS_MWTBLATT.' .width1 { min-width:1em; max-width:1em; }
.'.CSS_MWTBLATT.' .width2 { min-width:2em; max-width:2em; }
.'.CSS_MWTBLATT.' .lineal { line-height:0.25em; border-left:solid 1px '.$kalcol[2].'; }
.'.CSS_MWTBLATT.' .center { text-align:center; color:'.$kalcol[2].'; }
.'.CSS_MWTBLATT.' .right  { text-align:right;  color:'.$kalcol[2].'; }
/*   Terminfeld   */
.'.CSS_MWTBLATT.' .termin { white-space:nowrap; overflow-x:hidden;
    '.$form_col4.' padding:0.1em !important; }
@media screen and (max-width:'.KAL_MOBILE.'em) {
    .'.CSS_MWTBLATT.' .termin { white-space:normal;
        padding:0.25em; color:'.$kalcol[1].'; background-color:transparent; border:none; }
    }

/*   Suchmenue   */
.'.CSS_SUCH.' { }
.'.CSS_SUCH.' select, .'.CSS_SUCH.' input, .'.CSS_SUCH.' button { padding:0.1em; }
.'.CSS_SUCH.' .th { max-width:'.$such_width.'em; padding:0.25em; text-align:left; font-weight:bold; color:'.$kalcol[2].'; }
.'.CSS_SUCH.' .td { padding:0.25em; white-space:nowrap; color:'.$kalcol[1].'; }
.'.CSS_SUCH.' .kopf { font-size:1.2em; font-weight:bold; color:'.$kalcol[1].'; }
.'.CSS_SUCH.' .small { font-size:0.9em; }
.'.CSS_SUCH.' .right { float:right; }
.'.CSS_SUCH.' .left  { text-align:left; }
.'.CSS_SUCH.' .liste { padding-left:1em; }
@media screen and (max-width:'.KAL_MOBILE.'em) {
    .'.CSS_SUCH.' .liste { padding-left:0; }
    }

/*   Termin-Eingabeformular   */
.'.CSS_EINFORM.' { }
.'.CSS_EINFORM.' th { vertical-align:top; line-height:2em; text-align:left; font-weight:bold; }
.'.CSS_EINFORM.' td { line-height:1.5em; }
.'.CSS_EINFORM.' select { padding:0.25em; }
.'.CSS_EINFORM.' .action { font-weight:bold; color:'.$kalcol[2].'; }
.'.CSS_EINFORM.' .left { width:12em; vertical-align:top; white-space:nowrap; }
.'.CSS_EINFORM.' .right { text-align:right; }
.'.CSS_EINFORM.' .text { width:35em; padding:0 0.25em 0 0.25em; }
.'.CSS_EINFORM.' .date { width: 6em; padding:0 0.25em 0 0.25em; }
.'.CSS_EINFORM.' .time { width: 4em; padding:0 0.25em 0 0.25em; }
.'.CSS_EINFORM.' .int  { width: 3em; padding:0 0.25em 0 0.25em; }
.'.CSS_EINFORM.' .left2 { width:8em; vertical-align:top; white-space:nowrap; }
.'.CSS_EINFORM.' .pad { padding-left:1em; text-align:left; }

/*   Formulare im Backend   */
.'.CSS_CONFIG.' { }
.'.CSS_CONFIG.' h4 { text-align:center; }
.'.CSS_CONFIG.' th { text-align:center; }
.'.CSS_CONFIG.' .head { text-align:left; font-weight:bold; }
.'.CSS_CONFIG.' .indent { padding:0.1em 0.1em 0.1em 1.5em; white-space:nowrap; }
.'.CSS_CONFIG.' .undent { padding:0.1em 0.1em 0.1em 0.25em; white-space:nowrap; }
.'.CSS_CONFIG.' .number { padding:0.25em 1em 0.25em 0.25em; text-align:right; }
.'.CSS_CONFIG.' .inpint { width:4em; padding:0 0.25em 0 0.25em; text-align:right; }
.'.CSS_CONFIG.' .inptxt { width:14em; padding:0 0.25em 0 0.25em; }
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
   # --- Ueberschrift Farbe
   $string='
<div class="'.CSS_CONFIG.'">
<form method="post">
<table class="kal_table">
    <tr><td class="head" colspan="4">
            Farben in den Kalendermenüs (RGB):</td></tr>
    <tr><td class="indent">
            zumeist abgeleitet von der definierten Grundfarbe</td>
        <th class="number">R&nbsp;</th>
        <th class="number">G&nbsp;</th>
        <th class="number">B&nbsp;</th></tr>';
   #
   # --- Formular Grundfarbe
   $farben=self::kal_farben();
   $colrgb=array();
   $coltxt=array();
   for($i=1;$i<=count($farben);$i=$i+1):
      $colrgb[$i]=$farben[$i]['rgb'];
      $coltxt[$i]=$farben[$i]['name'];
      endfor;
   $col=self::kal_split_color($colrgb[1]);
   $string=$string.'
    <tr><td class="indent">
            <div class="indent kal_col1">'.$coltxt[1].'</div></td>
        <td class="indent">
            <input class="inpint" type="text" name="red"   value="'.$col['red'].'" /></td>
        <td class="undent">
            <input class="inpint" type="text" name="green" value="'.$col['green'].'" /></td>
        <td class="undent">
            <input class="inpint" type="text" name="blue"  value="'.$col['blue'].'" /></td></tr>';
   #
   # --- restliche Farben
   for($i=2;$i<=count($farben);$i=$i+1):
      $col=self::kal_split_color($colrgb[$i]);
      $string=$string.'
    <tr><td class="indent">
            <div class="indent kal_col'.$i.'">'.$coltxt[$i].'</div></td>
        <td class="number">'.$col['red'].'</td>
        <td class="number">'.$col['green'].'</td>
        <td class="number">'.$col['blue'].'</td></tr>';
      endfor;
   #
   # --- Formular Stundenleiste
   $string=$string.'
    <tr><td class="head" colspan="4">
            Darstellung des Uhrzeit-Bereichs bei Tagesterminen:</td></tr>
    <tr><td class="indent">Start-Uhrzeit &nbsp; <i>(ganze Zahl)</i>:</td>
        <td class="indent">
            <input class="inpint" type="text" name="'.STD_BEG_UHRZEIT.'" value="'.$readsett[STD_BEG_UHRZEIT].'" /></td>
        <td colspan="2"><tt>&nbsp;:00 Uhr</tt></td></tr>
    <tr><td class="indent">End-Uhrzeit &nbsp; <i>(ganze Zahl)</i>:</td>
        <td class="indent">
            <input class="inpint" type="text" name="'.STD_END_UHRZEIT.'" value="'.$readsett[STD_END_UHRZEIT].'" /></td>
        <td colspan="2"><tt>&nbsp;:00 Uhr</tt></td></tr>
    <tr><td class="indent">Gesamtbreite &nbsp; <i>(ganze Zahl, zwischen '.MIN_ANZ_PIXEL.' und '.MAX_ANZ_PIXEL.')</i>:</td>
        <td class="indent">
            <input class="inpint" type="text" name="'.STD_ANZ_PIXEL.'" value="'.$readsett[STD_ANZ_PIXEL].'" /></td>
        <td colspan="2"><tt>&nbsp;Pixel</tt></td></tr>';
   #
   # --- Formular, Terminkategorien
   $string=$string.'
    <tr><td class="head" colspan="4">Terminkategorien:</td></tr>';
   $anz=0;
   for($i=1;$i<=count($readsett)-4;$i=$i+1):
      $key=KAL_KAT.strval($i);
      $set=$readsett[$key];
      if(!empty($set)):
        $string=$string.'
    <tr><td class="indent">'.$i.'</td>
        <td class="indent" colspan="3">
            <input class="inptxt" type="text" name="'.$key.'" value="'.$set.'" /></td></tr>';
        $anz=$anz+1;
        endif;
      endfor;
   #
   # --- Formular, leeres Feld fuer eine neue Terminkategorie
   $i=$anz+1;
   $key=KAL_KAT.strval($i);
   $string=$string.'
    <tr><td class="indent">
            '.$i.' &nbsp; <i>(hier kann eine neue Kategorie angefügt werden)</i></td>
        <td class="indent" colspan="3">
            <input class="inptxt" type="text" name="'.$key.'" value="" /></td></tr>
    <tr><td class="indent">
            <i>Zum <b>Entfernen</b> der <b>letzten</b> Kategorien:</i></td>
        <td class="indent" colspan="3">
            <i>entsprechende Felder leeren</i></td></tr>';
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
        <td class="indent" colspan="3"><br/>
            <button class="btn btn-save"   type="submit" name="save"  value="save"
                    title="'.$sptit.'">'.$spbut.'</button></td></tr>
</table>
</form>
</div>';
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
        $defs='';
        if($i<$nzdefs and !empty($defsett[$key])) $defs=$defsett[$key];
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
   $kat=array();
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      if(substr($key,0,3)!=KAL_KAT) continue;
      $id=substr($key,3);
      $k=$id-1;
      $kat[$k]['id']  =$id;
      $kat[$k]['name']=$settings[$key];
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