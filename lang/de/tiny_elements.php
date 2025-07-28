<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin Elements strings for language de.
 *
 * @package     tiny_elements
 * @category    string
 * @copyright   2025 ISB Bayern
 * @author      Stefan Hanauska <stefan.hanauska@csg-in.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['additem'] = 'Hinzufügen';
$string['bulk_edit_displaynames'] = 'Editieren aller Anzeigenamen';
$string['bulk_edit_flavor_displaynames'] = 'Editieren aller Flavor Anzeigenamen';
$string['bulk_edit_variant_displaynames'] = 'Editieren aller Variant Anzeigenamen';
$string['button_elements'] = 'Elemente';
$string['c4lcompatibility'] = 'Wenn ausgewählt, lautet der Varianten-Klassenname c4l-...-variant anstelle von elements-...-variant, um mit den ursprünglichen c4l-Komponenten kompatibel zu sein.';
$string['c4lcompatibility_help'] = 'C4L (components for learning) ist das Plugin auf dem Elements basiert.';
$string['cachedef_tiny_elements_css'] = 'Cache für tiny_elements CSS';
$string['category'] = 'Kategorie';
$string['close'] = 'Schließen';

$string['code'] = 'HTML';
$string['code_help'] = 'Einzufügender HTML-Code. Sie können {{VARIANTS}}, {{FLAVOR}} und {{PLACEHOLDER}} als Platzhalter für Varianten, Flavors und einzufügenden Text verwenden.';
$string['compcat'] = 'Kategorien';
$string['compflavor_icons'] = 'Ändern der Symbole für Komponenten je nach Flavor';
$string['component'] = 'Komponente';
$string['component_flavor'] = 'Komponente/Flavor';
$string['componentname'] = 'Komponentenname';
$string['componentname_help'] = 'Name der Komponente zur internen Verwendung (auch als Klassenname in CSS)';
$string['components'] = 'Komponenten';
$string['content'] = 'Inhalt';
$string['copyasstring'] = 'Alle Elemente als String in den Zwischenspeicher laden';
$string['copyfail'] = 'Fehler beim Kopieren aufgetreten';
$string['copyof'] = 'Kopie von {$a}';
$string['copysuccess'] = 'Elemente in den Zwischenspeicher kopiert';
$string['css'] = 'CSS';
$string['delete'] = 'Element "{$a}" löschen';
$string['deletewarning'] = 'Sind Sie sicher, dass Sie dieses Element löschen möchten?';
$string['displayname'] = 'Anzeigename';
$string['displayname_help'] = 'Name der Komponente, der den Benutzern angezeigt wird';
$string['displayorder'] = 'Anzeigereihenfolge';
$string['dryrun'] = 'Import simulieren';
$string['dryrun_help'] = 'Wenn diese Option aktiviert ist, wird ein Import simuliert ohne Änderungen vorzunehmen. Damit kann festgestellt werden, ob durch den Import bestehende Objekte verändert werden.';
$string['edititem'] = 'Element bearbeiten';
$string['editlicenses'] = 'Quellenangaben der Symbole bearbeiten';
$string['editlicensesformfileautor_help'] = '<strong>Wer</strong> hat das Werk geschaffen? <br> Hier wird der Autor, Künstler
            oder wenn keine Person genannt ist, die herausgebende Institution angegeben. <br> <br>
            Üblich ist dabei folgendes Schema: <br> Nachname, Vorname. Herausgeber werden durch "Hrsg." am Schluss hervorgehoben.
            Im Falle mehrerer Autoren werden diese durch Schrägstriche voneinander getrennt angegeben. <br> Sind auf einer Website
            keine Autoren genannt, tritt der Name der Website bzw. der herausgebenden Institution an Autorenstelle.';
$string['editlicensesformfilelicense_help'] = 'Geben Sie bitte an, unter welcher Lizenz das verwendet Material steht.';
$string['editlicensesformfileurl'] = 'Quelle oder URL';
$string['editlicensesformfileurl_help'] = '<strong>Wo</strong> wurde das Werk veröffentlicht? <br> <br> Bei Büchern ist der Verlag
            und der Verlagsort, für Zeitschriftenartikel der Name der Zeitschrift plus Jahr-, Band-, Heft- und Seitenangabe, bei
            Webseiten die vollständige URL anzugeben.';
$string['elements:manage'] = 'Komponenten verwalten';
$string['elements:showteachercomponents'] = 'Komponenten anzeigen, die nur für Lehrkräfte bestimmt sind';
$string['elements:viewplugin'] = 'Kurselemente-Plugin anzeigen';
$string['enablepreview'] = 'Vorschau aktivieren';
$string['enablepreview_desc'] = 'Wenn aktiviert, wird eine Vorschau angezeigt, wenn Sie mit der Maus über jede Komponente fahren.';
$string['error_export'] = 'Fehler beim Erstellen der Exportdatei';
$string['error_fileimport'] = 'Fehler beim Importieren der Datei "{$a}"';
$string['error_import_component'] = 'Fehler beim Importieren der Komponente "{$a}"';
$string['error_import_missing_table'] = 'Fehler beim Importieren der XML-Datei: Tabelle "{$a}" fehlt';
$string['errorbackupfile'] = 'Fehler in der Sicherungsdatei';
$string['errorcompcat'] = '**compcat** darf nicht leer sein';
$string['errordisplayname'] = 'Anzeigename darf nicht leer sein';
$string['errorname'] = 'Name darf nicht leer sein';
$string['export'] = 'Exportieren';
$string['files'] = 'Dateien';
$string['flavor'] = 'Flavor';
$string['flavors'] = 'Flavors';
$string['foundcompcat'] = 'Nicht zugewiesene Elemente';
$string['generalsettings'] = 'Allgemeine Einstellungen';
$string['hideforstudents'] = 'Verbergen für Schüler';
$string['iconurl'] = 'Symbol-URL';
$string['import'] = 'Importieren';
$string['import_simulation'] = 'Import simulieren';
$string['js'] = 'JS';
$string['linktomanagerdesc'] = 'Gehen Sie zu <a href="{$a}">Verwaltung</a>, um Änderungen vorzunehmen.';
$string['linktomanagername'] = 'Link zur Verwaltung';
$string['linktopreviewall'] = 'Link zur Vorschau';
$string['linktopreviewall_desc'] = 'Gehen Sie zu <a href="{$a}">Vorschau</a>, um alle Elemente anzusehen und als String zu exportieren.';
$string['manage'] = 'Verwalten';
$string['management'] = 'Verwaltung';
$string['menuitem_elements'] = 'Kurselemente';
$string['name'] = 'Name';
$string['newcategory'] = 'Neue Kategorie "{$a}"';
$string['newcompflavor'] = 'Erzeuge Beziehung Komponente <-> Geschmacksrichtung "{$a}"';
$string['newcomponent'] = 'Neue Komponente "{$a}"';
$string['newcompvariant'] = 'Erzeuge Beziehung Komponente <-> Variante "{$a}"';
$string['newfile'] = 'Neue Datei "{$a}"';
$string['newflavor'] = 'Neue Geschmacksrichtung "{$a}"';
$string['newmetadata'] = 'Neue Quellenangabe "{$a}"';
$string['newmetadatafilemissing'] = 'Zugehörige Datei nicht gefunden: "{$a}"';
$string['newvariant'] = 'Neue Variante "{$a}"';
$string['pluginname'] = 'Kurselemente';
$string['preview'] = 'Vorschau';
$string['previewall'] = 'Vorschau aller Elemente';
$string['previewcss'] = 'Vorschau-CSS';
$string['previewcsstext'] = 'Wenn alles korrekt ist, sollte die Komponente in allen Flavors angezeigt werden.';
$string['previewdefault'] = 'Zeigen Sie mit dem Mauszeiger auf eine Komponente, um eine Vorschau anzuzeigen.';
$string['privacy:preference:category'] = 'Bevorzugte Kategorie';
$string['privacy:preference:components_flavors'] = 'Bevorzugte Flavors für jede Komponente';
$string['privacy:preference:components_variants'] = 'Bevorzugte Varianten jeder Komponente';
$string['replacecategory'] = 'Kategorie "{$a}" ersetzen';
$string['replacecompflavor'] = 'Ersetze Beziehung Komponente <-> Geschmacksrichtung "{$a}"';
$string['replacecomponent'] = 'Ersetze Komponente "{$a}"';
$string['replacecompvariant'] = 'Ersetze Beziehung Komponente <-> Variante "{$a}"';
$string['replacefile'] = 'Ersetze Datei "{$a}"';
$string['replaceflavor'] = 'Ersetze Geschmacksrichtung "{$a}"';
$string['replacevariant'] = 'Ersetze Variante "{$a}"';
$string['showprinturls'] = 'Ein Symbol auswählen';
$string['text'] = 'Text';
$string['unchangedfile'] = 'Datei "{$a}" ist unverändert';
$string['validclassname'] = 'Der Name muss ein gültiger CSS-Klassenname sein. Er darf nur Buchstaben, Zahlen und die Zeichen "-" und "_" enthalten. Er muss mit einem Buchstaben oder "_" beginnen. Es wird empfohlen, nur Kleinbuchstaben zu verwenden.';
$string['variant'] = 'Variante';
$string['variants'] = 'Varianten';
$string['wipe'] = 'Alles löschen';
$string['wipewarning'] = 'Diese Funktion entfernt alle Kategorien, Dateien, Komponenten, Flavors und Varianten. Es gibt keine Möglichkeit, diesen Schritt rückgängig zu machen. Stellen Sie sicher, dass Sie ein Backup haben!';
