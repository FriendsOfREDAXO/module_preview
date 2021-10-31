<p>
    Die Screenshots der Modulausgaben sollten im 16:9 Format vorliegen und im Asset Ordner des Addons unter Modules (assets\addons\module_preview\modules) abgelegt werden.
    <br>
    <br>
    Der Modulname gibt den Bildname vor.
    <br>
    Der Modulname wird über die REDAXO eigene Funktion normalisiert, so wird z.B. aus „03 . Text mit Bild (1 - 3 Spalten)“ - „03-text-mit-bild-1-3-spalten.jpg“.
    <br>
    <br>
    Der Bildname wird zusätzlich als data-name Attribut auf die jeweilige Modulauswahl geschrieben.

    <?php
        $file = dirname(__FILE__) . '/_changelog.txt';
        if (is_readable($file)) {
            echo str_replace('+', '&nbsp;&nbsp;+', nl2br(file_get_contents($file)));
        }
    ?>
</p>
