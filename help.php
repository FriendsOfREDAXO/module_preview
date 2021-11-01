<p>
    Die Screenshots der Modulausgaben sollten im 16:9 Format vorliegen und im Asset Ordner des Addons unter Modules (assets\addons\module_preview_modules) abgelegt werden.
    <br>
    <br>
    Die Modul-ID gibt den Bildname vor (1.jpg, 2.jpg, etc.).
    <?php
        $file = dirname(__FILE__) . '/_changelog.txt';
        if (is_readable($file)) {
            echo str_replace('+', '&nbsp;&nbsp;+', nl2br(file_get_contents($file)));
        }
    ?>
</p>
