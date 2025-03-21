<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2025
*/
#
class kal_termine {
#
#----------------------------------------- Methoden
#   Tabellenstruktur
#      kal_define_tabellenspalten()
#      kal_ausgabe_tabellenstruktur()
#   Definition der Default-Daten
#      kal_default_config()
#   Setzen/Lesen der konfigurierten Daten
#      kal_set_config($settings)
#      kal_get_config()
#   Auslesen der konfigurierten Daten
#      kal_post_in($key,$type)
#      kal_post_in_termin()
#      kal_split_color($color)
#      kal_farben()
#      kal_hatch_gen($dif,$color)
#      kal_conf_terminkategorien()
#      kal_get_spielkategorien()
#      kal_kategorie_name($kid)
#      kal_select_kategorie($name,$kid,$katids,$all)
#      kal_allowed_terminkategorien($kid)
#   Link-Funktion
#      kal_link($par,$linktext,$title,$modus)
#   Verfuegbare Menues
#      kal_define_menues()
#   Breite des Uhrzeit-Bildes
#      kal_uhrzeit_width($scrwid)
#
#----------------------------------------- Konstanten
const this_addon     =__CLASS__;             // Name des AddOns
#     Module-Identifizierung
const MODUL1_IDENT   =__CLASS__.'_module::kal_manage_termine()';   // Termine verwalten
const MODUL2_IDENT   =__CLASS__.'_module::kal_show_termine()';     // Termine anzeigen
#     Kalendermenue-Parameter-Schluessel
const KAL_MENNR      ='kal_menue';           // Key: Menue-Nummer
const KAL_AB         ='kal_ab';              // Key: Starttermin
const KAL_ANZTAGE    ='kal_anztage';         // Key: Termin-Zeitraum (Anzahl Tage)
const KAL_NEW_MENU   ='kal_new_menu';        // Key: neues Kalendermenue
#     Konfigurationsdaten
const STAUHRZ_KEY    ='stauhrz';             // Key: Uhrzeit fuer den Tagesanfang
const ENDUHRZ_KEY    ='enduhrz';             // Key: Uhrzeit fuer das Tagesende
const DEFAULT_COL    ='rgb(5,90,28)';        // Default-Grundfarbe (dunkelgruen)
const DEFAULT_COL_KEY='col';                 // Key: Default-Grundfarbe
const FIRST_CATEGORY ='Allgemein';           // einzige Kategorie der Default-Konfiguration
#     Bestimmung der abgeleiteten Farben
const COLOR_GREY       ='rgb(150,150,150)';  // neutrale Schrift- und Rahmenfarbe
#     andere Farben fuer das Backend
const BGCOLOR_READONLY ='rgb(211,211,211)';  // Hintergrundfarbe fuer readonly-Felder
const BGCOLOR_WARNING  ='rgb(200,180,80)';   // Hintergrundfarbe fuer Warnungen
const BGCOLOR_CODE2    ='rgb(242,249,244)';  // Hintergrundfarbe fuer Hervorhebungen
const COLOR_CODE2      ='rgb(0,120,0)';      // Textfarbe fuer Hervorhebungen
#     Terminkategorien
const ROLE_KAT       ='Terminkategorie';     // Rollenname: ROLE_KAT <Nr>
const KAT_KEYS       ='kat';                 // Keys: Namensstamm der Kategorie-Keys ('kat1', 'kat2', ...)
#     Schluessel fuer die Kalenderspalten
const TAB_KEY=array(
   'pid',    'kat_id', 'name',       'datum', 'beginn', 'ende',  'tage',
   'wochen', 'monate', 'ausrichter', 'ort',   'link',   'komm',  'zeit2',
   'text2',  'zeit3',  'text3',      'zeit4', 'text4',  'zeit5', 'text5');
#     Erlaeuterungstexte
const TIP_TAGE  ='Die Zahl gibt an, über wieviele Tage sich der betreffende '.
                 'Termin insgesamt erstreckt (=1: eintägig).';
const TIP_WOCHEN='Die Zahl gibt an, wie oft sich der betreffende Termin über den '.
                 'ersten Tag hinaus wöchentlich wiederholt (=0: keine Wiederholung).';
const TIP_MONATE='Die Zahl gibt an, wie oft sich der betreffende Termin als '.
                 'gleicher Wochentag monatlich wiederholt (=0: keine Wiederholung). '.
                 'Beispiel: beginnt die Folge der Termine am 2. Freitag im Mai, '.
                 'erstreckt sie sich auf alle 2. Freitage im Juni, Juli, ... '.
                 'Beachte: nur ein 1., 2., 3. oder 4. gleicher Wochentag im Monat '.
                 'kann sich so wiederholen, ein 5. nicht.';
#     Spieldaten
const SPIEL_KATID    =99990;                 // 99991, ... Kategorie-Ids der Spieldaten
const SPIEL_RAD      =12;                    // max. Anz. Wochen vor/nach der aktuellen Woche
const SPIEL_DIF      =self::SPIEL_RAD/4;     // Division sollte ohne Rest sein
#     Stylesheet
const CSS_MOBILE     =35;                    // Stylesheet Smartphone 'max-width:...em'
const CSS_COLS       ='kal_col';             // Stylesheet Namensstamm farbige Boxen
const CSS_SIZE       =4;                     // size-Wert fuer die Select-Menues
#     Formular-Parameter fuer die Aktionen mit Terminen (Formulare)
const ACTION_NAME    ='ACTION';              // Key: Name der Aktion (auch hidden value)
const ACTION_INSERT  ='INSERT';              // neuen Termin einfuegen
const ACTION_DELETE  ='DELETE';              // Termin loeschen
const ACTION_UPDATE  ='UPDATE';              // Termin korrigieren
const ACTION_COPY    ='COPY';                // Termin kopieren
const ACTION_REPEAT  ='REPEAT';              // Aktion wiederholen (Wert: 0 / 1)
#     Link-Parameter (Menues, als $_POST[...]-Parameter)
const KAL_MONAT      ='MONAT';               // Key (Wert: Monat als Zahl)
const KAL_KW         ='KW';                  // Key (Wert: Kalenderwoche als Zahl)
const KAL_JAHR       ='JAHR';                // Key (Wert: als vierstellige Zahl)
const KAL_DATUM      ='DATUM';               // Key (Wert in der Form tt.mm.jjj) 
const KAL_KATEGORIE  ='KATEGORIE';           // Key (Wert: Id der Kategorie)
const KAL_SUCHEN     ='SUCHEN';              // Key (Wert: Suchstring)
const KAL_MENUE      ='MENUE';               // Key (Wert: Nummer des Menues)
const KAL_PID        ='PID';                 // Key (Wert: Termin-Id als Zahl)
const KAL_ANKER      ='anker';               // Key (Wert: Anker innerhalb der Seite)
const KAL_MODUS      ='MODUS';               // Key (Wert: Modus fuer kal_monatsmenue)
#     Uhrzeit-Formular
const UHR_STD        ='uhr_std.png';         // Uhr mit Stunden-Ziffernblatt
const UHR_MIN        ='uhr_min.png';         // Uhr mit Minuten-Ziffernblatt
const KAL_STUNDE     ='stunde';              // Eingabefeld-Id Auswahl der Stunde
const KAL_MINUTEN    ='minuten';             // Eingabefeld-Id Auswahl der Minuten
const ID_UHRZEITFORM ='UHRZEITFORM';         // Id zum Verbergen des Uhrzeitformulars
const ID_UHRZEITKEY  ='UHRZEITKEY';          // Id Inputfeld Zwischenspeicherung des Zeit-Keys
const ID_INPUTFORM   ='INPUTFORM';           // Id zum Verbergen des Eingabeformulars
#     Stylesheet zur farblichen Markierung der Terminkategorien in einer Terminliste
const TERMLIST       ='termlist';            // Konfigurations-Key fuer die Terminliste-Keys
const PALETTE_FILE   ='termlist_farben.txt'; // Datei mit Farbpalette fuer die Terminliste
const PALETTE_DIR    ='vendor';              // Ordner fuer die Terminlisten-Farbpaletten-Datei
const PALETTE_SIZE   =1;                     // Param. zur Groesse der Default-Farbpalette (36)
const PALETTE_KEYS   =array('sides', 'pix'); // Paletten-Keys
const PALETTE_VALS   ='0,0';                 // Werte zu den Paletten-Keys
const MAX_PIX        =10;                    // max. Rahmendicke bei der Terminliste
#     Blaetter-Symbole, entnommen aus dem Awesome-Font
const AWE_HIDE       ='<i class="fa fa-solid fa-arrow-up-long"></i>';    // Konfig. Terminliste
const AWE_MONAT      ='<i class="fa fa-solid fa-calendar-days"></i>';    // M-M: Monatsmenue
const AWE_VORJAHR    ='<i class="fa fa-solid fa-angles-left"></i>';      // M-M: Vorjahr
const AWE_NACHJAHR   ='<i class="fa fa-solid fa-angles-right"></i>';     // M-M: Folgejahr
const AWE_VORHER     ='<i class="fa fa-solid fa-angle-left"></i>';       // M-M: Vormonat
const AWE_NACHHER    ='<i class="fa fa-solid fa-angle-right"></i>';      // M-M: Folgemonat
const AWE_UEBERSICHT ='<i class="fa fa-solid fa-bars"></i>';             // T-Ueb: Terminuebersicht
const AWE_HEUTE      ='<i class="fa fa-solid fa-calendar-day"></i>';     // T-Ueb: heute
const AWE_VORMON     ='<i class="fa fa-solid fa-angle-up"></i>';         // T-Ueb: Vormonat
const AWE_NACHMON    ='<i class="fa fa-solid fa-angle-down"></i>';       // T-Ueb: Folgemonat
const AWE_SUCHEN     ='<i class="fa fa-solid fa-magnifying-glass"></i>'; // T-Ueb: suchen
const AWE_CANCEL     ='<i class="fa fa-solid fa-xmark"></i>';            // Terminblatt: abbrechen
const AWE_EDIT       ='<i class="fa fa-regular fa-pen-to-square"></i>';  // Terminblatt: bearbeiten
const AWE_DELETE     ='<i class="fa fa-regular fa-trash-can"></i>';      // Terminblatt: loeschen
const AWE_COPY       ='<i class="fa fa-regular fa-copy"></i>';           // Terminblatt: kopieren
const AWE_SAVE       ='<i class="fa fa-regular fa-floppy-disk"></i>';    // Eingabeformular: speichern
#
#----------------------------------------- Variable
public static $SPIELDATEN    =FALSE;   // Spieldaten oder Datenbankdaten
#
#----------------------------------------- Tabellenstruktur
public static function kal_define_tabellenspalten() {
   #   Rueckgabe der Daten zu den Kalender-Tabellenspalten als nummerierte Array
   #   (Nummerierung ab 0):
   #   - Keys und Typen der Spalten (zur Einrichtung der Tabelle)
   #   - Beschreibung und Hinweise zu den Tabellenspalten
   #
   $cols=array(
      self::TAB_KEY[ 0]=>array('int(11) NOT NULL auto_increment', 'Termin-Id', 'Primärschlüssel', 'auto_increment'),
      self::TAB_KEY[ 1]=>array('int(11) NOT NULL DEFAULT 1', 'Kategorie', '',                '&ge;1'),
      self::TAB_KEY[ 2]=>array('varchar(255) NOT NULL',      'Titel',     '',                'nicht leer'),
      self::TAB_KEY[ 3]=>array('date NOT NULL',              'Datum',     'tt.mm.yyyy',      'nicht leer'),
      self::TAB_KEY[ 4]=>array('time NOT NULL',              'Beginn',    'hh:mm',           ''),
      self::TAB_KEY[ 5]=>array('time NOT NULL',              'Ende',      'hh:mm',           ''),
      self::TAB_KEY[ 6]=>array('int(11) NOT NULL DEFAULT 1', 'mehrtägig', ' ... Tage',       '&ge;1'),
      self::TAB_KEY[ 7]=>array('int(11) NOT NULL DEFAULT 0', 'wöchentl.', ' ... Wochen',     '&ge;0'),
      self::TAB_KEY[ 8]=>array('int(11) NOT NULL DEFAULT 0', 'monatlich', ' ... Monate',     '&ge;0'),
      self::TAB_KEY[ 9]=>array('varchar(500) NOT NULL',      'Ausrichter','',      ''),
      self::TAB_KEY[10]=>array('varchar(255) NOT NULL',      'Ort',       '',      ''),
      self::TAB_KEY[11]=>array('varchar(500) NOT NULL',      'Link',      '',      ''),
      self::TAB_KEY[12]=>array('text NOT NULL',              'Hinweise',  '',      ''),
      self::TAB_KEY[13]=>array('time NOT NULL',              ' Beginn 2', 'hh:mm', ''),
      self::TAB_KEY[14]=>array('varchar(255) NOT NULL',      ' Titel',    '',      ''),
      self::TAB_KEY[15]=>array('time NOT NULL',              ' Beginn 3', 'hh:mm', ''),
      self::TAB_KEY[16]=>array('varchar(255) NOT NULL',      ' Titel',    '',      ''),
      self::TAB_KEY[17]=>array('time NOT NULL',              ' Beginn 4', 'hh:mm', ''),
      self::TAB_KEY[18]=>array('varchar(255) NOT NULL',      ' Titel',    '',      ''),
      self::TAB_KEY[19]=>array('time NOT NULL',              ' Beginn 5', 'hh:mm', ''),
      self::TAB_KEY[20]=>array('varchar(255) NOT NULL',      ' Titel',    '',      ''));
   return $cols;
   }
public static function kal_ausgabe_tabellenstruktur() {
   #   Rueckgabe der Tabellenstrukturen in Form eines HTML-Codes.
   #
   # --- Schleife ueber die Tabellenspalten
   $cols=self::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   $table=cms_interface::name_termin_tabelle();
   $string='
<table cellspacing="0" cellpadding="0">
    <tr><td colspan="5">
            <h4 align="center">Struktur der Tabelle <tt>'.$table.'</tt></h4></td></tr>
    <tr><td class="kal_indent">
            <b>Spaltenname</b></td>
        <td class="kal_indent">
            <b>Spalteninhalt</b></td>
        <td class="kal_indent">
            <b>Ergänzung</b></td>
        <td class="kal_indent">
            <b>SQL-Format</b></td>
        <td class="kal_indent">
            <b>Restriktionen</b></td>
        <td class="kal_indent">
            <b>Hinweis</b></td></tr>
';
   for($i=0;$i<count($cols);$i=$i+1):
      $inha=$cols[$keys[$i]][1];
      $arr=explode(' ',$cols[$keys[$i]][0]);
      $form=$arr[0];
      $arr=explode('(',$form);
      $form=$arr[0];
      $ergz=$cols[$keys[$i]][2];
      $restr=$cols[$keys[$i]][3];
      $beme='';
      if($form=='text' or $form=='varchar') $beme=' &nbsp; 5';
      if($keys[$i]==self::TAB_KEY[6]) $beme='2';
      if($keys[$i]==self::TAB_KEY[7]) $beme='3';
      if($keys[$i]==self::TAB_KEY[8]) $beme='4';
      if($keys[$i]==self::TAB_KEY[1]) $beme='1';
      if($form=='varchar' and substr($keys[$i],0,4)=='text') $beme=' &nbsp; 5,6';
      if($form=='time'    and substr($keys[$i],0,4)=='zeit') $beme=' &nbsp; &nbsp; 6';
      $string=$string.'
    <tr><td class="kal_indent"><tt>
            '.$keys[$i].'</tt></td>
        <td class="kal_indent">
            '.$inha.'</td>
        <td class="kal_indent">
            <tt>'.$ergz.'</tt></td>
        <td class="kal_indent">
            <tt>'.$form.'</tt></td>
        <td class="kal_indent">
            <i>'.$restr.'</i></td>
        <td class="kal_indent">
            <tt>'.$beme.'</tt></td></tr>
';
     endfor;
   $string=$string.'
</table><br>
<div><b>Hinweise:</b></div>
<ol class="kal_olul">
    <li><tt>'.self::TAB_KEY[1].'</tt>: &nbsp; Id der Kategorie gemäß
        Konfiguration (= Nummer in der Reihenfolge)</li>
    <li><tt>'.self::TAB_KEY[6].'</tt>: &nbsp; '.self::TIP_TAGE.'</li>
    <li><tt>'.self::TAB_KEY[7].'</tt>: &nbsp; '.self::TIP_WOCHEN.'</li>
    <li><tt>'.self::TAB_KEY[8].'</tt>: &nbsp; '.self::TIP_MONATE.'</li>
    <li><tt>varchar</tt> / <tt>text</tt>: &nbsp; diese Texte
        können auch HTML-Code (z.B. Links) enthalten</li>
    <li><tt>'.self::TAB_KEY[13].'/'.self::TAB_KEY[14].', ... , 
        '.self::TAB_KEY[19].'/'.self::TAB_KEY[20].'</tt>:
        &nbsp; für eine evtl. zeitliche Untergliederung</li>
</ol>
';
   return $string;
   }
#
#----------------------------------------- Definition der Default-Daten
public static function kal_default_config() {
   #   Rueckgabe der Default-Konfigurationswerte.
   #
   $sett=array();
   # --- Grundfarbe
   $sett[self::DEFAULT_COL_KEY]=self::DEFAULT_COL;
   #
   # --- Stundenleiste
   $sett[self::STAUHRZ_KEY]=0;
   $sett[self::ENDUHRZ_KEY]=24;
   #
   # --- Terminkategorien (Default: nur eine)
   $sett[self::KAT_KEYS.'1']=self::FIRST_CATEGORY;
   #
   # --- Terminliste-Parameter
   $sett[self::TERMLIST]=self::PALETTE_VALS;
   return $sett;
   }
#
#----------------------------------------- Setzen/Lesen der konfigurierten Daten
public static function kal_set_config($settings) {
   #   Setzen der Konfigurationsparamter gemaess gegebenem Array.
   #   $settings       assoziatives Array der Konfigurationsparameter
   #                   (die Keys sind wegen der Default-Konfiguration in der
   #                   richtigen Reihenfolge vorgegeben)
   #
   # --- Zunaechst alle Konfigurationsparameter loeschen
   cms_interface::config_removeNamespace();
   #
   # --- Setzen der Parameter gemaess Vorgabe
   $lkc=strlen(self::DEFAULT_COL_KEY);
   $lkk=strlen(self::KAT_KEYS);
   $keys=array_keys($settings);
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      $value=$settings[$key];
      if($key!=self::DEFAULT_COL_KEY and
         substr($key,0,$lkk)!=self::KAT_KEYS and
         $key!=self::TERMLIST                   ) $value=intval($value);
      cms_interface::config_set($key,$value);
      endfor;
   }
public static function kal_get_config() {
   #   Rueckgabe der gesetzten Konfigurationsparameter als assoziatives Array
   #   (ohne Parameter mit dem Key TERMLIST).
   #
   $sett=cms_interface::config_get();
   $keys=array_keys($sett);
   $settings=array();
   for($i=0;$i<count($sett);$i=$i+1):
      $key=$keys[$i];
             if($key=='pixel') continue;   // ### key 'pixel' ab Vers. 3.7 entfernt
      if($key==self::TERMLIST) continue;
      $settings[$key]=$sett[$key];
      endfor;
   return $settings;
   }
#
#----------------------------------------- Auslesen der konfigurierten Daten
public static function kal_post_in($key,$type='string') {
   #   Rueckgabe des Wertes von $_POST[$key]. Nicht-leere Werte werden
   #   unveraendert zurueck gegeben. Leere Werte werden als '0' zurueck
   #   gegeben, falls $type=='int'.
   #   $key            Schluessel von $_POST
   #   $type           ='int':    Werte werden als integer zurueck gegeben,
   #                              leere Werte werden als '0' zurueck gegeben
   #                   ='string': Werte werden als Zeichenfolge zurueck gegeben,
   #                              leere Werte werden als '' zurueck gegeben
   #
   $typ=$type;
   if($typ!='int' and $typ!='string') $typ='string';
   $post=$_POST[$key];
   if(!empty($post)):
     return $post;
     else:
     if($typ=='int') return '0';
     return '';
     endif;
   }
public static function kal_post_in_termin() {
   #   Rueckgabe aller per $_POST[...] eingelesenen Parameter eines Termins
   #   in Form eines Termin-Arrays.
   #
   $cols=self::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   $termin=array();
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      $typ=$cols[$key][0];
      $typ=explode(' ',$typ)[0];
      $typ=explode('(',$typ)[0];
      if($typ=='int'):
        $val=self::kal_post_in($key,$typ);
        else:
        $val=self::kal_post_in($key);
        endif;
      $termin[$key]=$val;
      endfor;
   return $termin;
   }
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
   #      [2],[3],[4],[5] systematisch aufgehellte Variationen der Grundfarbe
   #      [6],[7]         komplementaere Farbtoene fuer den aktuellen Tag
   #      [8]             graue Schrift-/Rahmenfarbe im Monatsmenue fuer Tage
   #                      ausserhalb des aktuellen Monats
   #   Die Grundfarbe ist als dunkle Schriftfarbe zu konfigurieren.
   #   Deren RGB-Werte sollten nicht zu gross sein, damit sich die Farben
   #   genuegend unterscheiden (technisch gaebe es keine Grenze).
   #   Beispiele fuer die Farbgebung durch die Wahl der Grundfarbe:
   #      roetlich:   rgb(R,g,b)   (R ist deutlich groesser als g und b)
   #      gruenlich:  rgb(r,G,b)   (G ist deutlich groesser als r und b)
   #      blaeulich:  rgb(r,g,B)   (B ist deutlich groesser als r und g)
   #      gelblich:   rgb(R,G,b)   (R und G sind deutlich groesser als b)
   #      violett:    rgb(R,g,B)   (R und B sind deutlich groesser als g)
   #      tuerkis:    rgb(r,G,B)   (G und B sind deutlich groesser als r)
   #      grau:       rgb(R,G,B)   (R = G = B)
   #
   # --- Grundfarbe
   $sett=cms_interface::config_get();
   $keys=array_keys($sett);
   $base_col=self::DEFAULT_COL;
   for($i=0;$i<count($keys);$i=$i+1)
      if(substr($keys[$i],0,3)=='col'):
        $base_col=$sett[$keys[$i]];
        break;
        endif;
   #
   # --- Charakterisierung/Einsatz der Farben
   $cnam=array(
      1=>'dunkle Schrift-/Rahmenfarbe, <b>Grundfarbe &nbsp; (*)</b>',
      2=>'helle Schriftfarbe (Menüs, Stundenleiste)',
      3=>'Hintergrundfarbe (Termine Monats-, Wochen-, Tagesblatt), Schraffur',
      4=>'Hintergrundfarbe (Sonn-/Feiertage in den Menüs, Kategorieauswahl, ...)',
      5=>'Hintergrundfarbe (Wochentage in den Menüs, Kategorieauswahl, ...)',
      6=>'Schrift-/Rahmenfarbe (heutiger Tag in den Menüs)',
      7=>'Hintergrundfarbe (heutiger Tag in den Menüs)',
      8=>'neutrale Schrift-/Rahmenfarbe (nicht abgeleitet)');
   $anz=count($cnam)-3;   // Anzahl der Farbtoene in der Farbe der Grundfarbe
   #
   # --- RGB-Werte der Farben
   $dol=self::kal_split_color($base_col);
   $dolred=$dol['red'];
   $dolgre=$dol['green'];
   $dolblu=$dol['blue'];
   $max=max($dolred,$dolgre,$dolblu);   // Default: 90
   $dif=intval((255-$max)/$anz);        // Default: 33
   $col=array();
   $col[1]=array('rgb'=>$base_col, 'name'=>$cnam[1]);
   for($i=2;$i<=$anz;$i=$i+1):
      $red=intval($dolred+$i*$dif);     // Default: $i*
      $gre=intval($dolgre+$i*$dif);
      $blu=intval($dolblu+$i*$dif);
      $rgb='rgb('.$red.','.$gre.','.$blu.')';
      $col[$i]=array('rgb'=>$rgb, 'name'=>$cnam[$i]);
      endfor;
   #
   # --- RGB-Werte der Farben fuer den heutigen Tag
   #     Hintergrund: Komplementaerfarbe zur Grundfarbe
   $red2=intval(255-$dolred);
   $gre2=intval(255-$dolgre);
   $blu2=intval(255-$dolblu);
   #     Rahmen: Komplementaerfarbe zur hellsten Hintergrundfarbe
   $red1=intval($red2-$anz*$dif);
   $gre1=intval($gre2-$anz*$dif);
   $blu1=intval($blu2-$anz*$dif);
   $rgb='rgb('.strval($red1).','.strval($gre1).','.strval($blu1).')';
   $col[$anz+1]=array('rgb'=>$rgb, 'name'=>$cnam[$anz+1]);   // Rahmen
   $rgb='rgb('.strval($red2).','.strval($gre2).','.strval($blu2).')';
   $col[$anz+2]=array('rgb'=>$rgb, 'name'=>$cnam[$anz+2]);   // Hintergrund
   #
   # --- RGB-Wert der Schrift-/Rahmenfarbe fuer Tage ausserhalb des aktuellen Monats
   $col[$anz+3]=array('rgb'=>self::COLOR_GREY, 'name'=>$cnam[$anz+3]);
   return $col;
   }   
public static function kal_hatch_gen($dif,$color) {
   #   Rueckgabe eines Style-Elementes zur 45 Grad-Schraffur.
   #   $dif              Streifenbreite in %
   #   $color            Streifenfarbe
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
        $col=$color;
        $n=0;
        endif;
      $hatch=$hatch.'
    '.$col.' '.$i.'%, '.$col.' '.$kk.'%,';
      $ii=$kk;
      endfor;
   $hatch=substr($hatch,0,strlen($hatch)-1).'); }';
   return $hatch;
   }
public static function kal_conf_terminkategorien() {
   #   Rueckgabe der konfigurierten Terminkategorien als nummeriertes Array
   #   (Nummerierung ab 0). Jede Kategorie ist ein assoziatives Array mit Id und
   #   Bezeichnung der Kategorie. Die Ids bilden eine fortlaufende Zahlenfolge,
   #   beginnend mit 1.
   #
   $ident=self::KAT_KEYS;
   $len=strlen($ident);
   $settings=self::kal_get_config();
   $keys=array_keys($settings);
   $kat=array();
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      if(substr($key,0,$len)!=$ident) continue;
      $id=intval(substr($key,$len));
      $k=$id-1;
      $kat[$k]['id']  =$id;
      $kat[$k]['name']=$settings[$key];
      endfor;
   return $kat;
   }
public static function kal_get_spielkategorien() {
   #   Rueckgabe der zu den kuenstlichen Termindaten passenden Terminkategorien
   #   in Form eines nummerierten Arrays.
   #
   return array(
      array('id'=>self::this_addon::SPIEL_KATID+1, 'name'=>self::this_addon::FIRST_CATEGORY),
      array('id'=>self::this_addon::SPIEL_KATID+2, 'name'=>'Tischtennisverband'),
      array('id'=>self::this_addon::SPIEL_KATID+3, 'name'=>'Kulturkreis'));
   }
public static function kal_kategorie_name($kid) {
   #   Ermitteln der Kategoriebezeichnung aus der Kategorie-Id. Leere Rueckgabe,
   #   falls die eingegebene Id keiner konfigurierten Kategorie entspricht.
   #   $kid            Kategorie-Id
   #
   if($kid<=0) return;
   #
   # --- Kategorien der Spieltermine / konfigurierte Kategorien
   if($kid<=self::SPIEL_KATID):
     $kat=self::kal_conf_terminkategorien();
     else:
     $kat=self::kal_get_spielkategorien();
     endif;
   for($i=0;$i<count($kat);$i=$i+1) if($kat[$i]['id']==$kid) return $kat[$i]['name'];
   }
public static function kal_select_kategorie($name,$kid,$katids,$all) {
   #   Rueckgabe des HTML-Codes fuer die Auswahl der erlaubten Kategorien.
   #   Die erlaubten Kategorien haengen von den Terminkategorie-Rollen ab,
   #   die der Redakteur hat, der den aktuellen Artikel angelegt hat.
   #   $name           Name des select-Formulars
   #   $kid            Id der ggf. schon ausgewaehlten Kategorie, falls leer/0
   #                   bzw. self::SPIEL_KATID, werden alle Kategorien angenommen
   #   $katids         Array der fuer den Redakteur erlaubten Terminkategorien
   #                   (Nummerierung ab 1)
   #   $all            =TRUE: es kann auch 'alle Kategorien' ausgewaehlt werden
   #                   sonst: es kann nur genau eine Kategorie ausgewaehlt werden
   #
   $selkid=$kid;
   #
   # --- Kategorien (Ids + Namen)
   if($katids[1]<self::SPIEL_KATID):
     #     Datenbankdaten
     if($selkid<=0) $selkid=0;
     #     verfuegbare Kategorien
     $kat=self::kal_conf_terminkategorien();
     #     erlaubte Kategorien herausfiltern
     $kate=array();
     $m=0;
     for($i=0;$i<count($kat);$i=$i+1)
        for($k=1;$k<=count($katids);$k=$k+1)
           if($kat[$i]['id']==$katids[$k]):
             $m=$m+1;
             $kate[$m]=$kat[$i];
             endif;
     else:
     #     Spieldaten
     if($selkid<=self::SPIEL_KATID) $selkid=self::SPIEL_KATID;
     #     verfuegbare (= erlaubte) Kategorien
     $kat=self::kal_get_spielkategorien();
     $kate=array();
     for($i=0;$i<count($kat);$i=$i+1) $kate[$i+1]=$kat[$i];
     endif;
   #
   # --- Select-Formular
   $nzmax=count($kate);
   $option='';
   #     ALLE Terminkategorien
   if($all and count($kate)>1):   // nur wenn mehr als eine Kategorie zur Wahl steht
     if($katids[1]<self::SPIEL_KATID):
       $akid=0;
       else:
       $akid=self::SPIEL_KATID;
       endif;
     if($selkid==$akid):
       $sel='class="kal_select kal_normal" selected';
       else:
       $sel='class="kal_select kal_normal"';
       endif;   
     $option=$option.'
    <option value="'.$akid.'" '.$sel.'>(alle)</option>';
     $nzmax=$nzmax+1;
     endif;
   if($nzmax<=self::CSS_SIZE):
     $size='size="0"';
     else:
     $size='onfocus="this.size='.self::CSS_SIZE.';" onfocusout="this.size=null;"';
     endif;
   #     Select-Formatierung (size)
   $string='
<select name="'.$name.'" class="kal_select kal_normal" title="Kategorieauswahl"
        '.$size.'>'.$option;
   #     einzelne Terminkategorien
   for($i=1;$i<=count($kate);$i=$i+1):
      if($kate[$i]['id']==$selkid):
        $sel='class="kal_select kal_normal" selected';
        else:
        $sel='class="kal_select kal_normal"';
        endif;
      $option='
    <option value="'.$kate[$i]['id'].'" '.$sel.'>'.$kate[$i]['name'].'</option>';
      $string=$string.$option;
      endfor;
   $string=$string.'
</select>';
   return $string;
   }
public static function kal_allowed_terminkategorien($kid) {
   #   Rueckgabe einer oder aller fuer einen Redakteur erlaubten Terminkategorien
   #   als nummeriertes Array der zugehoerigen Kategorie-Ids (Nummerierung ab 1).
   #   $kid            =0:                    alle erlaubten Kategorien
   #                   >0                     nur erlaubte Kategorie $kid
   #                   =self::SPIEL_KATID:    alle Spielkategorien
   #                   >self::SPIEL_KATID:    nur Spielkategorie $kid
   #
   $katids=array();
   if($kid<self::SPIEL_KATID):
     #     konfigurierte Daten: alle Kategorien / einzelne Kategorie
     $kids=cms_interface::allowed_terminkategorien();
     if($kid==0):
       $katids=$kids;
       else:
       $katids[1]=$kids[$kid];
       endif;
     else:
     #     Spieldaten: alle Kategorien / einzelne Kategorie
     $kats=self::kal_get_spielkategorien();
     for($i=0;$i<count($kats);$i=$i+1) $kids[$i+1]=$kats[$i]['id'];
     if($kid==self::SPIEL_KATID):
       $katids=$kids;
       else:
       $katids[1]=$kids[intval($kid-self::SPIEL_KATID)];
       endif;
     endif;
   return $katids;
   }
public static function kal_select_anzahl($name,$anz) {
   #   Rueckgabe des HTML-Codes fuer die Auswahl einer Anzahl von Tagen
   #   (mehrtaegige Termine: 1, 2, 3, ...) bzw. von Wochen oder Monaten
   #   (woechentlich bzw. monatlich wiederkehrende Termine: 0, 1, 2, ...).
   #   $name           Name des select-Formulars
   #   $anz            ggf. schon ausgewaehlte Nummer
   #
   $addon=self::this_addon;
   $ke=$addon::TAB_KEY;
   $keytag=$ke[6];   // tage
   $keywch=$ke[7];   // wochen
   $keymon=$ke[8];   // monate
   #
   # --- Array der Anzahlen
   $anzahlen=array();
   if($name==$keytag):
     $nzmax=self::CSS_SIZE;
     for($i=1;$i<=$nzmax;$i=$i+1) $anzahlen[$i]=$i;
     $size='size="0"';
     endif;
   if($name==$keywch or $name==$keymon):
     $nzmax=52;
     for($i=0;$i<=$nzmax;$i=$i+1) $anzahlen[$i]=$i;
     $size='onfocus="this.size='.self::CSS_SIZE.';" onfocusout="this.size=null;"';
     endif;
   #
   # --- Select-Menue
   $string='
<select name="'.$name.'" class="kal_select kal_short kal_normal" title="Auswahl der Anzahl"
        '.$size.'>';
   $keys=array_keys($anzahlen);
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      $val=$anzahlen[$key];
      if($val==$anz):
        $sel='class="kal_select kal_short kal_normal" selected';
        else:
        $sel='class="kal_select kal_short kal_normal"';
        endif;
      $option='
    <option value="'.$val.'" '.$sel.'>'.$val.'</option>';
      $string=$string.$option;
      endfor;
   $string=$string.'
</select>';
   return $string;
   }
#
#----------------------------------------- Link-Funktion
public static function kal_link($par,$linktext,$title,$modus) {
   #   Rueckgabe einer Referenz als Formular.
   #   $par            assoziatives Array von Parametern, die Parameter werden
   #                   als hidden Parameter weiter gegeben in der Form
   #                   '<input type="hidden" name="$key" value="$value">',
   #                   einer der Parameter-Keys ist immer self::KAL_MENUE mit
   #                   der Nummer des Menues, auf das verwiesen wird, als Wert
   #   $linktext       anzuzeigender Linktext, kann auch HTML-tags enthalten
   #   $title          Popup-Titeltext
   #   $modus          =0:  es wird statt des Links nur der Linktext in der
   #                        Grundfarbe zurueck gegeben
   #                   <0:  es wird statt des Links nur der Linktext in der
   #                        Grundfarbe zurueck gegeben (fett und groesser)
   #                   >0:  es wird ein Link zurueck gegeben mit Linktext in
   #                        Grundfarbe:
   #                        =1: Normalschrift
   #                        =2: fett (class="kal_bold")
   #                        =3: fett und gross (class="kal_bold kal_big")
   #                            fuer die Ueberschriften beim Blaettern)
   #                        =4: fett und klein (class="kal_small kal_bold")
   #                        =5: fett und groesser (class="kal_lightbig kal_bold")
   #                        =6: sehr gross (class="kal_bigbig")
   #
   $ltxt=htmlspecialchars_decode($linktext);
   #
   if($modus==0)
     return '<button class="kal_transp kal_basecol kal_normal">'.$ltxt.'</button>';
   #
   if($modus<0)
     return '<button class="kal_transp kal_basecol kal_bold kal_lightbig">'.$ltxt.'</button>';
   #
   # --- Zusammenbaus des Links
   $cls=' kal_normal';
   if($modus==2) $cls=' kal_normal kal_bold';
   if($modus==3) $cls=' kal_big kal_bold';
   if($modus==4) $cls=' kal_small kal_bold';
   if($modus==5) $cls=' kal_lightbig kal_bold';
   if($modus==6) $cls=' kal_bigbig';
   $str='
<form method="post" title="'.$title.'">';
   $keys=array_keys($par);
   #     Zielmenue bestimmen (spaeter in den Button einsetzen, nur einmal vorhanden)
   $men='';
   $mennam=self::KAL_MENUE;
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      $val=$par[$key];
      if($key==$mennam):
        $men=$val;
        break;
        endif;
      endfor;
   #     hidden inputs setzen
   $href='';
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      if($key==$mennam) continue;
      $val=$par[$key];
      if(empty($val)) continue;
      $str=$str.'
    <input type="hidden" name="'.$key.'" value="'.$val.'">';
      #     ggf. Anker setzen
      if($key==self::KAL_ANKER)
        $href='
            onclick="location.href=\'#'.$val.'\';"';
     endfor;
   #     Button
   $str=$str.'
    <button type="submit" class="kal_basecol kal_transp kal_linkbut'.$cls.'" name="'.$mennam.'" value="'.$men.'"'.$href.'>
        '.$ltxt.'
    </button>
</form>';
   return $str;
   }
#
#----------------------------------------- Verfuebare Menues
public static function kal_define_menues() {
   #   Rueckgabe der Namen aller benutzten Menues als nummeriertes Array
   #   (Nummerierung ab 1).
   #
   $menmom=array('name' =>'Monatsmenü',
                 'titel'=>'Kompakte Darstellung der Tage eines Monats. Tage, an '.
                          'denen Termine eingetragen sind, werden durch Schraffur '.
                          'gekennzeichnet. Alle wesentlichen christlichen und '.
                          'gesetzlichen Feiertage werden als tooltip angezeigt.');
   $menmob=array('name' =>'Monatsblatt',
                 'titel'=>'halbgrafische Darstellung der Termine an den Tagen des Monats');
   $menwob=array('name' =>'Wochenblatt',
                 'titel'=>'halbgrafische Darstellung der Termine an den Tagen der Woche');
   $mentab=array('name' =>'Tagesblatt',
                 'titel'=>'halbgrafische Darstellung der Termine an diesem Tag');
   $menteb=array('name' =>'Terminblatt',
                 'titel'=>'tabellarische Auflistung der Parameter des Termins');
   $menueb=array('name' =>'Terminübersicht',
                 'titel'=>'Terminübersicht, Liste aller Termine mit Filterfunktionen '.
                          'zur Verkürzung der Liste.');
   $terlis=array('name' =>'Terminliste',
                 'titel'=>'Einfache Auflistung der Termine eines Zeitabschnitts.');
   $menins=array('name' =>'Eingabeformular',
                 'titel'=>'Formular zur Eingabe oder Korrektur oder Kopie eines Termins');
   $mendel=array('name' =>'Löschformular',
                 'titel'=>'Formular zur Löschung eines Termins');
   $menuhr=array('name' =>'Uhrzeitformular',
                 'titel'=>'Formular zur Eingabe einer Uhrzeit');
   $mentxt=array('name' =>'Textformular',
                 'titel'=>'Formular zur Eingabe eines Fließtextes');
   return array(1=>$menmom, 2=>$menmob, 3=>$menwob, 4=>$mentab, 5=>$menteb,
                6=>$menueb, 7=>$terlis, 8=>$menins, 9=>$mendel, 10=>$menuhr, 11=>$mentxt);
   }
#
#----------------------------------------- Breite des Uhrzeit-Bildes
public static function kal_uhrzeit_width($scrwid) {
   #   Rueckgabe der Breite, in der das Uhrzeit-Bild dargestellt wird, abhaengig
   #   von der Breite des Display-Viewports. Dabei wird das Bild bei kleinen
   #   Bildschirmen so breit wie möglich dargestellt, bei grossen Bildschirmen
   #   dagegen nicht zu gross. Die reale Breite betraegt 744x744 Pixel.
   #   $scrwid         Breite des Viewports in Anzahl Pixel, ermittelt mittels
   #                   Javascript beim Aufruf des Uhrzeitformulars (kal_eingabeform())
   #
   $uhrwid=0.85*$scrwid;
   if($scrwid>=360)  $uhrwid=280;   // Faktor <= 0.777...
   if($scrwid>=480)  $uhrwid=300;   // Faktor <= 0.625
   if($scrwid>=640)  $uhrwid=320;   // Faktor <= 0.5
   if($scrwid>=720)  $uhrwid=340;   // Faktor <= 0.4722...
   if($scrwid>=1024) $uhrwid=360;   // Faktor <= 0.351...
   return $uhrwid;
   }
}
?>