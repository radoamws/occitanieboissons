# Occitanie Boissons — Catalogue B2B

Site de commande de boissons en ligne à destination des clients professionnels (CHR).

**Production :** https://catalogue.occitanieboissons.com/

---

## Architecture

```
occitanieboissons/
├── catalogue/               # Module principal (public)
│   ├── index.php            # Page catalogue : mega-menu, filtres, listing produits
│   ├── includes/
│   │   ├── configuration.php  # DB, URLs, mails, import CSV depuis Declic
│   │   └── functions.php      # Fonctions partagées
│   ├── gallery/             # Assets CSS/JS/images
│   ├── transfert/
│   │   └── produits/        # Fichiers CSV exportés depuis l'ERP Declic
│   │       ├── TARIFINTERNET_COMPLET.CSV
│   │       ├── ART_PRIX_STO.CSV
│   │       ├── PRODUCT.CSV
│   │       └── TARIFINTERNET.CSV
│   └── pdf/                 # Génération de factures PDF
├── administration/          # Back-office (gestion catalogue, utilisateurs, actualités)
├── logistique/              # Suivi DLUO, emplacements stocks
├── ART_PRIX_STO.CSV         # Copies CSV à la racine (legacy)
├── TARIFINTERNET_COMPLET.CSV
└── occitanila010301.sql     # Dump de référence de la base de données
```

---

## Stack technique

- **PHP 8.2** — pur, sans framework
- **MySQL** via PDO — base `occitanila010301`
- **HTML / CSS / JS vanilla**
- **XAMPP 8** en environnement local
- **Hébergeur OVH** en production

---

## ERP Declic → Site

Le catalogue est alimenté automatiquement par des fichiers CSV exportés depuis l'ERP **Declic** :

1. Declic exporte les CSV dans `catalogue/transfert/produits/`
2. `configuration.php` parse et importe les données à chaque chargement (avec cache d'état `.ob_import_state.json` pour éviter les imports redondants)
3. Les produits, familles, catégories, fabricants et pays sont créés ou mis à jour en base

---

## Univers produits

| Univers | Familles Declic | Filtres |
|---|---|---|
| Bières | codes 20–30 | Style (= code_categorie), Brasserie, Pays |
| Vins | codes 10, 11 | Type, Appellation, Domaine, Pays |
| Spiritueux | code 1 | Type, Distillerie, Pays |
| Softs | codes 40–75 | Type, Marque |
| Promotions | remises | — |

> **Note bières :** "Couleur" et "Style" correspondent au même champ `code_categorie` dans Declic — ils sont fusionnés en une seule colonne "Style" dans le catalogue.

---

## Configuration locale (développement)

1. Installer XAMPP 8 avec PHP 8.2 et MySQL
2. Placer le projet dans `htdocs/occitanieboissons/`
3. Importer le dump `catalogue/occitanila010301.sql` dans MySQL
4. Vérifier dans `catalogue/includes/configuration.php` :
   ```php
   $env = "development";  // ne pas changer pour le local
   $host = "localhost";
   $user = "root";
   $pass = "";
   $dbname = "occitanila010301";
   ```
5. Accéder à : http://localhost/occitanieboissons/catalogue/

---

## Déploiement en production

1. Déposer les fichiers (hors `configuration.php` si credentials locaux dedans)
2. Dans `configuration.php`, décommenter les credentials prod et commenter ceux du local :
   ```php
   $host = "occitanila010301.mysql.db";
   $user = "occitanila010301";
   $pass = "...";  // voir gestionnaire de mots de passe
   $dbname = "occitanila010301";
   ```
   Ou positionner la variable d'environnement `OB_ENV=production` sur le serveur.
3. Si une migration SQL est nécessaire, exécuter les scripts dans `migrations/` (voir HISTORIQUE.md)

---

## Base de données

| Table | Description |
|---|---|
| `ob_catalogue_produits` | Produits (code, nom, prix, stock, famille, catégorie, pays, fabricant, degré…) |
| `ob_catalogue_familles` | Familles produits |
| `ob_catalogue_sous_familles` | Sous-familles (Bouteilles 33cl, Fûts 20L, BIB…) |
| `ob_catalogue_categories` | Styles/couleurs bière, types vin |
| `ob_catalogue_fabriquants` | Brasseries, domaines, distilleries |
| `ob_catalogue_pays` | Pays d'origine |
| `ob_users` | Utilisateurs (droits : catalogue, admin, logistique) |

---

## Contacts emails

| Rôle | Email |
|---|---|
| Commandes | commande@occitanieboissons.com |
| Commercial / Newsletter | commercial@occitanieboissons.com |
| Contact | contact@occitanieboissons.com |
