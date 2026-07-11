# BandElo

BandElo ist ein funktionaler LAMP-Prototyp: Benutzer melden sich mit Spotify an, importieren ihre Top-20-Interpreten und stimmen im Gewinner-bleibt-Modus paarweise ab. Der angeklickte Interpret bleibt in der nächsten Wahl stehen; jede persönliche Runde endet nach genau 20 Votes mit dem aktuellen Favoriten. Daraus entstehen globale Elo-Werte und ein Community-Leaderboard.

## Voraussetzungen

- Debian/Ubuntu mit Apache
- PHP 8.2+ mit `pdo_mysql`, `curl`, `json`
- MariaDB 10.x (extern möglich)
- Composer
- Spotify Developer App mit Scope `user-top-read`

## Projektstruktur

- `public/`: DocumentRoot, Seiten und JSON-Endpunkte
- `public/api/`: `vote.php`, `leaderboard.php`, `next-pair.php`, `reset-tournament.php`
- `src/`: objektorientierte App-, Repository- und Service-Klassen
- `config/bootstrap.php`: `.env`, Session und PDO-Bootstrap
- `database/schema.sql`: vollständiges MariaDB-Schema
- `scripts/deploy.sh`: automatisiertes Server-Deployment
- `scripts/install.php`: Installationsassistent für Schema und Konfigurationsprüfung

## Installation lokal oder nach Deployment

```bash
composer install
cp .env.example .env
# .env ausfüllen
composer install-app
```

Wenn du Composer bewusst als `root` ausführst, setze die von Composer erwartete Umgebungsvariable:

```bash
COMPOSER_ALLOW_SUPERUSER=1 composer install-app
```

`DB_PASSWORD` darf leer bleiben, wenn der MariaDB-Benutzer kein Passwort verwendet.

Benötigte `.env`-Werte:

```dotenv
DB_HOST=
DB_PORT=3306
DB_NAME=
DB_USER=
DB_PASSWORD=
SPOTIFY_CLIENT_ID=
SPOTIFY_CLIENT_SECRET=
SPOTIFY_REDIRECT_URI=
```

## Deployment

Auf einem frischen Debian-/Ubuntu-Server im geklonten Repository ausführen:

```bash
sudo APP_DIR=/var/www/bandelo SERVER_NAME=example.org bash scripts/deploy.sh
```

Das Skript installiert Apache, PHP-Erweiterungen, MariaDB-Client und Composer, kopiert das Projekt, erstellt `.env`, setzt Schreibrechte, aktiviert benötigte Apache-Module und richtet einen VirtualHost mit `public/` als DocumentRoot ein. Es erzeugt keine Datenbank.

Danach:

```bash
cd /var/www/bandelo
sudo nano .env
COMPOSER_ALLOW_SUPERUSER=1 composer install-app
sudo systemctl reload apache2
```

## Spotify Developer Console

1. App in der Spotify Developer Console erstellen.
2. Redirect URI exakt auf `SPOTIFY_REDIRECT_URI` setzen, z. B. `https://example.org/auth/callback.php`.
3. Client ID und Client Secret in `.env` eintragen.
4. Die Anwendung fordert ausschließlich `user-top-read` an.

## Datenbank vorbereiten

Eine leere MariaDB-Datenbank und einen Benutzer extern anlegen. Danach führt `composer install-app` `database/schema.sql` aus. Das Schema enthält Tabellen, Indexe, Foreign Keys, Unique Constraints und Check Constraints.

## Apache konfigurieren

Der VirtualHost muss auf `public/` zeigen. Beispiel siehe `scripts/deploy.sh`. `mod_rewrite` wird aktiviert, obwohl die Anwendung direkte PHP-Dateien nutzt.

## API-Dokumentation

### `GET /api/next-pair.php`

Authentifiziert. Liefert zu Beginn zwei zufällige Künstler des aktuellen Benutzers. Nach einem Vote bleibt der Gewinner als `champion_id` stehen und wird mit einem neuen Herausforderer gepaart. Die Antwort enthält `votes`, `votes_remaining` und `complete`; `complete` wird nach 20 Votes gesetzt.

### `POST /api/vote.php`

Authentifiziert. JSON-Body:

```json
{"artist_a_id":1,"artist_b_id":2,"winner_artist_id":1}
```

Benötigt Header `X-CSRF-Token`. Aktualisiert Elo, speichert den Vote und merkt den Gewinner in der Session als aktuellen Favoriten für die nächste Paarung. Nach 20 Votes lehnt der Endpunkt weitere Votes ab, bis die Runde zurückgesetzt wird.

### `POST /api/reset-tournament.php`

Authentifiziert. Benötigt Header `X-CSRF-Token`. Setzt die persönliche Gewinner-bleibt-Runde zurück.

### `GET /api/leaderboard.php?limit=10`

Öffentlich. Liefert Künstler nach globalem Elo sortiert inklusive Siegen, Niederlagen, Matches und Gewinnquote.

## Fehlerbehebung

- **Spotify Callback ungültig:** Redirect URI in Spotify und `.env` muss exakt identisch sein. Die Callback-Seite zeigt Spotify-Fehlerdetails an und schreibt sie zusätzlich ins PHP Error Log.
- **PDO-Verbindungsfehler:** DB-Host, Port, Benutzerrechte und Firewall prüfen.
- **Voting ohne Login:** `/vote.php` leitet nicht angemeldete Besucher zurück zur Startseite.
- **Leere Voting-Seite:** Der Spotify-Account muss Top Artists liefern; beim ersten Login werden maximal 20 importiert.
- **403/404 in Apache:** DocumentRoot auf `public/` und Dateirechte prüfen.
