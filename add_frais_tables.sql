-- Ajout de la table etat
CREATE TABLE IF NOT EXISTS `etat` (
  `id` char(2) NOT NULL,
  `libelle` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `etat` (`id`, `libelle`) VALUES
('CL', 'Saisie clôturée'),
('CR', 'Fiche créée, saisie en cours'),
('RB', 'Remboursée'),
('VA', 'Validée et mise en paiement');

-- Ajout de la table frais_forfait
CREATE TABLE IF NOT EXISTS `frais_forfait` (
  `id` char(3) NOT NULL,
  `libelle` char(20) DEFAULT NULL,
  `montant` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `frais_forfait` (`id`, `libelle`, `montant`) VALUES
('ETP', 'Forfait Etape', 110.00),
('KM', 'Frais Kilométrique', 0.62),
('NUI', 'Nuitée Hôtel', 80.00),
('REP', 'Repas Restaurant', 25.00);

-- Ajout de la table fiche_frais
CREATE TABLE IF NOT EXISTS `fiche_frais` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int(11) NOT NULL,
  `mois` char(7) NOT NULL,
  `nb_justificatifs` int(11) DEFAULT NULL,
  `montant_valide` decimal(10,2) DEFAULT NULL,
  `date_modif` date DEFAULT NULL,
  `id_etat` char(2) NOT NULL DEFAULT 'CR',
  PRIMARY KEY (`id`),
  KEY `id_etat` (`id_etat`),
  KEY `id_utilisateur` (`id_utilisateur`),
  CONSTRAINT `fiche_frais_ibfk_1` FOREIGN KEY (`id_etat`) REFERENCES `etat` (`id`),
  CONSTRAINT `fiche_frais_ibfk_2` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ajout de la table ligne_frais_forfait
CREATE TABLE IF NOT EXISTS `ligne_frais_forfait` (
  `id_fiche_frais` int(11) NOT NULL,
  `id_frais_forfait` char(3) NOT NULL,
  `quantite` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_fiche_frais`,`id_frais_forfait`),
  KEY `id_frais_forfait` (`id_frais_forfait`),
  CONSTRAINT `ligne_frais_forfait_ibfk_1` FOREIGN KEY (`id_fiche_frais`) REFERENCES `fiche_frais` (`id`),
  CONSTRAINT `ligne_frais_forfait_ibfk_2` FOREIGN KEY (`id_frais_forfait`) REFERENCES `frais_forfait` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ajout de la table ligne_frais_hors_forfait
CREATE TABLE IF NOT EXISTS `ligne_frais_hors_forfait` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_fiche_frais` int(11) NOT NULL,
  `libelle` varchar(100) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `montant` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_fiche_frais` (`id_fiche_frais`),
  CONSTRAINT `ligne_frais_hors_forfait_ibfk_1` FOREIGN KEY (`id_fiche_frais`) REFERENCES `fiche_frais` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ajout de la table comptable (copie de la structure de utilisateur)
CREATE TABLE IF NOT EXISTS `comptable` LIKE `utilisateur`;

-- Ajout de l'utilisateur admin
-- Le mot de passe sera hashé par l'application lors de la création
INSERT INTO `utilisateur` (`nom`, `prenom`, `email`, `mdp`, `type_utilisateur`) 
VALUES ('Admin', 'Admin', 'admin@gsb.fr', 'admin', 'admin')
ON DUPLICATE KEY UPDATE `nom`=VALUES(`nom`), `prenom`=VALUES(`prenom`), `email`=VALUES(`email`), `mdp` = VALUES(`mdp`), `type_utilisateur` = VALUES(`type_utilisateur`);

ALTER TABLE `utilisateur` MODIFY `mdp` VARCHAR(255) NOT NULL; 