# Historique des modifications — Occitanie Boissons Catalogue

---

## 2026-06-16 — Fusion Couleur + Style des bières

**Fichier modifié :** `catalogue/index.php`
**Base de données :** non touchée — aucune migration nécessaire

### Problème
Dans le mega-menu Bières, deux colonnes séparées "Couleur" et "Style" affichaient des données provenant du même champ `code_categorie` de l'ERP Declic. La séparation était artificielle (faite par détection de mots-clés dans le nom de la catégorie).

### Solution
Fusion des deux colonnes en une seule colonne "Style" contenant toutes les catégories bières.

### Zones impactées
| Zone | Avant | Après |
|---|---|---|
| Mega-menu bières | 2 colonnes : "Couleur" + "Style" | 1 colonne : "Style" (toutes catégories) |
| Barre latérale bières | 2 sections : "Couleur" + "Style" | 1 section : "Style" |
| Page "Voir tout" (`menu_scope`) | `bieres-couleur` et `bieres-style` | `bieres-style` unique (`bieres-couleur` conservé comme alias URL) |
| Config PDF `$pdf_menu` | col_2 "Couleur" + col_3 "Style" | col_2 "Style" (fusionné) |

### Code supprimé
- Fonction `$split_beer_categories()` — séparait les catégories par mots-clés couleur
- Variables `$beer_color_labels`, `$beer_color_items`, `$beer_style_items`
- Variable `$submenu_bieres_split`

---

## Commits git antérieurs (hors Claude)

| Commit | Description |
|---|---|
| `f42f62c` | filtre gauche ok |
| `be8e6e7` | sous menu corrigé |
| `2a5e987` | sous-menu des Voir tout |
| `325b9e4` | affiche le nom des pays (France, Belgique…) et non les codes (FR, BE…) |
| `709167a` | ajustement des menus et rendu dynamique |
| `bfa4c02` | ajustement des menus et rendu dynamique |
| `591b15d` | filtrage ok |
| `55589e6` | Fix encodage factures PDF (Windows-1252) |
| `2627023` | correction des mails |
| `2bae727` | Fix encodage DB Latin-1/UTF-8 |
| `7ecde9a` | Fix fonctions PHP 8.2 dépréciées (utf8_decode/encode) |
| `f90192d` | Fix suppression get_magic_quotes_runtime() (PHP 8) |
| `69e0426` | base de données |
| `a90e0ab` | redesign du menu avec la bonne structure |
| `2e05ca5` | correction de synchro entre les CSV et la base |
| `9c02149` | correction Voir tout des bières + utilisation CSV depuis /transfert |
| `0834e20` | optimisation import CSV |
| `6f9e963` | menu et sous-menu ok |
| `ca1c0a5` | centralisation des paramètres dans includes/configuration.php |
| `9c7c38f` | ajustement visuel des encarts |
| `1fcd817` | changement en encadré des listings |
| `e6c37d0` | menu ok (données manquantes pour conformité) |
| `25fe713` | ajout de l'administration et logistique |
| `1ced9da` | configuration locale |
| `0c9c2e6` | initial |

---

## Migrations SQL

> Aucune migration SQL n'a été nécessaire à ce jour.
> Si une migration est créée, elle sera placée dans `migrations/` avec la convention de nommage `YYYY-MM-DD_description.sql` et documentée ici avant exécution en production.
