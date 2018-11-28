<!doctype html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <title>Stud.IP-Konfigurationsfehler</title>
    <script>
      document.createElement("mark");
    </script>
    <style>
      body {
        margin: auto;
        width: 40em;
        font-family: Futura, "Century Gothic", AppleGothic, sans-serif;
      }
      p {
        line-height: 1.5em;
        margin-top: 1.5em;
        margin-bottom: 1.5em;
      }
      mark {
        display: inline-block;
        background: #ffff88;
        border: 1px dotted #888;
        padding: 0.5em;
      }
      code {
        font-family: "Monaco", "Courier New", "DejaVu Sans Mono", "Bitstream Vera Sans Mono", monospace;
        background-color: #f8f8ff;
        padding: 0.5em;
        line-height: 1.5em;
      }
      p code {
        padding: 0;
        background: none;
      }
    </style>
  </head>
  <body>
    <h1>Fehler in der PHP-Konfiguration Ihrer Stud.IP-Installation</h1>
    <p>
        Für den Betrieb von Stud.IP bis zur Version 2.0 war es erforderlich,
        die Konfiguration des PHP-Moduls Ihres Webservers anzupassen, damit
        folgende Zeile – evtl. leicht verändert – enthalten ist:
    </p>

    <code>
      auto_prepend_file = /usr/local/studip/lib/phplib/prepend4.php
    </code>

    <p>
      Diese Konfigurationsoption wird für Stud.IP ab der Version 2.0 nicht mehr
      verwendet.
    </p>

    <p>
      <mark>
      Entfernen Sie daher bitte die genannte Zeile aus Ihrer Konfiguration,
      um diese Fehlermeldung zu verhindern.
      </mark>
      Typischerweise finden Sie diese in einer der folgenden Dateien:
      <code>php.ini</code>, <code>httpd.conf</code> oder in einer
      <code>.htaccess</code> Datei.
    </p>

    <p>
      Wenn Sie dafür weitere Hilfe benötigen, besuchen Sie bitte das
      Forum im
      <a href="http://develop.studip.de/studip/seminar_main.php?auswahl=a70c45ca747f0ab2ea4acbb17398d370">Developer-Board</a>.
    </p>

    <p>
      Vielen Dank, Ihre Stud.IP-CoreGroup
    </p>

  </body>
</html>
<?
exit();
