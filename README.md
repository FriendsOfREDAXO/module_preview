# REDAXO-Addon: Module preview

REDAXO 5 Addon um eine Vorschau (Screenshot) bei der Modulauswahl anzuzeigen

![Screenshot](https://raw.githubusercontent.com/eaCe/module_preview/assets/screenshot.jpg)

Die Screenshots der Modulausgaben sollten im 16:9 Format vorliegen und im Asset Ordner (assets\addons\module_preview_modules) abgelegt werden.

Die Modul-ID oder der Modul-Key gibt den Bildnamen vor (1.jpg, 2.jpg, etc. oder modul_key.jpg, text.jpg etc.).

Ist das Theme- und Developer-Addon installiert und die Einstellung "Lade Bilder aus Theme Addon" aktiviert, so werden die Vorschaubilder aus dem Modul-Ordner des Theme-Addons geladen. 
Dazu muss eine .jpg mit dem Namen `module_preview.jpg` in das Verzeichniss `/private/redaxo/modules/__MODULNAME__'` gelegt werden.
