# 🌐 Webapp med Formulär, CI/CD & Säker Infrastruktur

En enkel men komplett webbapplikation som visar mitt namn och innehåller ett kontaktformulär. Applikationen är hostad på en säker Ubuntu-server i Azure med bastion host, reverse proxy och blob storage för lagring av formulärsvar.

---

# Infrastrukturuppsättning i Azure

För att strukturera projektets infrastruktur på ett säkert och skalbart sätt har jag inledningsvis skapat en resursgrupp vid namn **rg-webapp-mysql.** Denna resursgrupp fungerar som en samlad plats för alla relaterade resurser inom projektet.

Därefter konfigurerades ett virtuellt nätverk med namnet **vnet-webapp-mysql**, vilket är baserat på adressrymden **10.0.0.0/16.** Detta nätverk är indelat i flera undernät (subnets), där varje del har ett tydligt syfte och ansvar för olika komponenter i lösningen:

| Subnät                | Adressrymd    | Syfte                                                                                                                         |
| --------------------- | ------------- | ----------------------------------------------------------------------------------------------------------------------------- |
| `app-subnet`          | `10.0.1.0/24` | Här placeras applikationsservern som kör webbapplikationen.                                                                   |
| `db-subnet`           | `10.0.2.0/24` | Används för att isolera MySQL-databasen i en separat zon.                                                                     |
| `reverseproxy-subnet` | `10.0.3.0/24` | Innehåller en reverse proxy-server som hanterar trafik mellan klient och applikation.                                         |
| `bastionhost-subnet`  | `10.0.4.0/24` | Innehåller en Bastion Host som möjliggör säker administration (SSH) utan att exponera virtuella maskiner direkt mot internet. |

Denna uppdelning av nätverket möjliggör förbättrad säkerhet, enklare nätverksadministration och tydligare separering mellan olika typer av resurser.

# Säkerhetsfördelar med nätverksarkitekturen

Genom att segmentera det virtuella nätverket i dedikerade subnät för olika funktioner uppnås flera viktiga säkerhetsfördelar:

- 🔐 **Nätverksisolering:** Databasen ligger i ett separat db-subnet utan direkt exponering mot internet, vilket minimerar risken för intrång.
- 🔁 **Trafikstyrning och filtrering:** Med ett separat reverseproxy-subnet kan inkommande trafik kontrolleras och filtreras innan den når applikationen. Detta möjliggör implementation av t.ex. brandväggsregler, TLS-terminering och lastbalansering.
- 👨‍💻 **Säker administration:** Genom att använda en Bastion Host i ett eget bastionhost-subnet undviks behovet av att öppna portar för SSH direkt mot de virtuella maskinerna. All åtkomst sker via Azure Bastion, vilket erbjuder en säker och spårbar inloggningsmetod.

# Skapande av applikationsserver (VM)

Efter att nätverksinfrastrukturen var på plats skapades en virtuell maskin som fungerar som applikationsserver. Denna server är ansvarig för att köra webbapplikationen samt ansluta till databasen via LEMP-stacken (Linux, Nginx, MySQL, PHP).

**Konfiguration av virtuell maskin:**

| Parameter             | Värde                                                 |
| --------------------- | ----------------------------------------------------- |
| **Namn**              | `vm-webapp`                                           |
| **Region**            | North Europe                                          |
| **Image**             | Ubuntu Server 22.04 LTS – x64 Gen2                    |
| **Storlek**           | Standard\_B1s (kostnadseffektiv för utbildningssyfte) |
| **Virtuellt nätverk** | `vnet-webapp-mysql`                                   |
| **Subnet**            | `app-subnet` (`10.0.1.0/24`)                          |

För automatiserad installation och konfiguration av programvaran användes en cloud-init-fil. Denna fil ser till att alla nödvändiga komponenter för applikationsdrift installeras och konfigureras vid uppstart.

# Databaskonfiguration: Azure Database for MySQL – Flexible Server

För att hantera lagring och hantering av applikationens databas skapades en Azure Database for MySQL – Flexible Server. Denna tjänst är optimerad för utvecklingsmiljöer och erbjuder hög tillgänglighet, automatiska säkerhetskopior samt flexibel resurshantering.

| Parameter                  | Värde                                               |
| -------------------------- | --------------------------------------------------- |
| **Servernamn**             | `mysql-webapp-01`                                   |
| **Region**                 | North Europe                                        |
| **MySQL-version**          | 5.7                                                 |
| **Arbetsbelastning**       | Development (optimerad för test och utveckling)     |
| **Beräkning + lagring**    | Burstable B1ms (1 vCore, 2 GiB RAM), 20 GiB lagring |
| **Anslutningstyp**         | Privat anslutning via VNet-integration              |
| **Virtuellt nätverk**      | `vnet-webapp-mysql`                                 |
| **Subnet**                 | `db-subnet` (`10.0.2.0/24`)                         |
| **Privat DNS-integration** | Ja                                                  |
| **Privat DNS-zon**         | `mysql.database.azure.com`                          |

# Applikationsfiler och deras funktion

För att bygga webbapplikationen skapades ett antal viktiga filer, vilka utgör applikationens backend, frontend och stil. Nedan följer en översikt av varje fil och dess syfte:

| Filnamn               | Syfte                                                                                                |
| --------------------- | ---------------------------------------------------------------------------------------------------- |
| `database_setup.php`  | Skript för att skapa och konfigurera databastabeller som används av applikationen.                   |
| `index.html`          | Startsidan för webbapplikationen som visar användarens namn och grundläggande information.           |
| `contact_form.html`   | HTML-formulär där användare kan skicka in kontaktmeddelanden.                                        |
| `on_post_contact.php` | Backend-skript som hanterar formulärdata (POST) från `contact_form.html` och sparar det i databasen. |
| `on_get_messages.php` | Backend-skript som hämtar och returnerar sparade meddelanden från databasen (GET).                   |
| `style.css`           | CSS-fil som innehåller stilmallarna för webbapplikationens visuella utseende.                        |

**Sammanfattning**

Tillsammans utgör dessa filer en komplett webbapplikation med både frontend och backend som möjliggör insamling, lagring och visning av användarinmatad data, samtidigt som användarupplevelsen förstärks med en separat stilfil.

## 🧰 Funktionalitet

- ✅ Visar startsida (`index.html`)
- ✅ Kontaktformulär (via `contact_form.html`)
- ✅ Skickar formulärdata till backend (`on_post_contact.php`)
- ✅ Visar sparade meddelanden (`on_get_messages.php`)
- ✅ PHP-script för databas-setup (`database_setup.php`)
- ✅ Responsiv design via `style.css`
- ✅ Lagrar formulärsvar i Azure Blob Storage

---

## 📁 Mappstruktur

/webapp

├── contact_form.html

├── database_setup.php

├── index.html

├── on_get_messages.php

├── on_post_contact.php

└── style.css

# ☁️ Infrastruktur & Deployment

## 🖥️ Applikationsserver (Appserver)

- **Operativsystem:** Ubuntu 24.04 LTS
- Kör webbapplikationen (PHP, MySQL-anslutningar etc.)
- Hanterar logik och databasinteraktion
- Mottar trafik från reverse proxy-servern

## 🔄 Reverse Proxy Server

- **Operativsystem**: Ubuntu 24.04 LTS
- **Webbserver:** NGINX
- Hanterar inkommande trafik och skyddar backend-servrar
- Terminerar HTTPS-anslutningar (SSL-certifikat via Let's Encrypt)
- Proxyar trafiken vidare till appservern på interna IP-adresser
- Förbättrar säkerheten genom att begränsa direkt åtkomst till applikationsservern

## 🔐 Bastion Host (Säker SSH-access)

- **Operativsystem:** Ubuntu 24.04 LTS
- Säker gateway för SSH-anslutningar till interna servrar
- Används som hopppunkt (ProxyJump) vid fjärråtkomst och i CI/CD pipelines
- Begränsar åtkomst och ökar säkerheten vid serverhantering

## ☁️ Azure Blob Storage

- Lagrar formulärdata och filer från webbapplikationen
- Separat lagring utanför applikationsservern för bättre skalbarhet och säkerhet
- Hanteras via API-anrop från applikationen

## 🔐 Säkerhet

- ✅ Endast bastion host är öppen mot internet
- ✅ Webbserver är endast tillgänglig via bastionen (SSH ProxyJump)
- ✅ SSH-nycklar hanteras säkert via GitHub Secrets
- ✅ Automatisk uppdatering av serverns known_hosts via pipeline

## 🔐 Hantering av SSH-nycklar

För att möjliggöra säker och automatiserad deployment från GitHub Actions till webbservern används SSH-nyckelbaserad autentisering:
- Ett nyckelpar (privat + publik) genereras lokalt
- Den privata nyckeln (id_rsa) läggs till som en GitHub Secret i repositoryt (SSH_PRIVATE_KEY)
- Den publika nyckeln (id_rsa.pub) läggs till i filen ~/.ssh/authorized_keys på:
  - Bastion Host
  - Appservern

GitHub Actions använder sedan nyckeln för att ansluta till servern via SSH och köra deployment-kommandon (t.ex. git pull)

## 🔒 HTTPS och SSL/TLS-säkerhet

Applikationen är säkrad med HTTPS via ett kostnadsfritt SSL/TLS-certifikat från **Let's Encrypt**. Certifikatet hanteras automatiskt med hjälp av **Certbot**, och installationen sker direkt på reverse proxy-servern (Nginx).

Funktionaliteten bygger på följande:

- **Port 443** är öppen på reverse proxy-servern för att tillåta HTTPS-trafik.
- **Domänen (`wavvy.se`) pekar till reverse proxy-serverns IP** via A-poster i DNS (Loopia).
- **Certbot** används för att automatiskt:
  - Generera och installera SSL-certifikat
  - Förnya certifikaten regelbundet
- **Nginx** är konfigurerad att lyssna på både port 80 och 443, och omdirigerar trafik från HTTP till HTTPS.

Därmed säkerställs:
- Krypterad kommunikation mellan klient och server
- Skydd mot man-in-the-middle-attacker
- Förbättrad SEO och användarförtroende

Exempel på tillgänglig tjänst:
https://wavvy.se


## 🚀 CI/CD-pipeline med GitHub Actions

Applikationen använder en CI/CD-pipeline (Continuous Integration & Continuous Deployment) via GitHub Actions för att automatiskt:

- Bygga och testa kod (vid behov)
- Ansluta till webbservern via SSH genom en bastion host och reverse proxy
- Utföra git pull för att hämta senaste versionen av koden till servern
- Köra eventuella byggsteg (t.ex. npm install, composer install, etc.)
- Starta om applikationen vid behov (t.ex. med pm2)

## 🛠️ Processflöde

- När en ändring pushas till main-branchen startas arbetsflödet automatiskt.
- En GitHub Actions-runner sätter upp en säker SSH-anslutning till bastion/reverse proxy.
- På målsystemet hämtas den senaste koden.
- Tjänsten startas om så att ändringarna blir synliga direkt.

## 🔐 Säkerhet i pipelinen

- SSH-nycklar hanteras säkert via GitHub Secrets
- ProxyJump (bastion host) används för säker åtkomst till interna miljöer
- Endast privata nycklar används (lösenordsfri autentisering)
- HTTPS är aktiverat på webbservern via Let's Encrypt och Nginx

Cloud-init: Automatiserad serverkonfiguration

# Application Server Setup (LEMP Stack on Azure)

Det här är en `cloud-config`-fil som automatiserar installationen av en LEMP-stack (Linux, Nginx, MySQL, PHP) på en Ubuntu-server – anpassad för att ansluta till Azure MySQL.

## 📄 cloud-config.yaml

```yaml
#cloud-config
# Application server with LEMP stack for Azure MySQL connectivity

package_update: true

# Add external repositories
apt:
  sources:
    ondrej-php:
      source: ppa:ondrej/php

packages:
  - software-properties-common  # Required for adding PPAs
  - nginx          # Web server
  - php8.1-fpm     # PHP 8.1 FastCGI Process Manager (specific version)
  - php8.1-mysql   # PHP 8.1 MySQL extension
  - php8.1-cli     # PHP 8.1 command line interface
  - mysql-client   # MySQL client for testing
  - unzip          # For extracting files

write_files:
  # Configure Nginx to serve PHP files
  - path: /etc/nginx/sites-available/default
    content: |
      server {
          listen 80;
          root /var/www/html;
          index index.php index.html index.nginx-debian.html;

          server_name _;

          location / {
              try_files $uri $uri/ =404;
          }

          location ~ \.php$ {
              include snippets/fastcgi-php.conf;
              fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
          }

          # Health check endpoint
          location /health {
              access_log off;
              return 200 "Application server healthy\n";
              add_header Content-Type text/plain;
          }
      }

runcmd:
  # Set proper permissions for web directory
  - chown -R www-data:www-data /var/www/html
  - chmod -R 755 /var/www/html

  # Restart and enable services
  - systemctl restart nginx
  - systemctl enable nginx
  - systemctl restart php8.1-fpm
  - systemctl enable php8.1-fpm


  📄 Bilaga 1: database_setup.php

  <?php
// Azure MySQL Database configuration
// Replace with your actual Azure MySQL server details
$host = getenv('MYSQL_HOST') ?: '[YOUR-MYSQL-SERVER-NAME].mysql.database.azure.com';
$dbname = getenv('MYSQL_DATABASE') ?: 'contactforms';
$username = getenv('MYSQL_USERNAME') ?: 'mysqladmin';
$password = getenv('MYSQL_PASSWORD') ?: 'SecurePassword123!';

try {
    // Connect to Azure MySQL with SSL
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_SSL_CA => '/etc/ssl/certs/ca-certificates.crt'
    ]);

    // Create table if not exists (database-first approach for learning)
    $pdo->exec("CREATE TABLE IF NOT EXISTS contacts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_created_at (created_at)
    )");

    echo "<!-- Database connection successful -->\n";

} catch(PDOException $e) {
    // Log error and show user-friendly message
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please check configuration. Error: " . $e->getMessage());
}
?>
