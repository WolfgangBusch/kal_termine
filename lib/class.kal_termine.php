<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2024
*/
#
class kal_termine {
#
#----------------------------------------- Inhaltsuebersicht
#   Tabellenstruktur
#      kal_define_tabellenspalten()
#      kal_ausgabe_tabellenstruktur()
#   Definition der Default-Daten
#      kal_default_stundenleiste()
#      kal_default_config()
#   Setzen/Lesen der konfigurierten Daten
#      kal_set_config($settings)
#      kal_get_config()
#   Auslesen der konfigurierten Daten
#      kal_get_terminkategorien()
#      kal_select_kategorie($name,$kid,$katids,$all)
#      kal_get_stundenleiste()
#      kal_define_stundenleiste()
#   Terminkategorien als Benutzerrollen
#      kal_set_roles()
#      kal_allowed_terminkategorien($artid)
#
#----------------------------------------- Konstanten
const this_addon     =__CLASS__;             // Name des AddOns
const MODULE_IN_OUT  ='output';              // Modul-Input/-Output, der Identifizierung enthaelt
#     Konfigurationsdaten
const DEFAULT_BEG_KEY='stauhrz';             // Key: Default-Uhrzeit fuer den Tagesanfang
const MIN_BEG        =0;                     // fruehester Tagesanfang
const MAX_BEG        =23;                    // spaetester Tagesanfang
const DEFAULT_END_KEY='enduhrz';             // Key: Default-Uhrzeit fuer das Tagesende
const MIN_END        =1;                     // fruehestes Tagesende
const MAX_END        =24;                    // spaetestes Tagesende
const DEFAULT_PIX_KEY='pixel';               // Key: Anzahl Pixel fuer Tageslange
const MIN_ANZ_PIXEL  =100;                   // Mindestanzahl Pixel fuer Tageslaenge
const MAX_ANZ_PIXEL  =1000;                  // Maximalanzahl Pixel fuer Tageslaenge
const DEFAULT_COL    ='rgb(5,90,28)';        // Default-Grundfarbe (dunkelgruen)
const DEFAULT_COL_KEY='col';                 // Key: Default-Grundfarbe
const FIRST_CATEGORY ='Allgemein';           // einzige Kategorie der Default-Konfiguration
#     Bestimmung der abgeleiteten Farben
const RGB_GREY       ='rgb(150,150,150)';    // Farbe fuer Tage ausserhalb des aktuellen Monats
const RGB_DIFF       =25;                    // RGB-Werte-Differenz 
const RGB_MAX        =255-6*self::RGB_DIFF;  // unterster RGB-Wert fuer die Grundfarbe
#     Terminkategorien
const ROLE_KAT       ='Terminkategorie';     // Rollenname <Nr.> fuer Terminkategorien
const KAT_KEYS       ='kat';                 // Keys: Namensstamm der Kategorie-Keys ('kat1', 'kat2', ...)
#     Schluessel fuer die Kalenderspalten
const TAB_KEY=array(
   'pid',    'kat_id', 'name',       'datum', 'beginn', 'ende',  'tage',
   'wochen', 'monate', 'ausrichter', 'ort',   'link',   'komm',  'zeit2',
   'text2',  'zeit3',  'text3',      'zeit4', 'text4',  'zeit5', 'text5');
   #     Spieldaten
const SPIEL_KATID    =99990;                 // 99991, ... Kategorie-Ids der Spieldaten
const SPIEL_RAD      =8;                     // Anz. Wochen vor/nach der aktuellen Woche
const SPIEL_WDH      =5;                     // Anz. Wochen Wiederholungstermin: 8+5
#     Stylesheet
const CSS_MOBILE     =35;                    // Stylesheet Smartphone 'max-width:...em'
const CSS_TERMBLATT  ='kal_terminblatt';     // Stylesheet Terminblatt
const CSS_MONMENUE   ='kal_monatsmenue';     // Stylesheet Monatsmenue
const CSS_MWTBLATT   ='kal_mowotablatt';     // Stylesheet Mo-/Wo-/Ta-Blatt
const CSS_SUCH       ='kal_such';            // Stylesheet Suchmenue
const CSS_EINFORM    ='kal_eingabeform';     // Stylesheet Eingabeformulare
const CSS_CONFIG     ='kal_config';          // Stylesheet Konfigurationsformulare
const CSS_COLS       ='kal_col';             // Stylesheet Namensstamm farbige Boxen
#     Formular-Parameter fuer die Termindaten
const VALUE_NAME     ='value_';              // Namensstamm fuer value_01, value_02, ...
                                             // Keys fuer die Termindaten
#     Formular-Parameter fuer die Aktionen mit Terminen (Formulare)
const ACTION_NAME    ='ACTION';              // Key: Name der Aktion (auch hidden value)
const ACTION_START   ='START';               // Abbruch und Neustart
const ACTION_INSERT  ='INSERT';              // neuen Termin einfuegen
const ACTION_DELETE  ='DELETE';              // Termin loeschen
const ACTION_UPDATE  ='UPDATE';              // Termin korrigieren
const ACTION_COPY    ='COPY';                // Termin kopieren
const ACTION_SELECT  ='SELECT';              // Auswahl einer Aktion (Radio-Buttons)
const PID_NAME       ='PID';                 // Key: Termin-Id (hidden value)
const CALL_NUM       ='call_number';         // Key: Aktionsdurchlauf-Nr. (1/2) (hidden value)
#     Link-Parameter (Menues, als $_POST[...]-Parameter)
const KAL_MONAT      ='MONAT';               // Key (Wert: Monat als Zahl)
const KAL_KW         ='KW';                  // Key (Wert: Kalenderwoche als Zahl)
const KAL_JAHR       ='JAHR';                // Key (Wert: als vierstellige Zahl)
const KAL_DATUM      ='DATUM';               // Key (Wert in der Form tt.mm.jjj) 
const KAL_KATEGORIE  ='KATEGORIE';           // Key (Wert: Id der Kategorie)
const KAL_SUCHEN     ='SUCHEN';              // Key (Wert: Suchstring)
const KAL_VORHER     ='VORHER';              // Key (Wert: TRUE/FALSE)
const KAL_MENUE      ='MENUE';               // Key (Wert: Nummer des Menues)
const KAL_PID        ='PID';                 // Key (Wert: Termin-Id als Zahl)
#     Stylesheet zur farblichen Markierung der Terminkategorien in einer Terminliste
const TERMLIST       ='termlist';            // Konfigurations-Key fuer die Terminliste-Keys
const PALETTE_FILE   ='termlist_farben.txt'; // Datei mit Farbpalette fuer die Terminliste
const PALETTE_SIZE   =1;                     // Param. zur Groesse der Default-Farbpalette (36)
const PALETTE_KEYS   =array('sides', 'pix', 'title', 'date', 'shadow');
const SHADOW         ='1px 1px 0 grey';      // Wert fuer Text- und Box-Shadow
const MAX_PIX        =10;                    // max. Rahmendicke bei der Terminliste
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
      self::TAB_KEY[ 1]=>array('int(11) NOT NULL DEFAULT 1', 'Kategorie-Id',   '',                '&ge;1'),
      self::TAB_KEY[ 2]=>array('varchar(255) NOT NULL',      'Veranstaltung',  '',                'nicht leer'),
      self::TAB_KEY[ 3]=>array('date NOT NULL',              'Datum',          'tt.mm.yyyy',      'nicht leer'),
      self::TAB_KEY[ 4]=>array('time NOT NULL',              'Uhrzeit Beginn', 'hh:mm',           ''),
      self::TAB_KEY[ 5]=>array('time NOT NULL',              'Uhrzeit Ende',   'hh:mm',           ''),
      self::TAB_KEY[ 6]=>array('int(11) NOT NULL DEFAULT 1', 'Dauer in Tagen', '',                '&ge;1'),
      self::TAB_KEY[ 7]=>array('int(11) NOT NULL DEFAULT 0', 'Wiederholung',   'über ... Wochen', '&ge;0'),
      self::TAB_KEY[ 8]=>array('int(11) NOT NULL DEFAULT 0', 'Wiederholung',   'über ... Monate', '&ge;0'),
      self::TAB_KEY[ 9]=>array('varchar(500) NOT NULL',      'Ausrichter',     '',      ''),
      self::TAB_KEY[10]=>array('varchar(255) NOT NULL',      'Ort',            '',      ''),
      self::TAB_KEY[11]=>array('varchar(500) NOT NULL',      'Link',           '',      ''),
      self::TAB_KEY[12]=>array('text NOT NULL',              'Hinweise',       '',      ''),
      self::TAB_KEY[13]=>array('time NOT NULL',              'Teil 2, Beginn', 'hh:mm', ''),
      self::TAB_KEY[14]=>array('varchar(255) NOT NULL',      'Teil 2, Titel',  '',      ''),
      self::TAB_KEY[15]=>array('time NOT NULL',              'Teil 3, Beginn', 'hh:mm', ''),
      self::TAB_KEY[16]=>array('varchar(255) NOT NULL',      'Teil 3, Titel',  '',      ''),
      self::TAB_KEY[17]=>array('time NOT NULL',              'Teil 4, Beginn', 'hh:mm', ''),
      self::TAB_KEY[18]=>array('varchar(255) NOT NULL',      'Teil 4, Titel',  '',      ''),
      self::TAB_KEY[19]=>array('time NOT NULL',              'Teil 5, Beginn', 'hh:mm', ''),
      self::TAB_KEY[20]=>array('varchar(255) NOT NULL',      'Teil 5, Titel',  '',      ''));
   ###         create table:     'PRIMARY KEY (pid)'
   return $cols;
   }
public static function kal_ausgabe_tabellenstruktur() {
   #   Rueckgabe der Tabellenstrukturen in Form eines HTML-Codes.
   #   Aufgerufen nur in pages/table.php
   #   benutzte functions:
   #      self::kal_define_tabellenspalten()
   #
   # --- Schleife ueber die Tabellenspalten
   $cols=self::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   $table=rex::getTablePrefix().self::this_addon;
   $string='
<div class="'.self::CSS_CONFIG.'">
<table class="kal_table">
    <tr><td colspan="5">
            <h4>Struktur der Tabelle <tt>'.$table.'</tt></h4></td></tr>
    <tr><td class="head">Spaltenname</td>
        <td class="kal_indent head">Spalteninhalt</td>
        <td class="kal_indent head">SQL-Format</td>
        <td class="kal_indent head">Restriktionen</td>
        <td class="kal_indent head">Hinweis</td></tr>
';
   for($i=0;$i<count($cols);$i=$i+1):
      $inha=$cols[$keys[$i]][1];
      $arr=explode(' ',$cols[$keys[$i]][0]);
      $form=$arr[0];
      $arr=explode('(',$form);
      $form=$arr[0];
      $bedg=$cols[$keys[$i]][3];
      $beme='';
      if($form=='text' or $form=='varchar') $beme=' &nbsp; &nbsp; &nbsp; &nbsp; 5.';
      if($keys[$i]==self::TAB_KEY[7]) $beme='2.';
      if($keys[$i]==self::TAB_KEY[8]) $beme='3.';
      if($keys[$i]==self::TAB_KEY[1]) $beme='1.';
      if($form=='varchar' and substr($keys[$i],0,4)=='text') $beme=' &nbsp; &nbsp; 4.';
      if($form=='time'    and substr($keys[$i],0,4)=='zeit') $beme=' &nbsp; &nbsp; 4.';
      $string=$string.'
    <tr><td class="kal_indent"><tt>'.$keys[$i].'</tt></td>
        <td class="kal_indent">'.$inha.'</td>
        <td class="kal_indent"><tt>'.$form.'</tt></td>
        <td class="kal_indent"><i>'.$bedg.'</i></td>
        <td class="kal_indent"><i>'.$beme.'</i></td></tr>
';
     endfor;
   $string=$string.'
</table><br>
<div><b>Hinweise:</b></div>
<ol class="kal_olul">
    <li><tt>'.self::TAB_KEY[1].'</tt>: &nbsp; Id der Kategorie gemäß
        Konfiguration (= Nummer in der Reihenfolge)</li>
    <li><tt>'.self::TAB_KEY[7].'</tt>: &nbsp; gibt an, wie oft sich der
        betreffende Termin ab <tt>&nbsp;datum&nbsp;</tt> wöchentlich wiederholt</li>
    <li><tt>'.self::TAB_KEY[8].'</tt>: &nbsp; gibt an, wie oft sich der
        betreffende Termin als 1., 2., 3. oder 4. gleicher<br>
        Wochentag ab <tt>&nbsp;datum&nbsp;</tt> monatlich wiederholt</li>
    <li><tt>'.self::TAB_KEY[13].'/'.self::TAB_KEY[14].', ... , 
        '.self::TAB_KEY[19].'/'.self::TAB_KEY[20].'</tt>:
        &nbsp; für eine evtl. zeitliche Untergliederung</li>
    <li><tt>varchar</tt> / <tt>text</tt>: &nbsp; diese Texte
        können auch HTML-Code (z.B. Links) enthalten</li>
</ol>
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
   $sett[self::DEFAULT_COL_KEY]=self::DEFAULT_COL;
   #
   # --- Stundenleiste
   $daten=self::kal_default_stundenleiste();
   $sett[self::DEFAULT_BEG_KEY]=$daten[1]['stl'];
   $sett[self::DEFAULT_END_KEY]=$daten[2]['stl'];
   $sett[self::DEFAULT_PIX_KEY]=$daten[3]['stl'];
   #
   # --- Terminkategorien (Default: nur eine)
   $sett[self::KAT_KEYS.'1']=self::FIRST_CATEGORY;
   #
   # --- Terminliste-Parameter
   $sett[self::TERMLIST]='0,0,0,0,0';
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
   rex_config::removeNamespace(self::this_addon);
   #
   # --- Setzen der Parameter gemaess Vorgabe
   $lkc=strlen(self::DEFAULT_COL_KEY);
   $lkk=strlen(self::KAT_KEYS);
   $keys=array_keys($settings);
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      $set=$settings[$key];
      if($key!=self::DEFAULT_COL_KEY and
         substr($key,0,$lkk)!=self::KAT_KEYS and
         $key!=self::TERMLIST                   ) $set=intval($set);
      $bool=rex_config::set(self::this_addon,$key,$set);
      endfor;
   rex_config::save();
   }
public static function kal_get_config() {
   #   Rueckgabe der gesetzten Konfigurationsparameter als assoziatives Array
   #   (ohne Parameter mit dem Key TERMLIST). 
   #
   rex_config::refresh();
   $sett=rex_config::get(self::this_addon,NULL);
   $keys=array_keys($sett);
   $settings=array();
   for($i=0;$i<count($sett);$i=$i+1):
      $key=$keys[$i];
      if($key==self::TERMLIST) continue;
      if(rex_config::has(self::this_addon,$key)) $settings[$key]=$sett[$key];
      endfor;
   return $settings;
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
      if(substr($key,0,3)!=self::KAT_KEYS) continue;
      $id=substr($key,3);
      $k=$id-1;
      $kat[$k]['id']  =$id;
      $kat[$k]['name']=$settings[$key];
      endfor;
   return $kat;
   }
public static function kal_select_kategorie($name,$kid,$katids,$all) {
   #   Rueckgabe des HTML-Codes fuer die Auswahl der erlaubten Kategorien.
   #   Die erlaubten Kategorien haengen von den Terminkategorie-Rollen ab,
   #   die der Redakteur hat, der den aktuellen Artikel angelegt hat.
   #   $name           Name des select-Formulars
   #   $kid            Id der ggf. schon ausgewaehlten Kategorie,
   #                   falls leer/0, werden alle Kategorien angenommen
   #   $katids         Array der fuer den Redakteur erlaubten Terminkategorien
   #                   (Nummerung ab 1)
   #   $all            =TRUE: es kann auch 'alle Kategorien' ausgewaehlt werden
   #                   sonst: es kann nur genau eine Kategorie ausgewaehlt werden
   #   benutzte functions:
   #      self::kal_get_terminkategorien()
   #
   $selkid=$kid;
   if($katids[1]<self::SPIEL_KATID):
     #
     # --- Datenbankdaten
     if($selkid<=0) $selkid=0;
     #     verfuegbare Kategorien
     $kat=self::kal_get_terminkategorien();
     #     erlaubte Kategorien, entsprechend den Rollen des Autors des aktuellen Artikels
     $kat_ids=$katids;
     else:
     #
     # --- Spieldaten
     if($selkid<=0) $selkid=self::SPIEL_KATID;
     #     verfuegbare Kategorien
     $kat=kal_termine_tabelle::kal_get_spielkategorien();
     #    alle Kategorien erlaubt
     $kat_ids=array();
     for($i=1;$i<=count($kat);$i=$i+1) $kat_ids[$i]=$kat[$i-1]['id'];
     endif;
   #
   # --- Einschraenkung auf die erlaubten Kategorien
   $kate=array();
   $m=0;
   for($i=0;$i<count($kat);$i=$i+1)
      for($k=1;$k<=count($kat_ids);$k=$k+1)
         if($kat[$i]['id']==$kat_ids[$k]):
           $m=$m+1;
           $kate[$m]=$kat[$i];
           endif;
   #
   # --- Select-Formular
   $string='
            <select name="'.$name.'" class="'.self::CSS_COLS.'5">';
   #     ALLE Terminkategorien
   if($all and count($kate)>1):   // nur wenn mehr als eine Kategorie zur Wahl steht
     if($katids[1]<self::SPIEL_KATID):
       $akid=0;
       else:
       $akid=self::SPIEL_KATID;
       endif;
     if($selkid==$akid):
       $sel='class="'.self::CSS_COLS.'5" selected="selected"';
       else:
       $sel='class="'.self::CSS_COLS.'6"';
       endif;   
     $string=$string.'
                <option value="'.$akid.'" '.$sel.'>(alle)</option>';
     endif;
   #     einzelne Terminkategorien
   for($i=1;$i<=count($kate);$i=$i+1):
      if($kate[$i]['id']==$selkid):
        $sel='class="'.self::CSS_COLS.'5" selected="selected"';
        else:
        $sel='class="'.self::CSS_COLS.'6"';
        endif;
      $option='
                <option value="'.$kate[$i]['id'].'" '.$sel.'>'.$kate[$i]['name'].'</option>';
      $string=$string.$option;
      endfor;
   $string=$string.'
            </select>';
   return $string;
   }
public static function kal_get_stundenleiste() {
   #   Rueckgabe der konfigurierten Daten zur Stundenleiste als assoziatives Array
   #   mit diesen Keys und Werten:
   #      [self::DEFAULT_BEG_KEY]    Start-Uhrzeit (integer)
   #      [self::DEFAULT_END_KEY]    End-Uhrzeit (integer)
   #      [self::DEFAULT_PIX_KEY]    Laenge der Stundenleiste in Anzahl Pixel
   #   Die Gesamtlaenge der Stundenleiste ist die Summe der Netto-Inhalte der
   #   Tabellenzellen ohne Border- und Padding-Breiten.
   #   benutzte functions:
   #      self::kal_get_config()
   #
   $settings=self::kal_get_config();
   $stl=array();
   $stl[self::DEFAULT_BEG_KEY]=$settings[self::DEFAULT_BEG_KEY];
   $stl[self::DEFAULT_END_KEY]=$settings[self::DEFAULT_END_KEY];
   $stl[self::DEFAULT_PIX_KEY]=$settings[self::DEFAULT_PIX_KEY];
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
   $stauhrz=$stl[self::DEFAULT_BEG_KEY];
   $enduhrz=$stl[self::DEFAULT_END_KEY];
   $pixel  =$stl[self::DEFAULT_PIX_KEY];
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
#
#----------------------------------------- Terminkategorien als Benutzerrollen
public static function kal_set_roles() {
   #   Einrichtung von je einer Benutzerrolle pro konfigurierter Terminkategorie.
   #   Zur Kontrolle werden die Rollen in Form eines nummerierten Arrays zurueck
   #   gegeben (Nummerierung ab 1, jede Rolle als assoziatives Array).
   #   Aufgerufen nur in boot.php
   #   benutzte functions:
   #      self::kal_get_terminkategorien()
   #
   # --- Administrator-Benutzer und heutiges Datum
   $art_id=rex_article::getSiteStartArticleId();
   $table=rex::getTablePrefix().'article';
   $sql=rex_sql::factory();
   $art=$sql->getArray('SELECT * FROM '.$table.' WHERE id=\''.$art_id.'\'');
   $admin=$art[0]['updateuser'];
   $dat=date('d.m.Y');
   $heute=substr($dat,6).'-'.substr($dat,3,2).'-'.substr($dat,0,2).' 00:00:00';
   #
   # --- konfigurierte Terminkategorien
   $kats=self::kal_get_terminkategorien();
   $anz=count($kats);
   $idmax=$kats[$anz-1]['id'];
   #
   # --- zugehoerige Rollen in die Datenbanktabelle rex_user_role schreiben
   $table=rex::getTablePrefix().'user_role';
   $roles=array();
   for($i=0;$i<$anz;$i=$i+1):
      $katid=$kats[$i]['id'];
      $perm=self::this_addon.'['.$katid.']';
      $role=self::ROLE_KAT.' '.$katid;
      #     Permission registrieren
      rex_perm::register($perm,$kats[$i]['name'],rex_perm::EXTRAS);
      #     Rolle einrichten, falls sie nicht schon eingerichtet ist
      $perm=json_encode(["general"=>null, "options"=>null,"extras"=>"|".$perm."|"]);
      $qselect='SELECT * FROM '.$table.' WHERE name=\''.$role.'\'';
      $arr=$sql->getArray($qselect);
      if(count($arr)<=0):
        $qpar='name,perms,createuser,updateuser,createdate,updatedate';
        $qval='\''.$role.'\',\''.$perm.'\',\''.$admin.'\',\''.$admin.'\',\''.$heute.'\',\''.$heute.'\'';
        $insert='INSERT INTO '.$table.' ('.$qpar.') VALUES ('.$qval.')';
        $sql->setQuery($insert);
        #     neue Rolle direkt wieder auslesen
        $arr=$sql->getArray($qselect);
        endif;
      $roles[$i+1]=$arr[0];
      endfor;
   #
   # --- geloeschte Terminkategorien (Konfiguration), zugehoerige Rollen entfernen
   $qselect='SELECT * FROM '.$table.' WHERE name LIKE \'%'.self::ROLE_KAT.'%\'';
   $rollen=$sql->getArray($qselect);
   $pos=strlen(self::ROLE_KAT)+1;
   $m=0;
   for($i=0;$i<count($rollen);$i=$i+1):
      $name=$rollen[$i]['name'];
      $katid=intval(substr($name,$pos));
      if($katid>$idmax):
        $role=self::ROLE_KAT.' '.$katid;
        $delete='DELETE FROM '.$table.' WHERE name=\''.$role.'\'';
        $sql->setQuery($delete);
        endif;
      endfor;
   #
   # --- Rueckgabe der definierten Terminkategorie-Rollen
   return $roles;
   }
public static function kal_allowed_terminkategorien($artid=0) {
   #   Rueckgabe der Ids der konfigurierten Terminkategorien, die ein
   #   bestimmter Redaxo-Redakteur verwenden darf, als nummeriertes Array
   #   (Nummerierung ab 1). Der Redakteur wird aus den Daten des aktuellen
   #   Artikels ermittelt:
   #   Die function wird nur in den Modulen dieses AddOns aufgerufen.
   #   Wenn ein Redakteur diese Module in einem Artikelblock nutzt,
   #   hinterlaesst er in natuerlicher Weise seine Identitaet im Artikel-
   #   Parameter CREATEUSER. Die Ids der Terminkategorien werden aus den
   #   Permissions der Benutzerrolle (name='self::ROLE_KAT id') des
   #   Redakteurs ausgelesen.
   #   $artid          <=0: Es wird die Id des aktellen Artikels angenommen.
   #                   >0:  Id eines Artikels (nur zu Testzwecken). Falls
   #                        kein Artikel mit entsprechender Id existiert,
   #                        wird die Id des aktellen Artikels angenommen.
   #   benutzte functions:
   #      self::kal_get_terminkategorien()
   #
   # --- aktueller Artikel
   $art_id=$artid;
   if($art_id<=0) $art_id=rex_article::getCurrentId();
   $art=rex_article::get($art_id);
   if($art==null):
     $art=rex_article::getCurrent();
     $art_id=$art->getId();
     endif;
   #
   # --- Id des Redakteurs, der den Artikel erzeugt hat
   $login=$art->getCreateUser();
   $sql=rex_sql::factory();
   $table=rex::getTablePrefix().'user';
   $users=$sql->getArray('SELECT * FROM '.$table.' WHERE login=\''.$login.'\'');
   $userid=$users[0]['id'];
   $user=rex_user::get($userid);
   #
   $katids=array();
   #
   # --- Administrator (Zugriff auf alle Terminkategorien)
   if($user->isAdmin()):
     $kat=self::kal_get_terminkategorien();
     for($i=1;$i<=count($kat);$i=$i+1) $katids[$i]=$i;
     endif;
   #
   # --- sonstiger Redakteur
   if(!$user->isAdmin()):
     $role=$user->getValue('role');
     $roles=explode(',',$role);
     $m=0;
     $sql=rex_sql::factory();
     $table2=rex::getTablePrefix().'user_role';
     for($k=0;$k<count($roles);$k=$k+1):
        $query='SELECT * FROM '.$table2.' WHERE id='.$roles[$k];
        $rolarr=$sql->getArray($query);
        #     hat er Rollen namens 'self::ROLE_KAT id'?
        $rname=$rolarr[0]['name'];
        if(substr($rname,0,strlen(self::ROLE_KAT))!=self::ROLE_KAT) continue;
        #     Katgorie-Ids ausgelesen aus Permissions der Rolle 'self::ROLE_KAT id'
        $perms=$rolarr[0]['perms'];
        $extras=json_decode($perms,TRUE)['extras'];   // = |kal_termine[id]|
        $id=substr($extras,1,strlen($extras)-3);      // = kal_termine[id
        $id=substr($id,strpos($id,'[')+1);            // = id
        $m=$m+1;
        $katids[$m]=$id;
        endfor;
     endif;
   return $katids;
   }
}
?>