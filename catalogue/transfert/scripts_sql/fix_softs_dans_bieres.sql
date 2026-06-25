-- =============================================================
-- SCRIPT FIX : Produits avec famille_id bières (20-30)
--              mais sous_famille_id pointant vers softs/eaux
-- Sous-familles concernées :
--   60.12 = famille code 60 (EAUX),   sous-famille code 12 → EAUX BOITE AROMATISEES
--   40.16 = famille code 40 (SOFTS),  sous-famille code 16 → BOISSONS SUCREES VP
-- =============================================================
-- Ordre d'exécution : DIAGNOSTIC → FIX → VERIFICATION
-- ⚠️  Un ré-import CSV avec les mêmes données ERP erronées
--     ré-écrira famille_id. Corriger l'ERP (Declic) en priorité.
-- =============================================================

-- ============================================================
-- SECTION 1 — DIAGNOSTIC
-- ============================================================

-- 1a. Produits concernés (famille bières mais sf 60.12 ou 40.16)
SELECT
    p.code_produit,
    p.nom                        AS produit_nom,
    f_prod.code                  AS famille_code_actuelle,
    f_prod.nom                   AS famille_actuelle,
    f_sf.code                    AS famille_sf_correcte,
    f_sf.nom                     AS famille_sf_nom,
    sf.code                      AS sf_code,
    sf.nom                       AS sf_nom
FROM ob_catalogue_produits        p
JOIN ob_catalogue_familles        f_prod ON p.famille_id      = f_prod.id
JOIN ob_catalogue_sous_familles   sf     ON p.sous_famille_id = sf.id
JOIN ob_catalogue_familles        f_sf   ON sf.famille_id     = f_sf.id
WHERE f_prod.code BETWEEN 20 AND 30          -- produit dans bières (erroné)
  AND (
       (f_sf.code = 60 AND sf.code = 12)     -- SF 60.12 EAUX BOITE AROMATISEES
    OR (f_sf.code = 40 AND sf.code = 16)     -- SF 40.16 BOISSONS SUCREES VP
  )
ORDER BY sf.nom, p.nom;

-- 1b. Vérifier que les familles cibles existent (doit retourner 2 lignes)
SELECT id, code, nom FROM ob_catalogue_familles
WHERE code IN (40, 60)
ORDER BY code;


-- ============================================================
-- SECTION 2 — CORRECTION
-- ============================================================

-- FIX : corriger famille_id vers la famille de la sous-famille
--        (60 pour eaux-boite-aromatisees, 40 pour boissons-sucrees-vp)
UPDATE ob_catalogue_produits      p
JOIN ob_catalogue_familles        f_prod ON p.famille_id      = f_prod.id
JOIN ob_catalogue_sous_familles   sf     ON p.sous_famille_id = sf.id
JOIN ob_catalogue_familles        f_sf   ON sf.famille_id     = f_sf.id
SET p.famille_id = f_sf.id                   -- aligner famille produit sur famille SF
WHERE f_prod.code BETWEEN 20 AND 30
  AND (
       (f_sf.code = 60 AND sf.code = 12)
    OR (f_sf.code = 40 AND sf.code = 16)
  );


-- ============================================================
-- SECTION 3 — VÉRIFICATION POST-FIX
-- ============================================================

-- Doit retourner 0 ligne après correction
SELECT p.code_produit, p.nom, f_prod.code AS fcode_actuelle, sf.nom AS sf_nom
FROM ob_catalogue_produits        p
JOIN ob_catalogue_familles        f_prod ON p.famille_id      = f_prod.id
JOIN ob_catalogue_sous_familles   sf     ON p.sous_famille_id = sf.id
JOIN ob_catalogue_familles        f_sf   ON sf.famille_id     = f_sf.id
WHERE f_prod.code BETWEEN 20 AND 30
  AND (
       (f_sf.code = 60 AND sf.code = 12)
    OR (f_sf.code = 40 AND sf.code = 16)
  );

-- Produits désormais dans la bonne famille
SELECT p.code_produit, p.nom, f.code AS fcode, f.nom AS fnomom, sf.nom AS sf_nom
FROM ob_catalogue_produits        p
JOIN ob_catalogue_familles        f  ON p.famille_id      = f.id
JOIN ob_catalogue_sous_familles   sf ON p.sous_famille_id = sf.id
JOIN ob_catalogue_familles        f_sf ON sf.famille_id   = f_sf.id
WHERE (f_sf.code = 60 AND sf.code = 12)
   OR (f_sf.code = 40 AND sf.code = 16)
ORDER BY sf.nom, p.nom;
