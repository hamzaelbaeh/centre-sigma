# Centre Sigma / CenterFlow — NOOR ACADEMY

Clone fonctionnel d’un SaaS de gestion scolaire (CenterFlow) en **Laravel + Blade**.

- **École démo :** NOOR ACADEMY  
- **Année :** 2026/2027  
- **Login :** `admin` / `admin123`

---

## العربية — ملخص سريع

نظام إدارة مدرسة (CenterFlow clone) مبني بـ Laravel.
- المدرسة التجريبية: **NOOR ACADEMY** — السنة **2026/2027**
- الدخول: **admin** / **admin123**
- محلياً: SQLite — على Hostinger: MariaDB/MySQL مع جذر الموقع على مجلد `public/`

### التشغيل المحلي (عربي)
```bash
cd centre-sigma
composer install
cp .env.example .env   # أو استخدم .env الموجود (sqlite)
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```
افتح: http://127.0.0.1:8000

### النشر على Hostinger (عربي)
1. ارفع الملفات عبر FTP/Git.
2. في لوحة Hostinger: وجّه **Document Root** إلى مجلد `public/`.
3. أنشئ قاعدة MariaDB واملأ `.env`:
   - `DB_CONNECTION=mysql`
   - `DB_HOST=localhost` + اسم القاعدة والمستخدم وكلمة المرور
4. نفّذ: `composer install --no-dev`, `php artisan key:generate`, `php artisan migrate --seed`, `php artisan storage:link`
5. صلاحيات الكتابة: `storage/` و `bootstrap/cache/`

---

## Prérequis

- PHP **8.3+** (8.4 OK)
- Composer
- Extensions PHP : sqlite / pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json, fileinfo

## Installation locale (SQLite)

```bash
cd centre-sigma
export PATH="$HOME/.local/bin:$PATH"   # si besoin
composer install
cp .env.example .env                   # optionnel si .env existe déjà
# Pour SQLite local, dans .env :
#   DB_CONNECTION=sqlite
#   # DB_DATABASE laisse vide → database/database.sqlite
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

Ouvrir **http://127.0.0.1:8000** → connexion `admin` / `admin123`.

## Modules

| Groupe | Pages |
|--------|--------|
| **SCOLAIRE** | Élèves, Parents / Tuteurs, Classes, Enseignants, Matières, Emploi du temps, Présences |
| **FINANCES** | Paiements élèves, Config. frais, Paie enseignants, Employés admin., Paie employés, Dépenses |
| **GESTION** | Élèves sortants, Formations, Années scolaires |
| **SYSTÈME** | Documents, Rapports, Utilisateurs, Paramètres |

### Logique métier clé

- **Paiements :** `RESTANT = MONTANT − PAYÉ` → statuts *Non payé / Partiel / Soldé* ; encaissement partiel autorisé ; **Générer les mensualités** depuis Config. frais.
- **Paie employés :** `NET = BRUT + PRIMES − AVANCES − RETENUES`.
- **Départs :** soft — l’élève n’est jamais effacé, statut mis à jour.
- **Présences :** enregistrement par lot (radios Présent/Absent/Retard + Justifié/Motif).
- **Paramètres :** couleurs + aperçu live de la barre latérale.

## Déploiement Hostinger (MariaDB / MySQL)

1. Uploader le projet (Git / FTP).
2. **Pointer la racine du domaine vers le dossier `public/`** (Document Root).
3. Créer une base MariaDB dans hPanel.
4. Configurer `.env` :

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votredomaine.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=votre_base
DB_USERNAME=votre_user
DB_PASSWORD=votre_mot_de_passe
```

5. Sur le serveur :

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force --seed
php artisan storage:link
php artisan config:cache
php artisan route:cache
```

6. Droits d’écriture : `storage/` et `bootstrap/cache/`.

> Ne pas exposer `.env`, `vendor/` hors de `public`, ni la racine Laravel comme Document Root.

## Design

- Police : Inter / system-ui  
- Fond `#f0f4f8`, texte `#1e293b`  
- Sidebar 252px `#081f52`, topbar 60px blanche  
- Accent or `#f7be1d`, cartes blanches ~12px  

## Structure utile

- Spec : `CENTERFLOW_SPEC.md`
- Captures : `docs/shots/`
- CSS : `public/css/app.css`

## Licence

Projet de démonstration / clone éducatif pour NOOR ACADEMY.

Voir **HOSTINGER.md** pour le deploiement Hostinger (public/, MySQL, artisan).
