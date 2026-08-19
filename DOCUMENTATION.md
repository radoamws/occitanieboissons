# Documentation fonctionnelle — Occitanie Boissons Catalogue

> **Mise à jour** : 2026-06-25 (rev. 3)  
> **À tenir à jour** à chaque modification du catalogue (`catalogue/index.php`, `catalogue/includes/configuration.php`).

---

## Table des matières

1. [Architecture générale](#1-architecture-générale)
2. [Flux de données ERP → CSV → DB → Affichage](#2-flux-de-données)
3. [Classifications en base de données](#3-classifications-en-base-de-données)
   - 3.1 Familles
   - 3.2 Sous-familles
   - 3.3 Catégories
   - 3.4 Produits
4. [Univers d'affichage](#4-univers-daffichage)
5. [Packs de conditionnement](#5-packs-de-conditionnement)
6. [Personnalisations en dur (hardcoded)](#6-personnalisations-en-dur)
7. [Méga-menu et filtres sidebar](#7-méga-menu-et-filtres-sidebar)
8. [Incohérences et points d'attention](#8-incohérences-et-points-dattention)
9. [Requêtes SQL de diagnostic](#9-requêtes-sql-de-diagnostic)
10. [Historique des modifications majeures](#10-historique-des-modifications-majeures)

---

## 1. Architecture générale

| Couche | Technologie | Rôle |
|--------|-------------|------|
| ERP | Declic (logiciel métier) | Source de vérité produits/prix/stock |
| Export | CSV (`/catalogue/transfert/produits/`) | Bridge ERP → site |
| Import | `configuration.php` | Parse CSV → upsert DB à chaque page load |
| DB | MySQL, base `occitanila010301` | Cache produits + référentiels |
| Affichage | `catalogue/index.php` (~2 450 lignes) | Logique catalogue, méga-menu, filtres, listing |
| Front | HTML/CSS/JS vanilla | Rendu sans framework |

### Fichiers clés

| Fichier | Rôle |
|---------|------|
| `catalogue/index.php` | Toute la logique catalogue : univers, filtres, méga-menu, sidebar, listing |
| `catalogue/includes/configuration.php` | DB, sessions, import CSV complet |
| `catalogue/includes/functions.php` | Fonctions partagées |
| `catalogue/transfert/produits/TARIFINTERNET_COMPLET.CSV` | Import principal (26 colonnes) |
| `catalogue/transfert/produits/ART_PRIX_STO.CSV` | Complément famille/sous-famille |
| `catalogue/occitanila010301.sql` | Dump de référence de la DB prod |

---

## 2. Flux de données

```
ERP Declic
    ↓ export manuel ou planifié
CSV dans /transfert/produits/
    ↓ à chaque page load (si fichier modifié)
configuration.php : parse + upsert
    ↓
ob_catalogue_familles       ← ob_slugify(LIBELLE FAMILLE)
ob_catalogue_sous_familles  ← ob_slugify(LIBELLE SS FAMILLE) [clé: (famille_id, code)]
ob_catalogue_categories     ← INSERT ON DUPLICATE KEY UPDATE [clé: code]
ob_catalogue_fabriquants
ob_catalogue_pays
ob_catalogue_produits       ← UPDATE si code_produit existe, INSERT sinon
    ↓
index.php : requêtes DB → méga-menu + sidebar + listing
```

### Détail du fichier TARIFINTERNET_COMPLET.CSV

Séparateur `;`, 26 colonnes :

| # | Colonne | Champ DB |
|---|---------|----------|
| 1 | ID | — |
| 2 | CODE_PRODUIT | `ob_catalogue_produits.code_produit` |
| 3 | LIBELLE | `nom` |
| 4 | LIBELLE COMP. | `nom_sup` |
| 5 | CODE FAMILLE | → `famille_id` via lookup |
| 6 | CODE SS FAMILLE | → `sous_famille_id` via lookup |
| 7 | LIBELLE FAMILLE | nom de la famille |
| 8 | CODE CATEGORIE | `categorie` |
| 9 | CATEGORIE | → `ob_catalogue_categories.nom` |
| 10 | CODE FABRIQUANT | `brasserie` |
| 11 | FABRIQUANT | — |
| 12 | CODE PAYS | `pays_code` |
| … | … | … |
| 18 | CODE_TVA | `code_tva` |
| 19 | UV_CAISSE | `uv_caisse` |
| 20 | STOCK_UV | `stock` |
| 21 | CONTENANCE | `contenance` (en cl) |
| 22 | DEGRE | `degre` |
| 23 | MARQUE | `marque` enum('0','1','2') — 0=inactif, 1=actif catalogue, 2=actif complet |
| 24 | CONSIGNE_PAR_CONDITION_VENTE | `consigne_caisse` |
| 25 | CODE CONDITION_VENTE | `condition_vente` |
| 26 | CONDITION_VENTE | — |

---

## 3. Classifications en base de données

### 3.1 Familles (`ob_catalogue_familles`)

Structure : `id`, `code` (int, UNIQUE), `nom`, `slug`

| Code famille | Nom ERP | Univers catalogue |
|-------------|---------|-------------------|
| 1 | ALCOOL | Spiritueux |
| 10 | VINS | Vins |
| 11 | VINS PERMANENTS | Vins |
| 20 | BIERES | Bières |
| 30 | CIDRES | Bières (famille 30 ≤ 30, dans plage 20-30) |
| 40 | SOFTS | Softs |
| 50 | JUS DE FRUIT | Softs |
| 60 | EAUX | Softs |
| 70 | *(code ERP)* | Softs |
| 75 | *(code ERP)* | Softs |
| 80 | *(code ERP)* | Softs |
| 85 | *(code ERP)* | Softs |
| 87 | ARTICLES DIVERS | Hors catalogue public |
| 89 | MATERIELS | Hors catalogue public |
| 91 | SERVICES | Hors catalogue public |

> **Note** : D'autres familles peuvent exister selon l'évolution du catalogue ERP. La liste Softs [40,50,60,70,75,80,85] est hardcodée dans `$univers_definitions` — à mettre à jour si une nouvelle famille Softs est créée dans Declic.

### 3.2 Sous-familles (`ob_catalogue_sous_familles`)

Structure : `id`, `famille_id` (FK), `code` (int), `nom`, `slug`  
Contraintes uniques : `(famille_id, code)` **ET** `(famille_id, slug)` — voir incohérence §8.10

Exemples pour famille BIERES (code 20) :

| Code SF | Nom | Notes |
|---------|-----|-------|
| 1 | BIERE FUT 30L | Pack : fûts |
| 2 | BIERE FUT 20L | Pack : fûts |
| 3 | BIERE FUT 10/15L | Pack : fûts |
| 4 | BIERE FUT CASK 100/120L | Pack : fûts |
| 5 | BIERE FUT CASK 50L | Pack : fûts |
| 6 | BIERE FUT CASK 30L | Pack : fûts |
| 7 | BIERE FUT CASK 20L | Pack : fûts |
| 8 | BIERE FUT CASK 10/15L | Pack : fûts |
| 9 | BIERE FUT PETKEGS 10/20L | Pack : fûts |
| 10 | BIERE 20/25/33/35CL VP | Pack : bouteilles-canettes |
| 11 | BIERE 50/75CL VP | Pack : bouteilles-canettes |
| 12 | BIERE MAGNUM 150/200CL | Pack : bouteilles-canettes |
| 15 | BIERE 75VP | Pack : bouteilles-canettes |
| 20 | BIERE PRESSION COMPTOIR | ⚠️ Voir incohérences §8 |
| 25 | BIERE 33/44/50 CL CANETTE | Pack : bouteilles-canettes ⚠️ Voir §8 |

> La nomenclature exacte des sous-familles est gérée par l'ERP. Les slugs sont générés automatiquement via `ob_slugify()`.

### 3.3 Catégories (`ob_catalogue_categories`)

Structure : `id`, `code` (int, UNIQUE), `nom`, `slug`  
Les catégories représentent le **style** (bières) ou le **type** (vins, spiritueux).

#### Catégories Bières (codes 1–19, 28–33)

| Code | Nom DB | Notes |
|------|--------|-------|
| 1 | IPA | |
| 2 | SOUR | |
| 3 | BLANCHE | |
| 4 | AMBREE / ROUSSE / RED ALE | |
| 5 | STOUT / PORTER | |
| 6 | BARREL AGED | |
| 7 | LAGER / PILS / ALE / BLONDE | |
| 8 | PALE ALE | |
| 9 | GOSE | |
| 10 | TRIPLE | |
| 11 | SAISON | |
| 12 | BRUNE | |
| 13 | LAMBIC | |
| 14 | FRUITEE | |
| 15 | STRONG ALE | |
| 16 | GINGER BEER | |
| 17 | BITTER | Ajouté en prod — inclus dans filtres bières |
| 18 | HIVER / NOEL | |
| 19 | GRUIT | Ajouté en prod — inclus dans filtres bières |
| 28 | CIDRE/CIDER | |
| 29 | DUBBLE / DOUBLE GRAINS | |
| 32 | SANS ALCOOL | |
| 33 | QUADRUPLE | Ajouté en prod — inclus dans filtres bières |

#### Catégories Vins (codes 20–26)

| Code | Nom DB |
|------|--------|
| 20 | VIN ROUGE |
| 21 | VIN ROSE |
| 22 | VIN BLANC SEC |
| 23 | VIN BLANC DEMI SEC |
| 24 | VIN BLANC MOELLEUX |
| 25 | VIN BLANC LIQUOREUX |
| 26 | VIN PETILLANT |

#### Catégories Spiritueux (codes 30–31)

| Code | Nom DB |
|------|--------|
| 30 | TOURBE |
| 31 | LEGEREMENT TOURBE |

#### Catégories Champagne — HARDCODÉES (codes 34–37)

⚠️ **Ces catégories N'EXISTENT PAS dans `ob_catalogue_categories`**. Elles sont définies entièrement en dur dans `index.php` et ne sont jamais importées depuis Declic.

| Code | Label hardcodé | Variable |
|------|---------------|---------|
| 34 | BLANC DE NOIRS | `$champagne_cat_ids`, `$champagne_appellation_items` |
| 35 | BLANC DE BLANCS | idem |
| 36 | DEMI SEC | idem |
| 37 | BRUT | idem |

### 3.4 Produits (`ob_catalogue_produits`)

Structure — colonnes principales :

| Colonne | Type | Notes |
|---------|------|-------|
| `code_produit` | int | Code ERP Declic (unique) |
| `nom` | varchar(200) | charset latin1 ⚠️ (voir §8) |
| `marque` | enum('0','1','2') | 0=hors catalogue, 1=actif, 2=actif complet |
| `famille_id` | int FK | → `ob_catalogue_familles.id` |
| `sous_famille_id` | int FK | → `ob_catalogue_sous_familles.id` |
| `categorie` | int | Code catégorie (= `ob_catalogue_categories.code`) |
| `brasserie` | int | Code fabriquant |
| `pays_code` | varchar(50) | Code pays |
| `contenance` | float | En cl |
| `degre` | float | En % vol. |

> Seuls les produits avec `marque IN ('1','2')` sont affichés dans le catalogue public.

---

## 4. Univers d'affichage

Les univers sont définis dans `$univers_definitions` (index.php, ~ligne 52). Chaque produit est rattaché à un univers si son `famille_id` OU sa `categorie` correspond aux critères.

La clause WHERE est construite par `$build_universe_where($key, $alias)` :

```sql
-- Exemple pour 'bieres' :
(p.categorie IN (1,2,...,33) OR p.famille_id IN (SELECT id FROM ob_catalogue_familles WHERE code BETWEEN 20 AND 30))
```

### 4.1 Bières

| Critère | Valeur |
|---------|--------|
| Familles | code BETWEEN 20 AND 30 |
| Catégories | 1–19, 28–29, 32–33 (tous les styles bière) |
| Packs | bouteilles-canettes, fûts |
| Sidebar filtres | Format/Contenance (sous-famille), Style (catégorie), Brasserie, Pays |

> Les codes de famille **20** (BIERES) et **30** (CIDRES) sont dans la plage. Les cidres apparaissent donc dans l'univers bières.

### 4.2 Vins

| Critère | Valeur |
|---------|--------|
| Familles | codes [10, 11] |
| Catégories | 20–26 + 34–37 (champagne, hardcodé) |
| Packs | bouteilles, bib |
| Sidebar filtres | Type (catégorie), Appellation (sous-famille), Domaine, Pays |

> Les champagnes (cat. 34–37) n'existent pas en DB. Ils sont gérés entièrement en dur.

### 4.3 Spiritueux

| Critère | Valeur |
|---------|--------|
| Familles | codes [1] (ALCOOL) |
| Catégories | 30, 31 (TOURBE, LEGEREMENT TOURBE) |
| Packs | bouteilles, fûts |
| Sidebar filtres | Type (sous-famille), Distillerie, Pays |

> Les codes catégorie 30–31 sont redondants avec `famille_codes=[1]` car tous les produits tourbés sont dans la famille ALCOOL.

### 4.4 Softs

| Critère | Valeur |
|---------|--------|
| Familles | codes [40, 50, 60, 70, 75, 80, 85] |
| Catégories | aucune (liste vide) |
| Packs | bouteilles, fûts |
| Sidebar filtres | Type (famille), Marque |

> ⚠️ Si Declic crée une nouvelle famille Soft avec un code hors de cette liste, elle n'apparaîtra pas. La liste doit être mise à jour manuellement dans `$univers_definitions['softs']['famille_codes']`.

### 4.5 Promotions

| Critère | Valeur |
|---------|--------|
| Familles | slug = 'remises' (via `$univers_famille_filter_slugs`) |
| Catégories | aucune |
| Packs | aucun |
| Sidebar filtres | Famille, Type |

---

## 5. Packs de conditionnement

Les packs filtrent les produits par type de contenant. La logique est dans `$build_pack_scope($universKey, $packSlug, $alias)`.

### Définition des packs

| Pack (slug) | Label | Univers |
|-------------|-------|---------|
| `bouteilles-canettes` | Bouteilles et canettes | Bières |
| `futs` | Fûts | Bières, Spiritueux, Softs |
| `bouteilles` | Bouteilles | Vins, Spiritueux, Softs |
| `bib` | BIB | Vins |

### Logique de détection fûts

Un produit est considéré **fût** si l'une des conditions est vraie :
```sql
UPPER(sf.nom) REGEXP '(^|[^A-Z])FUT([^A-Z]|$)'   -- FUT mot entier dans nom sous-famille
OR UPPER(p.nom) REGEXP '(^|[^A-Z])FUT([^A-Z]|$)' -- FUT mot entier dans nom produit
OR UPPER(p.nom) REGEXP '(^|[^0-9])([0-9]{1,2})L([^A-Z]|$)'  -- litre : 2L, 5L, 10L, 20L...
```

> **Attention** : Le REGEXP litre matche `XL` où X est 1–2 chiffres, L non suivi d'une majuscule. Peut générer des faux positifs si un nom produit contient `XL` comme suffixe numérique.  
> **Note** : Le match `FUT` utilise un REGEXP à frontière de mot `(^|[^A-Z])FUT([^A-Z]|$)` pour éviter les faux positifs sur des mots comme "FUTUR" (ex : "RETOUR VERS LE FUTUR").

### Logique de détection BIB (vins)

```sql
UPPER(sf.nom) LIKE '%BIB%'
OR UPPER(p.nom) LIKE '%BIB%'
OR (p.contenance IN (300,500,1000) AND UPPER(p.nom) NOT LIKE '%MAGNUM%')
```

---

## 6. Personnalisations en dur

### 6.1 Champagne (OBLIGATOIREMENT en dur)

Les champagnes sont une personnalisation **pérenne** car ils n'existent pas dans Declic/CSV.

**Fichier** : `catalogue/index.php`, ~ligne 185–195

```php
$champagne_cat_ids = [34, 35, 36, 37];
$champagne_appellation_items = [
    ['code' => 34, 'nom' => 'BLANC DE NOIRS'],
    ['code' => 35, 'nom' => 'BLANC DE BLANCS'],
    ['code' => 36, 'nom' => 'DEMI SEC'],
    ['code' => 37, 'nom' => 'BRUT'],
];
```

**Ce qui est hardcodé pour champagne** :
- Les 4 codes catégorie (34–37) dans `$univers_definitions['vins']['categorie_ids']`
- Les labels (BLANC DE NOIRS, BLANC DE BLANCS, DEMI SEC, BRUT)
- L'entrée "CHAMPAGNE" avec sous-dropdown dans le méga-menu vins
- Le checkbox champagne-group dans le filtre sidebar vins (TYPE)
- La section APPELLATION sidebar avec les 4 types champagne
- La logique JS : cliquer CHAMPAGNE injecte `filtre_categorie[]=34,35,36,37`

### 6.2 Exclusions de sous-familles (temporaires ou structurelles)

**Fichier** : `catalogue/index.php`, ~ligne 83

```php
$univers_excluded_sous_famille_slugs = [
    'bieres' => [
        '*'    => ['biere-pression-comptoir'],
        'futs' => ['biere-33-44-50-cl-canette', 'boissons-sucrees-vp'],
    ],
];
```

| Exclusion | Raison | Statut |
|-----------|--------|--------|
| `biere-pression-comptoir` (tous packs) | Produits d'autres univers (eaux, softs) mal catégorisés dans l'ERP | À retirer après correction données ERP |
| `eaux-boite-aromatisees` (tous packs) | Famille 60 (EAUX) affectée par erreur dans famille bières | À retirer après correction données ERP |
| `boissons-sucrees-vp` (tous packs) | Famille 40 (SOFTS) affectée par erreur dans famille bières | À retirer après correction données ERP |
| `biere-33-44-50-cl-canette` (pack fûts) | Contient des canettes — certains noms de produits triggent le pattern fût | Structurel — à conserver |

> Pour supprimer une exclusion devenue inutile, retirer son slug de l'array ci-dessus. Pour vérifier, lancer les requêtes SQL du §9.

### 6.3 Degrés d'alcool (buckets de filtrage)

**Fichier** : `catalogue/index.php`, lignes 23–29

```php
$degre_buckets_definitions = [
    ['key' => '0-3',    'min' => 0.0, 'max' => 3.0,  'label' => "0° à 2,9°"],
    ['key' => '3-5',    'min' => 3.0, 'max' => 5.0,  'label' => "3° à 4,9°"],
    ['key' => '5-7',    'min' => 5.0, 'max' => 7.0,  'label' => "5° à 6,9°"],
    ['key' => '7-9',    'min' => 7.0, 'max' => 9.0,  'label' => "7° à 8,9°"],
    ['key' => '9-plus', 'min' => 9.0, 'max' => null,  'label' => "9° et +"],
];
```

### 6.4 Définition des univers (logique métier, hardcodé par design)

Les codes famille/catégorie par univers sont des **règles métier** qui reflètent le plan de comptes ERP Declic. Ils doivent rester en dur et être mis à jour manuellement si Declic crée de nouveaux codes.

```php
$univers_definitions = [
    'bieres'     => ['categorie_ids' => [1..19, 28,29,32,33], 'famille_code_min' => 20, 'famille_code_max' => 30],
    'vins'       => ['categorie_ids' => [20..26, 34..37],       'famille_codes' => [10, 11]],
    'spiritueux' => ['categorie_ids' => [30, 31],               'famille_codes' => [1]],
    'softs'      => ['categorie_ids' => [],                     'famille_codes' => [40,50,60,70,75,80,85]],
    'promotions' => ['categorie_ids' => []],
];
```

---

## 7. Méga-menu et filtres sidebar

### Méga-menu

Le méga-menu est **entièrement dynamique** (lecture DB) à l'exception du groupe Champagne.

| Univers | Col. 1 | Col. 2 | Col. 3 | Col. 4 | Spécial |
|---------|--------|--------|--------|--------|---------|
| Bières | Style (catégorie) | Brasserie | Pays | — | — |
| Vins | Type (catégorie) | Appellation (sous-famille) | Domaine | Pays | **CHAMPAGNE** hardcodé |
| Spiritueux | Type (sous-famille) | Distillerie | Pays | — | — |
| Softs | Type (famille) | Marque | — | — | — |

Les **titres de colonnes** ("Style", "Brasserie", etc.) sont hardcodés dans le HTML de `index.php`. Ce sont des labels UI qui n'ont pas vocation à être en DB.

### Filtres sidebar

| Univers | Section | Source données | Hardcodé |
|---------|---------|---------------|----------|
| Bières | Format / Contenance | `sous_familles_top` (DB) | Titre uniquement |
| Bières | Style | `categories` (DB) | Titre uniquement |
| Bières | Brasserie | `fabricants_all` (DB) | Titre uniquement |
| Bières | Pays | `pays_all` (DB) | Titre uniquement |
| Vins | Type | `categories` (DB) — sans champagne | Titre + logique champagne-group |
| Vins | Appellation | `sous_familles_all` (DB) + champagne | Champagne items en dur |
| Vins | Domaine | `fabricants_all` (DB) | Titre uniquement |
| Vins | Pays | `pays_all` (DB) | Titre uniquement |
| Spiritueux | Type | `sous_familles_all` (DB) | Titre uniquement |
| Spiritueux | Distillerie | `fabricants_all` (DB) | Titre uniquement |
| Softs | Type | `familles` (DB) | Titre uniquement |
| Softs | Marque | `fabricants_all` (DB) | Titre uniquement |

---

## 8. Incohérences et points d'attention

### 8.1 Charset latin1 vs utf8mb4

`ob_catalogue_produits` utilise `charset=latin1` (COLLATE latin1_swedish_ci).  
Toutes les autres tables utilisent `utf8mb4`.  
→ **Risque** : Les noms de produits avec caractères spéciaux (accents, tirets longs, €) peuvent s'afficher incorrectement.  
→ **Ne pas corriger sans migration data complète** (ALTER TABLE + CONVERT TO charset utf8mb4 + vérification de tous les produits).

### 8.2 BIERE PRESSION COMPTOIR (sous-famille 20.20)

Des produits appartenant à d'autres univers (eaux aromatisées famille 60, boissons sucrées famille 40) étaient assignés à la sous-famille "BIERE PRESSION COMPTOIR" dans famille BIERES.  
→ **Correction ERP en cours**. En attendant : exclusion en dur dans `$univers_excluded_sous_famille_slugs`.  
→ **Action** : Après correction CSV/DB, retirer `'biere-pression-comptoir'` de la liste d'exclusion.

### 8.3 BIERE 33/44/50 CL CANETTE dans le pack fûts

La sous-famille "BIERE 33/44/50 CL CANETTE" (slug : `biere-33-44-50-cl-canette`) apparaissait dans le pack fûts.  
→ **Cause probable** : Des noms de produits dans cette sous-famille matchent le pattern litre `([0-9]{1,2})L` (ex: produit "KRONENBOURG 50CL" → "50" non suivi de L directement, mais vérifier).  
→ **Exclusion structurelle maintenue** dans le pack fûts.

### 8.4 Cidre fût en double

"Cidre fût" peut apparaître deux fois dans les filtres car :
- Le query `GROUP BY sf.id` ramène deux enregistrements `ob_catalogue_sous_familles` avec slugs différents mais noms identiques (ex: cidre dans famille 20 ET famille 30).
- **Fix PHP** : déduplication par slug déjà implémentée dans `sous_familles_top`.
- **Fix SQL** : lancer le script `catalogue/transfert/scripts_sql/fix_bieres_sous_familles.sql`, section FIX 2a.

### 8.5 Catégories manquantes dans la liste bières (corrigé)

Codes **17** (BITTER), **19** (GRUIT), **33** (QUADRUPLE) existent en DB prod mais n'étaient pas dans `$univers_definitions['bieres']['categorie_ids']`.  
→ **Corrigé** le 2026-06-25 : ajoutés dans la liste.

### 8.6 Code categorie vs ID categorie

Dans `ob_catalogue_produits`, le champ `categorie` stocke le **code** (`ob_catalogue_categories.code`), pas l'`id`.  
Le join doit donc être `JOIN ob_catalogue_categories c ON c.code = p.categorie` (et non `c.id`).  
→ Déjà correct dans index.php. À vérifier si d'autres requêtes sont écrites ad hoc.

### 8.7 Softs famille_codes non exhaustif

`$univers_definitions['softs']['famille_codes'] = [40,50,60,70,75,80,85]`  
Si Declic crée une nouvelle famille Soft avec un code hors de cette liste (ex: 45, 55...), elle n'apparaîtra pas dans l'univers Softs.  
→ **Action** : Vérifier régulièrement dans la DB si de nouveaux codes famille apparaissent dans la plage 40–85.

### 8.8 Champagnes sans produits en DB

Les catégories 34–37 n'existent pas dans `ob_catalogue_categories`. Les produits champagne doivent être dans la famille vins (code 10 ou 11) et avoir le code catégorie correspondant (34–37).  
→ Si aucun produit dans famille 10/11 n'a ces catégories, les champagnes n'afficheront pas de produits (menu présent mais listing vide).  
→ **Action** : S'assurer que l'ERP assigne bien les catégories 34–37 aux produits champagne lors de l'export CSV.

### 8.9 `$univers_famille_filter_slugs` pour promotions

```php
$univers_famille_filter_slugs = ['promotions' => ['remises']];
```

Cette liste force l'univers Promotions à n'afficher que les produits de la famille dont le slug est `remises`. Si le slug change dans la DB (re-import ERP avec un libellé différent), les promotions disparaissent.  
→ Surveiller le slug de la famille Remises en DB.

### 8.10 Crash import : deux codes sous-famille ERP avec le même libellé (corrigé 2026-08-19)

**Symptôme** : `Fatal error: Uncaught PDOException ... Duplicate entry '4-alcool-de-riz' for key 'ob_catalogue_sous_familles.uniq_fam_slug'` dans `configuration.php:416`, qui casse l'import et donc tout le catalogue (crash au chargement de n'importe quelle page, `connexion_.php` inclus).

**Cause** : la table `ob_catalogue_sous_familles` a DEUX contraintes uniques : `(famille_id, code)` et `(famille_id, slug)`. L'export ERP (Déclic, `TARIFINTERNET_COMPLET.CSV`) peut affecter à une même famille deux codes sous-famille différents portant strictement le même libellé (ex. famille ALCOOL/code 1 : code SF 42 et code SF 84, tous deux `ALCOOL DE RIZ`). La fonction `ob_get_or_create_famille_by_code()` protégeait déjà ce cas via un helper `$resolveSlug` qui suffixe le slug en cas de collision (`slug-code`), mais son équivalent `ob_get_or_create_sous_famille_by_code()` n'avait **aucune** protection : elle mettait à jour/insérait le slug brut sans vérifier qu'il n'était pas déjà pris par une autre sous-famille de la même famille → violation de contrainte non catchée → crash fatal de tout le site.

**Fix** : ajout d'un `$resolveSousFamilleSlug` (même principe que pour les familles, scopé par `famille_id`) dans `ob_get_or_create_sous_famille_by_code()` (`configuration.php`). En cas de collision, le second code obtient un slug suffixé par son code ERP (ex. `alcool-de-riz-42`) au lieu de faire planter l'import.

**À expliquer au client** : ce n'est pas un bug introduit par nous — c'est une incohérence dans les données fournies par le logiciel de gestion (Déclic) : deux codes sous-famille différents affectés au même libellé "ALCOOL DE RIZ" sous la famille ALCOOL. Le script d'import n'était pas assez défensif contre ce cas (contrairement à celui des familles) et une exception SQL non interceptée faisait planter tout le site à chaque page. Corrigé : le site fusionne maintenant proprement ce genre de doublon (le second code garde son libellé et reçoit un slug distinct) au lieu de crasher.

**Fichier** : `catalogue/includes/configuration.php` (fonction `ob_get_or_create_sous_famille_by_code`, ~ligne 399-433)

---

## 9. Requêtes SQL de diagnostic

```sql
-- 9.1 Toutes les familles présentes en DB avec leur code
SELECT id, code, nom, slug FROM ob_catalogue_familles ORDER BY code;

-- 9.2 Toutes les catégories en DB
SELECT id, code, nom, slug FROM ob_catalogue_categories ORDER BY code;

-- 9.3 Sous-familles de l'univers bières (famille code 20–30)
SELECT f.code AS fcode, f.nom AS fnomom, sf.code AS sfcode, sf.nom, sf.slug,
       COUNT(p.id) AS nb_produits
FROM ob_catalogue_sous_familles sf
JOIN ob_catalogue_familles f ON sf.famille_id = f.id
LEFT JOIN ob_catalogue_produits p ON p.sous_famille_id = sf.id AND p.marque IN ('1','2')
WHERE f.code BETWEEN 20 AND 30
GROUP BY sf.id ORDER BY f.code, sf.nom;

-- 9.4 Produits dans biere-pression-comptoir
SELECT p.code_produit, p.nom, f.code AS fcode, f.nom AS fnomom, sf.nom AS sfnom
FROM ob_catalogue_produits p
JOIN ob_catalogue_familles f ON p.famille_id = f.id
JOIN ob_catalogue_sous_familles sf ON p.sous_famille_id = sf.id
WHERE sf.slug = 'biere-pression-comptoir';

-- 9.5 Catégories bières présentes sur des produits (pour valider univers_definitions)
SELECT c.code, c.nom, COUNT(p.id) AS nb
FROM ob_catalogue_produits p
JOIN ob_catalogue_familles f ON p.famille_id = f.id
LEFT JOIN ob_catalogue_categories c ON c.code = p.categorie
WHERE f.code BETWEEN 20 AND 30 AND p.marque IN ('1','2') AND p.categorie <> 0
GROUP BY c.code, c.nom ORDER BY c.code;

-- 9.6 Produits détectés comme fûts par sous-famille (pour valider exclusions)
SELECT sf.slug, sf.nom, COUNT(*) AS nb
FROM ob_catalogue_produits p
JOIN ob_catalogue_familles f ON p.famille_id = f.id
LEFT JOIN ob_catalogue_sous_familles sf ON p.sous_famille_id = sf.id
WHERE f.code BETWEEN 20 AND 30 AND p.marque IN ('1','2')
  AND (UPPER(COALESCE(sf.nom,'')) LIKE '%FUT%'
       OR UPPER(p.nom) LIKE '%FUT%'
       OR p.nom REGEXP '(^|[^0-9])([0-9]{1,2})L([^A-Z]|$)')
GROUP BY sf.slug, sf.nom ORDER BY sf.nom;

-- 9.7 Nouvelles familles Softs potentielles (codes entre 40 et 85 hors liste)
SELECT id, code, nom FROM ob_catalogue_familles
WHERE code BETWEEN 40 AND 85
  AND code NOT IN (40,50,60,70,75,80,85)
ORDER BY code;

-- 9.8 Doublons de sous-familles (même slug dans l'univers bières)
SELECT sf.slug, COUNT(*) AS nb, GROUP_CONCAT(sf.id) AS ids
FROM ob_catalogue_sous_familles sf
JOIN ob_catalogue_familles f ON sf.famille_id = f.id
WHERE f.code BETWEEN 20 AND 30
GROUP BY sf.slug HAVING COUNT(*) > 1;

-- 9.9 Produits champagne (vérifier si cat 34-37 existent)
SELECT p.categorie, COUNT(*) AS nb
FROM ob_catalogue_produits p
JOIN ob_catalogue_familles f ON p.famille_id = f.id
WHERE f.code IN (10,11) AND p.categorie IN (34,35,36,37) AND p.marque IN ('1','2')
GROUP BY p.categorie;
```

---

## 10. Historique des modifications majeures

| Date | Description | Fichier(s) |
|------|-------------|-----------|
| 2026-08-19 | Fix crash fatal import : collision de slug entre 2 codes sous-famille ERP portant le même libellé (`uniq_fam_slug`, ex. ALCOOL DE RIZ codes 42/84). Ajout protection anti-collision (suffixe par code) dans `ob_get_or_create_sous_famille_by_code()`, symétrique à celle déjà existante pour les familles. Voir §8.10. | `catalogue/includes/configuration.php` |
| 2026-06-25 | Fix SQL `famille_id` des produits softs/eaux mal affectés en famille bières : script `fix_softs_dans_bieres.sql`. Après fix DB, ces produits apparaissent dans SOFTS et disparaissent de bières. | `scripts_sql/fix_softs_dans_bieres.sql` |
| 2026-06-25 | Correction FORMAT bières bouteilles-canettes : ajout exclusion `eaux-boite-aromatisees` et `boissons-sucrees-vp` (tous packs). Fix faux positif fût sur "FUTUR" : LIKE '%FUT%' → REGEXP mot entier. | `index.php`, `DOCUMENTATION.md` |
| 2026-06-25 | Synchronisation DB locale avec prod. Ajout catégories bières manquantes (17, 19, 33). Suppression code mort `$categorie_labels`. Documentation initiale. | `index.php`, `DOCUMENTATION.md` |
| 2026-06-25 | Déduplication `sous_familles_top` par slug. Mécanisme d'exclusion `$univers_excluded_sous_famille_slugs`. Script SQL `fix_bieres_sous_familles.sql`. | `index.php` |
| 2026-06-16 | Implémentation champagnes (catégories 34–37) : méga-menu vins + sidebar vins TYPE/APPELLATION + logique JS. Fusion Couleur+Style bières en colonne unique "Style". | `index.php`, `catalogue.css` |
