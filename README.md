Dieses Modul wird nicht mehr weiter gepflegt, da es in ein anderes Repo umgezogen ist, bitte die Info im Thread im IP-Symcon Forum lesen: https://www.symcon.de/forum/threads/36262-Modul-Tasmota


# IPS-Sonoff
Mit diesem Modul ist es möglich geflashte Sonoff Geräte kinderleicht in IPS zu integrieren.
Kommuniziert wird über das MQTT Prokotoll, somit muss der Status der Sonoff Geräte nicht gepollt werden

## Inhaltverzeichnis
1. [Voraussetzungen](#1-voraussetzungen)
2. [Installation](#2-installation)
3. [Konfiguration IPS-SonoffSwitch](#3-konfiguration-ips-sonoffswitch)

## 1. Voraussetzungen

* [Mosquitto Broker](https://mosquitto.org)
* [MQTT Client](https://github.com/Schnittcher/IPS-KS-MQTT) - akteull eine abgeänderte Version von [IPS_MQTT von thomasf68](https://github.com/thomasf68/IPS_MQTT)
* mindestens IPS Version 4.1

## 2. Installation
Ich erkläre hier nur die Installation des IPS Moduls für den Broker, bitte Google benutzen.

IPS-KS-MQTT Client:
```
https://github.com/Schnittcher/IPS-KS-MQTT.git
```

IPS-SonoffSwitch:
```
https://github.com/Schnittcher/IPS-Sonoff.git
```
Als erstes kann der IPS-SonoffSwitch angelegt werden, dadurch wird als Parent Instanz der MQTT Splitter (IPS_KS_MQTTClient) angelegt.
Der Splitter (IPS_KS_MQTTClient) erstellt automatisch einen Client Sockt mit dem Namen: MQTT Client Socket, in diesem Client Socket wird die Verbindung zum Mosquitto Broker eingetragen.
Sobald dies geschehen ist muss die Instanz des Splitters (IPS_KS_MQTTClient) über den Button "Active Instance" aktiviert werden.

## 3. Konfiguration IPS-SonoffSwitch

Feld | Beschreibung
------------ | -------------
Sonoff MQTT Topic | Name des Sonoff Moduls, ist in den MQTT Einstellungen des Sonoff Switch zu finden
Power On| 1 oder ON - Je nachdem wie das Sonoff Modul geflasht wurde
Power Off| 0 oder OFF - Je nachdem wie das Sonoff Modul geflasht wurde
Full Topic| Full Topic des Sonoff Moduls, ist in den MQTT Einstellungen des Sonoff Switch zu finden
