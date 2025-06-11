-- Script pour ajouter des médicaments aux rapports qui n'en ont pas encore
-- Identifie les rapports sans médicaments
CREATE TEMPORARY TABLE rapports_sans_medicaments AS
SELECT r.id
FROM rapport r
LEFT JOIN offrir o ON r.id = o.idRapport
WHERE o.idRapport IS NULL;

-- Sélectionne tous les médicaments disponibles
SET @total_medicaments = (SELECT COUNT(*) FROM medicament);

-- Vérifie qu'il y a des médicaments dans la base
SET @min_medicament_id = NULL;
SELECT MIN(id) INTO @min_medicament_id FROM medicament;

-- Ajoute un médicament aléatoire pour chaque rapport sans médicament
INSERT INTO offrir (idRapport, idMedicament, quantite)
SELECT 
    r.id as idRapport,
    (SELECT id FROM medicament ORDER BY RAND() LIMIT 1) as idMedicament,
    FLOOR(RAND() * 5) + 1 as quantite
FROM rapports_sans_medicaments r
WHERE @min_medicament_id IS NOT NULL
LIMIT 500;  -- Limitons à 500 ajouts pour éviter une surcharge

-- Pour les rapports restants, utilisons un autre lot de médicaments aléatoires
INSERT INTO offrir (idRapport, idMedicament, quantite)
SELECT 
    r.id as idRapport,
    (SELECT id FROM medicament ORDER BY RAND() LIMIT 1) as idMedicament,
    FLOOR(RAND() * 5) + 1 as quantite
FROM rapports_sans_medicaments r
WHERE @min_medicament_id IS NOT NULL
AND r.id NOT IN (SELECT idRapport FROM offrir)
LIMIT 500;  -- Un autre lot de 500

-- Nettoyage
DROP TEMPORARY TABLE IF EXISTS rapports_sans_medicaments;

-- Affiche le nombre de rapports qui ont maintenant des médicaments
SELECT COUNT(*) as nb_rapports_avec_medicaments 
FROM (
    SELECT DISTINCT idRapport FROM offrir
) as rapport_med; 