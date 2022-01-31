# Infoportal-Suche
Dieses Wordpress-Plugin soll bei einer Suche auch externe APIs durchsuchen und die Ergebnisse dieser Suchanfragen an das Endergebnis anhängen. Genutzt werden hierbei einfache REST-Anfragen und das dementsprechende En- und Decoding des Inhalts. Einstellungen sollen es außerdem ermöglichen weitere Webseiten (welche auf den vorgegebenen APIs basieren) hinzuzufügen und ein einfaches hinzufügen von den Tokens erlauben.

## Unterstütze Schnittstellen
- Moodle Kurse
- Wordpress (WIP)

## Nutzung
Nach der Aktivierung des Plugins sollte es in der Admin-Oberfläche in der linken Leiste unter Einstellungen -> Licks (WIP) Optionen geben um alles anzupassen
- 31.01.2022: Zurzeit ist das auch die Ausgabe zum Debuggen

## Voraussetzungen
- Eine Webseite, welche auf den vorgegebenen APIs basiert
- Einen Nutzer auf dieser Webseite, welche genügend Rechte besitzt, um REST-Anfragen abzusenden
- Einen generierten Token, über welchen diese Suchanfragen ausgeführt werden können

