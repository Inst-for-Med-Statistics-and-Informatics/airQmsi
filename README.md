# airQmsi
PHP Script zum Speichern der air-Q-Daten in eine Textdatei

Hallo zusammen,

wir an der Medizinischen Universität Innsbruck, verwenden den air-Q Science.

Im Zuge dessen hab ich für uns eine Script-Set auf PHP basis erstellt, mit dem man folgendes machen kann:

a) Automatisches Speichern der air-Q-Daten in eine Text-Datei (Intervall kann spezifiert werden)
b) Web-basiertes auslesen der air-Q-Daten
c) SPSS Label-Datei

Die Dokumentation dazu findet man im Ordner PDF.

@SPSS
Im Statistik-Programm SPSS kann man die Daten aus der data.csv importieren, dann mittels "Dateneigenschaften kopieren", kann man die Feldbeschriftungen aus der SPSS Label-Datei (data_spss_label.sav) mit dem Datensatz zusammenführen

@Excel
In der vom Script erzeugten data.csv werden wird "." (Punkt) als Dezimaltrenner verwendet, womit Excel ein Problem hat, denn diese Zahlen werden automatisch beim Öffnen in einen Datumswert umgewandelt. Wenn man diesen dann wieder in eine Zahl zurückwandelt erhält man einen FALSCHEN Wert. Daher folgender TIPP:

a) data.csv mit einem Texteditor (z.B. Notepad) öffnen
b) alle "." (Punkt) durch "," (Komma, Beistrich) ersetzen.

Dann kann man die data.csv problemlos mit Excel öffnen.

Schöne Grüsse

Lalit Kaltenbach
