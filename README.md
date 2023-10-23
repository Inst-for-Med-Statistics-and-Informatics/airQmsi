# airQmsi
PHP Script zum Speichern der air-Q-Daten in eine Textdatei

Hallo zusammen,

wir an der Medizinischen Universität Innsbruck,
verwenden den air-Q Science.

Im Zuge dessen hab ich für uns eine Script-Set auf PHP basis erstellt, mit dem man folgendes machen kann:

a) Automatisches Speichern der air-Q-Daten in eine Text-Datei (Intervall kann spezifiert werden)
b) Web-basiertes auslesen der air-Q-Daten

Die Dokumentation dazu findet man unter:
air-Q-MUI-Dokumentation (http://www.i-med.ac.at/msig/forschung/airQ/airQmui_Doku.pdf)

Die notwendigen Dateien findet man unter:
air-Q-MUI-Dateien (http://www.i-med.ac.at/msig/forschung/airQ/airQmui.zip)

PS: In der ZIP-Datei ist auch eine SPSS Label-Datei, sprich wenn man die .csv-Datei in das Statistik-Programm SPSS importiert, kann man mit der Lable-Datei die Beschriftungen der Felder aktualisieren :)

Ein Hinweis am Rande:

Die Dezimalzahlen werden mit "." (Punkt) als Dezimalzeichen in die .csv-Datei eingetragen, das macht bei Excel Probleme, da das Programm die Werte dann teilweise in Datums-Werte umwandelt. Daher:

Um die Daten in Excel zu importieren bzw. mit Excel zu öffnen:
a) .csv mit einem Texteditor (Notepad z.B.) öffnen
b) "." (Punkt) durch "," (Komma, Beistrich) ersetzten

.. keine Angst, der "." kommt wirklich nur bei den Dezimalzahlen in der .csv-Datei vor, sonst nirgendwo :)

Schöne Grüße

Lalit Kaltenbach
