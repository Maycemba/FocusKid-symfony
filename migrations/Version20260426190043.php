<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260426190043 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

public function up(Schema $schema): void
{
    // Créer detection_emotion
    $this->addSql('CREATE TABLE detection_emotion (id INT AUTO_INCREMENT NOT NULL, emotion_choisie VARCHAR(50) NOT NULL, emotion_detectee VARCHAR(50) DEFAULT NULL, scores_detection JSON DEFAULT NULL, photo_capture LONGTEXT DEFAULT NULL, correspondance TINYINT DEFAULT NULL, score_confiance DOUBLE PRECISION DEFAULT NULL, created_at DATETIME NOT NULL, humeur_journaliere_id INT NOT NULL, INDEX IDX_1451AC68908D73D1 (humeur_journaliere_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    $this->addSql('ALTER TABLE detection_emotion ADD CONSTRAINT FK_1451AC68908D73D1 FOREIGN KEY (humeur_journaliere_id) REFERENCES humeur_journaliere (id) ON DELETE CASCADE');

    // Créer messenger_messages
    $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');

    // Supprimer les anciennes FK uniquement si elles existent
    $this->addSql('ALTER TABLE carnet_educatif DROP FOREIGN KEY IF EXISTS `carnet_educatif_ibfk_1`');
    $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY IF EXISTS `commentaire_ibfk_1`');
    $this->addSql('ALTER TABLE correction_quiz DROP FOREIGN KEY IF EXISTS `correction_quiz_ibfk_1`');
    $this->addSql('ALTER TABLE exercice DROP FOREIGN KEY IF EXISTS `exercice_ibfk_1`');
    $this->addSql('ALTER TABLE lecon DROP FOREIGN KEY IF EXISTS `lecon_ibfk_1`');
    $this->addSql('ALTER TABLE question DROP FOREIGN KEY IF EXISTS `question_ibfk_1`');
    $this->addSql('ALTER TABLE question_exercice DROP FOREIGN KEY IF EXISTS `question_exercice_ibfk_1`');
    $this->addSql('ALTER TABLE question_quiz DROP FOREIGN KEY IF EXISTS `question_quiz_ibfk_1`');
    $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY IF EXISTS `quiz_ibfk_1`');
    $this->addSql('ALTER TABLE reponse_user_exercice DROP FOREIGN KEY IF EXISTS `reponse_user_exercice_ibfk_1`');
    $this->addSql('ALTER TABLE reponse_user_exercice DROP FOREIGN KEY IF EXISTS `reponse_user_exercice_ibfk_2`');
    $this->addSql('ALTER TABLE reponse_user_exercice DROP FOREIGN KEY IF EXISTS `reponse_user_exercice_ibfk_3`');
    $this->addSql('ALTER TABLE reponse_user_quiz DROP FOREIGN KEY IF EXISTS `reponse_user_quiz_ibfk_1`');
    $this->addSql('ALTER TABLE reponse_user_quiz DROP FOREIGN KEY IF EXISTS `reponse_user_quiz_ibfk_2`');
    $this->addSql('ALTER TABLE reponse_user_quiz DROP FOREIGN KEY IF EXISTS `reponse_user_quiz_ibfk_3`');
    $this->addSql('ALTER TABLE reponse_user_quiz DROP FOREIGN KEY IF EXISTS `reponse_user_quiz_ibfk_4`');
    $this->addSql('ALTER TABLE score DROP FOREIGN KEY IF EXISTS `score_ibfk_1`');
    $this->addSql('ALTER TABLE score DROP FOREIGN KEY IF EXISTS `score_ibfk_2`');
    $this->addSql('ALTER TABLE sessions_de_calme DROP FOREIGN KEY IF EXISTS `sessions_de_calme_ibfk_1`');

    // Supprimer les anciennes tables
    $this->addSql('DROP TABLE IF EXISTS carnet_educatif');
    $this->addSql('DROP TABLE IF EXISTS commentaire');
    $this->addSql('DROP TABLE IF EXISTS correction_quiz');
    $this->addSql('DROP TABLE IF EXISTS cours');
    $this->addSql('DROP TABLE IF EXISTS exercice');
    $this->addSql('DROP TABLE IF EXISTS exercices');
    $this->addSql('DROP TABLE IF EXISTS exercice_enfant');
    $this->addSql('DROP TABLE IF EXISTS jeu');
    $this->addSql('DROP TABLE IF EXISTS lecon');
    $this->addSql('DROP TABLE IF EXISTS question');
    $this->addSql('DROP TABLE IF EXISTS question_exercice');
    $this->addSql('DROP TABLE IF EXISTS question_quiz');
    $this->addSql('DROP TABLE IF EXISTS quiz');
    $this->addSql('DROP TABLE IF EXISTS reponse_exercice');
    $this->addSql('DROP TABLE IF EXISTS reponse_user_exercice');
    $this->addSql('DROP TABLE IF EXISTS reponse_user_quiz');
    $this->addSql('DROP TABLE IF EXISTS score');
    $this->addSql('DROP TABLE IF EXISTS sessions_de_calme');
    $this->addSql('DROP TABLE IF EXISTS utilisateur');

    // alertes_email
    $this->addSql('DROP INDEX IF EXISTS enfant_id ON alertes_email');
    $this->addSql('ALTER TABLE alertes_email CHANGE type_alerte type_alerte VARCHAR(255) NOT NULL, CHANGE date_envoi date_envoi DATETIME DEFAULT NULL, CHANGE session_ids session_ids VARCHAR(255) DEFAULT NULL');

    // emotion
    $this->addSql('ALTER TABLE emotion CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE photo photo LONGTEXT DEFAULT NULL');

    // humeur_journaliere — supprimer l'ancienne FK si elle existe, puis recréer proprement
    $this->addSql('ALTER TABLE humeur_journaliere DROP FOREIGN KEY IF EXISTS `humeur_journaliere_ibfk_1`');
    $this->addSql('ALTER TABLE humeur_journaliere DROP INDEX IF EXISTS emotionid');
    $this->addSql('ALTER TABLE humeur_journaliere CHANGE dateHeure dateHeure DATETIME DEFAULT NULL');
    $this->addSql('ALTER TABLE humeur_journaliere ADD CONSTRAINT FK_1674A11E881453BA FOREIGN KEY (emotionId) REFERENCES emotion (id)');
    $this->addSql('CREATE INDEX IDX_1674A11E881453BA ON humeur_journaliere (emotionId)');
    $this->addSql('ALTER TABLE humeur_journaliere ADD CONSTRAINT `humeur_journaliere_ibfk_1` FOREIGN KEY (emotionId) REFERENCES emotion (id) ON DELETE CASCADE');

    // scenario — supprimer l'ancienne FK si elle existe, puis recréer proprement
    $this->addSql('ALTER TABLE scenario DROP FOREIGN KEY IF EXISTS `scenario_ibfk_1`');
    $this->addSql('ALTER TABLE scenario DROP INDEX IF EXISTS emotionid');
    $this->addSql('ALTER TABLE scenario CHANGE description description LONGTEXT DEFAULT NULL, CHANGE animation animation LONGTEXT DEFAULT NULL');
    $this->addSql('ALTER TABLE scenario ADD CONSTRAINT FK_3E45C8D8881453BA FOREIGN KEY (emotionId) REFERENCES emotion (id)');
    $this->addSql('CREATE INDEX IDX_3E45C8D8881453BA ON scenario (emotionId)');
    $this->addSql('ALTER TABLE scenario ADD CONSTRAINT `scenario_ibfk_1` FOREIGN KEY (emotionId) REFERENCES emotion (id) ON DELETE CASCADE');
}

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE carnet_educatif (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, date_etude DATE DEFAULT NULL, heure_debut TIME DEFAULT NULL, heure_fin TIME DEFAULT NULL, duree_totale INT DEFAULT NULL, lieu VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, matiere VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, type_activite TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, niveau_difficulte ENUM(\'Facile\', \'Moyen\', \'Difficile\') CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, niveau_concentration TINYINT DEFAULT NULL, niveau_agitation TINYINT DEFAULT NULL, nombre_interruptions INT DEFAULT NULL, temps_avant_perte_concentration INT DEFAULT NULL, travaille_seul TINYINT DEFAULT NULL, demande_aide TINYINT DEFAULT NULL, niveau_autonomie TINYINT DEFAULT NULL, travail_termine TINYINT DEFAULT NULL, difficultes TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, points_positifs TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX user_id (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE commentaire (id INT AUTO_INCREMENT NOT NULL, carnet_id INT DEFAULT NULL, date_commentaire DATE DEFAULT NULL, texte_commentaire TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, type_commentaire ENUM(\'Observation\', \'Problème\', \'Suggestion\', \'Amélioration\') CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, INDEX carnet_id (carnet_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE correction_quiz (id_correction INT AUTO_INCREMENT NOT NULL, id_question INT DEFAULT NULL, contenu VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, est_correcte TINYINT DEFAULT NULL, INDEX id_question (id_question), PRIMARY KEY (id_correction)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE cours (id_cours INT AUTO_INCREMENT NOT NULL, titre VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, niveau VARCHAR(30) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, formateur VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, statut VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY (id_cours)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE exercice (id_exercice INT AUTO_INCREMENT NOT NULL, id_cours INT NOT NULL, titre VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, difficulte ENUM(\'facile\', \'moyen\', \'difficile\') CHARACTER SET utf8mb4 DEFAULT \'facile\' COLLATE `utf8mb4_general_ci`, points_total INT DEFAULT 0, duree INT DEFAULT 0, est_actif TINYINT DEFAULT 1, INDEX id_cours (id_cours), PRIMARY KEY (id_exercice)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE exercices (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, type ENUM(\'MEMOIRE\', \'ATTENTION\', \'LOGIQUE\', \'CHRONO\') CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, consigne TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, difficulte INT DEFAULT 1, duree INT DEFAULT 60 COMMENT \'Durée en secondes\', contenu LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_bin` COMMENT \'Configuration spécifique à l\'\'exercice\', pour_tous_enfants TINYINT DEFAULT 1 COMMENT \'True = tous les enfants, False = enfants spécifiques\', actif TINYINT DEFAULT 1, cree_par INT DEFAULT NULL COMMENT \'ID de l\'\'enseignant qui a créé l\'\'exercice\', date_creation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, complete TINYINT DEFAULT 0, archive TINYINT DEFAULT 0, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE exercice_enfant (id INT AUTO_INCREMENT NOT NULL, exercice_id INT NOT NULL, enfant_id INT NOT NULL, date_attribution DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX enfant_id (enfant_id), UNIQUE INDEX unique_exercice_enfant (exercice_id, enfant_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE jeu (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, type ENUM(\'QUIZ\', \'EXERCICE\') CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, niveau ENUM(\'FACILE\', \'MOYEN\', \'DIFFICILE\') CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE lecon (id_lecon INT AUTO_INCREMENT NOT NULL, titre_lecon VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, contenu TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, id_cours INT DEFAULT NULL, INDEX id_cours (id_cours), PRIMARY KEY (id_lecon)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, jeu_id INT DEFAULT NULL, question_text VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, option_a VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, option_b VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, option_c VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, bonne_reponse CHAR(1) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, INDEX jeu_id (jeu_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE question_exercice (id_question_exercice INT AUTO_INCREMENT NOT NULL, id_exercice INT DEFAULT NULL, enonce TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, ordre INT DEFAULT NULL, INDEX id_exercice (id_exercice), PRIMARY KEY (id_question_exercice)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE question_quiz (id_question INT AUTO_INCREMENT NOT NULL, id_quiz INT DEFAULT NULL, enonce TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, type_question ENUM(\'qcm\', \'vrai_faux\') CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, points INT DEFAULT NULL, temps_limite INT DEFAULT NULL, ordre INT DEFAULT NULL, INDEX id_quiz (id_quiz), PRIMARY KEY (id_question)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE quiz (id_quiz INT AUTO_INCREMENT NOT NULL, id_cours INT DEFAULT NULL, titre VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, difficulte ENUM(\'facile\', \'moyen\', \'difficile\') CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, points_total INT DEFAULT NULL, duree_totale INT DEFAULT NULL, est_actif TINYINT DEFAULT NULL, INDEX id_cours (id_cours), PRIMARY KEY (id_quiz)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE reponse_exercice (id INT AUTO_INCREMENT NOT NULL, exercice_id INT NOT NULL, enfant_id INT NOT NULL, score INT DEFAULT 0 COMMENT \'Points obtenus\', temps_passe INT DEFAULT 0 COMMENT \'Temps passé en secondes\', reponses TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci` COMMENT \'Réponses de l\'\'enfant (format JSON)\', reussite TINYINT DEFAULT 0 COMMENT \'Exercice réussi ou non\', date_passage DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE reponse_user_exercice (id_rep_user INT AUTO_INCREMENT NOT NULL, id_user INT DEFAULT NULL, id_exercice INT DEFAULT NULL, id_question_exercice INT DEFAULT NULL, reponse TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, est_correcte TINYINT DEFAULT NULL, temps_reponse INT DEFAULT NULL, date_reponse DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX id_user (id_user), INDEX id_exercice (id_exercice), INDEX id_question_exercice (id_question_exercice), PRIMARY KEY (id_rep_user)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE reponse_user_quiz (id_rep_user INT AUTO_INCREMENT NOT NULL, id_user INT DEFAULT NULL, id_quiz INT DEFAULT NULL, id_question INT DEFAULT NULL, id_correction INT DEFAULT NULL, est_correcte TINYINT DEFAULT NULL, temps_reponse INT DEFAULT NULL, date_reponse DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX id_correction (id_correction), INDEX id_user (id_user), INDEX id_quiz (id_quiz), INDEX id_question (id_question), PRIMARY KEY (id_rep_user)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE score (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT DEFAULT NULL, jeu_id INT DEFAULT NULL, points INT DEFAULT NULL, date_partie DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX utilisateur_id (utilisateur_id), INDEX jeu_id (jeu_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE sessions_de_calme (id INT AUTO_INCREMENT NOT NULL, enfant_id INT DEFAULT NULL, type_activite ENUM(\'respiration\', \'musique\', \'coloriage\', \'fractales\', \'autre\') CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, declencheur ENUM(\'enfant\', \'ia_detection\', \'parent\') CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, duree_prevue INT DEFAULT NULL, duree_reelle INT DEFAULT NULL, horodatage DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, feedback_enfant TINYINT DEFAULT NULL, note_parent TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, INDEX enfant_id (enfant_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, email VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, passwordHash VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, role VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, isActive TINYINT DEFAULT 1, createdAt DATETIME DEFAULT CURRENT_TIMESTAMP, notifications_email TINYINT DEFAULT 1, UNIQUE INDEX username (username), UNIQUE INDEX email (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE carnet_educatif ADD CONSTRAINT `carnet_educatif_ibfk_1` FOREIGN KEY (user_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT `commentaire_ibfk_1` FOREIGN KEY (carnet_id) REFERENCES carnet_educatif (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE correction_quiz ADD CONSTRAINT `correction_quiz_ibfk_1` FOREIGN KEY (id_question) REFERENCES question_quiz (id_question) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exercice ADD CONSTRAINT `exercice_ibfk_1` FOREIGN KEY (id_cours) REFERENCES cours (id_cours) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lecon ADD CONSTRAINT `lecon_ibfk_1` FOREIGN KEY (id_cours) REFERENCES cours (id_cours) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT `question_ibfk_1` FOREIGN KEY (jeu_id) REFERENCES jeu (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE question_exercice ADD CONSTRAINT `question_exercice_ibfk_1` FOREIGN KEY (id_exercice) REFERENCES exercice (id_exercice) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE question_quiz ADD CONSTRAINT `question_quiz_ibfk_1` FOREIGN KEY (id_quiz) REFERENCES quiz (id_quiz) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT `quiz_ibfk_1` FOREIGN KEY (id_cours) REFERENCES cours (id_cours) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reponse_user_exercice ADD CONSTRAINT `reponse_user_exercice_ibfk_1` FOREIGN KEY (id_user) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reponse_user_exercice ADD CONSTRAINT `reponse_user_exercice_ibfk_2` FOREIGN KEY (id_exercice) REFERENCES exercice (id_exercice) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reponse_user_exercice ADD CONSTRAINT `reponse_user_exercice_ibfk_3` FOREIGN KEY (id_question_exercice) REFERENCES question_exercice (id_question_exercice) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reponse_user_quiz ADD CONSTRAINT `reponse_user_quiz_ibfk_1` FOREIGN KEY (id_user) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reponse_user_quiz ADD CONSTRAINT `reponse_user_quiz_ibfk_2` FOREIGN KEY (id_quiz) REFERENCES quiz (id_quiz) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reponse_user_quiz ADD CONSTRAINT `reponse_user_quiz_ibfk_3` FOREIGN KEY (id_question) REFERENCES question_quiz (id_question) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reponse_user_quiz ADD CONSTRAINT `reponse_user_quiz_ibfk_4` FOREIGN KEY (id_correction) REFERENCES correction_quiz (id_correction) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE score ADD CONSTRAINT `score_ibfk_1` FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE score ADD CONSTRAINT `score_ibfk_2` FOREIGN KEY (jeu_id) REFERENCES jeu (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sessions_de_calme ADD CONSTRAINT `sessions_de_calme_ibfk_1` FOREIGN KEY (enfant_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE detection_emotion DROP FOREIGN KEY FK_1451AC68908D73D1');
        $this->addSql('DROP TABLE detection_emotion');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE alertes_email CHANGE type_alerte type_alerte ENUM(\'warning\', \'encouragement\') NOT NULL, CHANGE date_envoi date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE session_ids session_ids VARCHAR(500) DEFAULT NULL');
        $this->addSql('CREATE INDEX enfant_id ON alertes_email (enfant_id)');
        $this->addSql('ALTER TABLE emotion CHANGE nom nom VARCHAR(100) DEFAULT NULL, CHANGE photo photo LONGBLOB DEFAULT NULL');
        $this->addSql('ALTER TABLE humeur_journaliere DROP FOREIGN KEY FK_1674A11E881453BA');
        $this->addSql('ALTER TABLE humeur_journaliere DROP FOREIGN KEY FK_1674A11E881453BA');
        $this->addSql('ALTER TABLE humeur_journaliere CHANGE dateHeure dateHeure DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE humeur_journaliere ADD CONSTRAINT `humeur_journaliere_ibfk_1` FOREIGN KEY (emotionId) REFERENCES emotion (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX idx_1674a11e881453ba ON humeur_journaliere');
        $this->addSql('CREATE INDEX emotionId ON humeur_journaliere (emotionId)');
        $this->addSql('ALTER TABLE humeur_journaliere ADD CONSTRAINT FK_1674A11E881453BA FOREIGN KEY (emotionId) REFERENCES emotion (id)');
        $this->addSql('ALTER TABLE scenario DROP FOREIGN KEY FK_3E45C8D8881453BA');
        $this->addSql('ALTER TABLE scenario DROP FOREIGN KEY FK_3E45C8D8881453BA');
        $this->addSql('ALTER TABLE scenario CHANGE description description TEXT DEFAULT NULL, CHANGE animation animation LONGBLOB NOT NULL');
        $this->addSql('ALTER TABLE scenario ADD CONSTRAINT `scenario_ibfk_1` FOREIGN KEY (emotionId) REFERENCES emotion (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX idx_3e45c8d8881453ba ON scenario');
        $this->addSql('CREATE INDEX emotionId ON scenario (emotionId)');
        $this->addSql('ALTER TABLE scenario ADD CONSTRAINT FK_3E45C8D8881453BA FOREIGN KEY (emotionId) REFERENCES emotion (id)');
    }
}
