<p>
    Die Screenshots der Modulausgaben sollten im 16:9 Format vorliegen und im Asset Ordner
    (assets\addons\module_preview_modules) abgelegt werden.
    <br>
    <br>
    Die Modul-ID gibt den Bildname vor (1.jpg, 2.jpg, etc.).
    <br>
    <br>
    Ist das Theme- und Developer-Addon installiert und die Einstellung "Lade Bilder aus Theme Addon" aktiviert, so werden die Vorschaubilder aus dem Modul-Ordner des Theme-Addons geladen.
    <br>
    Dazu muss eine .jpg mit dem Namen <code>module_preview.jpg</code> in das Verzeichniss <code>/private/redaxo/modules/__MODULNAME__</code> gelegt werden.
    <?php
    $file = __DIR__ . '/_changelog.txt';
    if (is_readable($file)) {
        echo str_replace('+', '&nbsp;&nbsp;+', nl2br(file_get_contents($file)));
    }
    ?>
</p>
