-- =============================================================
-- SCRIPT DIAGNOSTIC + FIX : Bières — Problèmes sous-familles
-- Points N°1 et N°2 (session juin 2026)
-- Lancer d'abord les requêtes DIAGNOSTIC, puis les FIX
-- =============================================================

-- ============================================================
-- SECTION 1 — DIAGNOSTIC
-- ============================================================

-- 1a. Toutes les sous-familles présentes dans l'univers bières (famille_code 20-30)
SELECT
    f.code  AS famille_code,
    f.nom   AS famille_nom,
    sf.id   AS sf_id,
    sf.code AS sf_code,
    sf.nom  AS sf_nom,
    sf.slug AS sf_slug,
    COUNT(p.id) AS nb_produits
FROM ob_catalogue_sous_familles sf
INNER JOIN ob_catalogue_familles f ON sf.famille_id = f.id
LEFT JOIN ob_catalogue_produits p ON p.sous_famille_id = sf.id AND p.marque IN ('1','2')
WHERE f.code BETWEEN 20 AND 30
GROUP BY sf.id, f.code, f.nom, sf.code, sf.nom, sf.slug
ORDER BY f.code, sf.nom;

-- 1b. Sous-familles en double dans l'univers bières (même slug, IDs différents)
SELECT
    sf.slug,
    COUNT(*)                  AS nb_doublons,
    GROUP_CONCAT(sf.id)       AS ids,
    GROUP_CONCAT(sf.nom)      AS noms,
    GROUP_CONCAT(f.code)      AS famille_codes
FROM ob_catalogue_sous_familles sf
INNER JOIN ob_catalogue_familles f ON sf.famille_id = f.id
WHERE f.code BETWEEN 20 AND 30
GROUP BY sf.slug
HAVING COUNT(*) > 1;

-- 1c. Produits dans BIERE PRESSION COMPTOIR (Point N°1)
SELECT p.code, p.nom, f.code AS famille_code, f.nom AS famille_nom, sf.nom AS sf_nom
FROM ob_catalogue_produits p
INNER JOIN ob_catalogue_familles f  ON p.famille_id     = f.id
INNER JOIN ob_catalogue_sous_familles sf ON p.sous_famille_id = sf.id
WHERE sf.slug = 'biere-pression-comptoir'
ORDER BY f.code, p.nom;

-- 1d. Sous-familles qui apparaissent dans les FÛTs bières (pour identifier les intrus — Point N°2)
SELECT
    sf.slug AS sf_slug,
    sf.nom  AS sf_nom,
    f.code  AS famille_code,
    COUNT(*) AS nb_produits
FROM ob_catalogue_produits p
INNER JOIN ob_catalogue_familles f ON p.famille_id = f.id
LEFT JOIN ob_catalogue_sous_familles sf ON p.sous_famille_id = sf.id
WHERE (f.code BETWEEN 20 AND 30)
  AND p.marque IN ('1','2')
  AND (
      UPPER(COALESCE(sf.nom,'')) LIKE '%FUT%'
      OR UPPER(p.nom) LIKE '%FUT%'
      OR p.nom REGEXP '(^|[^0-9])([0-9]{1,2})L([^A-Z]|$)'
  )
GROUP BY sf.slug, sf.nom, f.code
ORDER BY sf.nom;

-- 1e. Produits "Boissons sucrées VP" dans l'univers bières
SELECT p.code, p.nom, f.code AS famille_code, f.nom AS famille_nom, sf.nom AS sf_nom
FROM ob_catalogue_produits p
INNER JOIN ob_catalogue_familles f  ON p.famille_id     = f.id
INNER JOIN ob_catalogue_sous_familles sf ON p.sous_famille_id = sf.id
WHERE sf.slug = 'boissons-sucrees-vp'
  AND f.code BETWEEN 20 AND 30
ORDER BY p.nom;


-- ============================================================
-- SECTION 2 — CORRECTION (adapter selon résultats diagnostic)
-- ============================================================

-- FIX 2a : Fusionner les doublons de "Cidre fût"
-- (Remplacer @SF_ID_GARDER et @SF_ID_SUPPRIMER par les vrais IDs du diagnostic 1b)
-- Étape 1 : réassigner les produits du doublon vers l'entrée principale
-- UPDATE ob_catalogue_produits
--     SET sous_famille_id = @SF_ID_GARDER
--     WHERE sous_famille_id = @SF_ID_SUPPRIMER;
-- Étape 2 : supprimer le doublon de la table sous-familles
-- DELETE FROM ob_catalogue_sous_familles WHERE id = @SF_ID_SUPPRIMER;

-- FIX 2b : Corriger famille_id des produits mal affectés dans BIERE PRESSION COMPTOIR
-- (Point N°1 — si les produits "Eaux aromatisées" et "Boissons sucrées VP"
--  ont famille_code=20 au lieu de leur vraie famille)
-- Trouver la vraie famille pour les eaux :
--   SELECT id FROM ob_catalogue_familles WHERE code = 60 LIMIT 1; -- ou code Softs/Eaux
-- Exemple de correction (adapter @BONNE_FAMILLE_ID) :
-- UPDATE ob_catalogue_produits
--     SET famille_id = @BONNE_FAMILLE_ID
--     WHERE sous_famille_id IN (SELECT id FROM ob_catalogue_sous_familles WHERE slug = 'biere-pression-comptoir')
--       AND nom LIKE '%EAU%';
-- UPDATE ob_catalogue_produits
--     SET famille_id = @BONNE_FAMILLE_SOFTS_ID
--     WHERE sous_famille_id IN (SELECT id FROM ob_catalogue_sous_familles WHERE slug = 'biere-pression-comptoir')
--       AND nom LIKE '%SUCR%';

-- FIX 2c : Après correction prod, supprimer la sous-famille fantôme si elle n'a plus de produits
-- DELETE FROM ob_catalogue_sous_familles
--     WHERE slug = 'biere-pression-comptoir'
--       AND (SELECT COUNT(*) FROM ob_catalogue_produits WHERE sous_famille_id = ob_catalogue_sous_familles.id) = 0;
