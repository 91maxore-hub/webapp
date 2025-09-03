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