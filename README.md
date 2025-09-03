# 🌐 Webapp med Formulär, CI/CD & Säker Infrastruktur

En enkel men komplett webbapplikation som visar mitt namn och innehåller ett kontaktformulär. Applikationen är hostad på en säker Ubuntu-server i Azure med bastion host, reverse proxy och blob storage för lagring av formulärsvar.

---

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
├── contact_form.html # Kontaktformulär
├── database_setup.php # Initierar databas och blob-storage
├── index.html # Startsida med mitt namn
├── on_get_messages.php # Hämtar meddelanden
├── on_post_contact.php # Tar emot formulärdata
└── style.css # CSS-stil

## ☁️ Infrastruktur & Deployment

🖥️ Värdmiljö (Ubuntu-server i Azure)
- Operativsystem: Ubuntu 24.04 LTS
- Webbserver: NGINX
- Reverse proxy skyddar applikationen från direkt åtkomst
- Bastion host används för säker SSH-anslutning
- Azure Blob Storage används för att lagra formulärdata

## 🔐 Säkerhet

- ✅ Endast bastion host är öppen mot internet
- ✅ Webbserver är endast tillgänglig via bastionen (SSH ProxyJump)
- ✅ SSH-nycklar hanteras säkert via GitHub Secrets
- ✅ Automatisk uppdatering av serverns known_hosts via pipeline

## 🔄 CI/CD Pipeline

- GitHub Actions används för automatisk deployment
- Pipen körs vid varje push till main
- Workflow:
   1. Push triggar GitHub Action
   2. Action ansluter till webbserver via bastion (SSH ProxyJump)
   3. Gör git pull på servern i /var/www/html
   4. Startar om/uppdaterar applikationen

