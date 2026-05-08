<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260508202037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE alertes_email (id INT AUTO_INCREMENT NOT NULL, enfant_id INT NOT NULL, type_alerte VARCHAR(255) NOT NULL, date_envoi DATETIME DEFAULT NULL, email_envoye VARCHAR(255) DEFAULT NULL, session_ids VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE carnet_educatif (id INT AUTO_INCREMENT NOT NULL, date_etude DATE NOT NULL, heure_debut TIME NOT NULL, heure_fin TIME NOT NULL, duree_totale INT DEFAULT NULL, lieu VARCHAR(100) NOT NULL, matiere VARCHAR(50) NOT NULL, type_activite LONGTEXT NOT NULL, niveau_difficulte VARCHAR(20) NOT NULL, niveau_concentration INT NOT NULL, niveau_agitation INT NOT NULL, nombre_interruptions INT NOT NULL, temps_avant_perte_concentration INT DEFAULT NULL, travaille_seul TINYINT NOT NULL, demande_aide TINYINT NOT NULL, niveau_autonomie INT NOT NULL, travail_termine TINYINT NOT NULL, difficultes LONGTEXT DEFAULT NULL, points_positifs LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_5F40B66BA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE commentaire (id INT AUTO_INCREMENT NOT NULL, date_commentaire DATETIME NOT NULL, texte_commentaire LONGTEXT NOT NULL, type_commentaire VARCHAR(50) NOT NULL, carnet_id INT DEFAULT NULL, INDEX IDX_67F068BCFA207516 (carnet_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE correction_quiz (id_correction INT AUTO_INCREMENT NOT NULL, contenu VARCHAR(255) DEFAULT NULL, est_correcte TINYINT DEFAULT NULL, id_question INT DEFAULT NULL, INDEX IDX_45AD42F4E62CA5DB (id_question), PRIMARY KEY (id_correction)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE cours (id_cours INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, niveau VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, formateur VARCHAR(255) DEFAULT NULL, statut VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id_cours)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE emotion (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) DEFAULT NULL, photo LONGTEXT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE exercice_enfant (id INT AUTO_INCREMENT NOT NULL, exercice_id INT NOT NULL, enfant_id INT NOT NULL, date_attribution DATETIME NOT NULL, complete TINYINT DEFAULT 0, score_final INT DEFAULT 0, date_completion DATETIME DEFAULT NULL, jours VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE exercices (id_exercice INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, consigne LONGTEXT DEFAULT NULL, difficulte INT DEFAULT NULL, duree INT DEFAULT NULL, contenu LONGTEXT DEFAULT NULL, pour_tous_enfants TINYINT DEFAULT NULL, actif TINYINT DEFAULT NULL, cree_par INT DEFAULT NULL, date_creation DATETIME NOT NULL, complete TINYINT DEFAULT NULL, archive TINYINT DEFAULT NULL, INDEX IDX_1387EAE14C28E2B5 (cree_par), PRIMARY KEY (id_exercice)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE humeur_journaliere (id INT AUTO_INCREMENT NOT NULL, dateHeure DATETIME DEFAULT NULL, emotionId INT DEFAULT NULL, INDEX IDX_1674A11E881453BA (emotionId), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE jeu (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, niveau VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE lecon (id_lecon INT AUTO_INCREMENT NOT NULL, titre_lecon VARCHAR(255) NOT NULL, contenu LONGTEXT DEFAULT NULL, id_cours INT DEFAULT NULL, INDEX IDX_94E6242E134FCDAC (id_cours), PRIMARY KEY (id_lecon)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE login_anomaly (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(100) NOT NULL, ip_address VARCHAR(45) NOT NULL, reason VARCHAR(255) NOT NULL, detected_at DATETIME NOT NULL, alert_sent TINYINT DEFAULT 0 NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE objectifs (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(255) NOT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, statut VARCHAR(20) DEFAULT \'EN_COURS\' NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, question_text VARCHAR(255) DEFAULT NULL, option_a VARCHAR(255) DEFAULT NULL, option_b VARCHAR(255) DEFAULT NULL, option_c VARCHAR(255) DEFAULT NULL, bonne_reponse VARCHAR(255) DEFAULT NULL, jeu_id INT DEFAULT NULL, INDEX IDX_B6F7494E8C9E392E (jeu_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE question_exercice (id_question_exercice INT AUTO_INCREMENT NOT NULL, enonce LONGTEXT DEFAULT NULL, ordre INT DEFAULT NULL, id_exercice INT DEFAULT NULL, INDEX IDX_87107741B4C32BD8 (id_exercice), PRIMARY KEY (id_question_exercice)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE question_quiz (id_question INT AUTO_INCREMENT NOT NULL, enonce LONGTEXT DEFAULT NULL, type_question VARCHAR(255) DEFAULT NULL, points INT DEFAULT NULL, temps_limite INT DEFAULT NULL, ordre INT DEFAULT NULL, id_quiz INT DEFAULT NULL, INDEX IDX_FAFC177D2F32E690 (id_quiz), PRIMARY KEY (id_question)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE quiz (id_quiz INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, difficulte VARCHAR(255) DEFAULT NULL, points_total INT DEFAULT NULL, duree_totale INT DEFAULT NULL, est_actif TINYINT DEFAULT NULL, id_cours INT DEFAULT NULL, INDEX IDX_A412FA92134FCDAC (id_cours), PRIMARY KEY (id_quiz)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reponse_exercice (id INT AUTO_INCREMENT NOT NULL, exercice_id INT NOT NULL, enfant_id INT NOT NULL, score INT DEFAULT NULL, temps_passe INT DEFAULT NULL, reponses LONGTEXT DEFAULT NULL, reussite TINYINT DEFAULT NULL, date_passage DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reponse_user_exercice (id_rep_user INT AUTO_INCREMENT NOT NULL, reponse LONGTEXT DEFAULT NULL, est_correcte TINYINT DEFAULT NULL, temps_reponse INT DEFAULT NULL, date_reponse DATETIME DEFAULT NULL, id_user INT DEFAULT NULL, id_exercice INT DEFAULT NULL, id_question_exercice INT DEFAULT NULL, INDEX IDX_2F511A806B3CA4B (id_user), INDEX IDX_2F511A80B4C32BD8 (id_exercice), INDEX IDX_2F511A8076271F56 (id_question_exercice), PRIMARY KEY (id_rep_user)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reponse_user_quiz (id_rep_user INT AUTO_INCREMENT NOT NULL, est_correcte TINYINT DEFAULT NULL, temps_reponse INT DEFAULT NULL, date_reponse DATETIME DEFAULT NULL, id_user INT DEFAULT NULL, id_quiz INT DEFAULT NULL, id_question INT DEFAULT NULL, id_correction INT DEFAULT NULL, INDEX IDX_10ADDE4B6B3CA4B (id_user), INDEX IDX_10ADDE4B2F32E690 (id_quiz), INDEX IDX_10ADDE4BE62CA5DB (id_question), INDEX IDX_10ADDE4B717216F (id_correction), PRIMARY KEY (id_rep_user)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE scenario (id INT AUTO_INCREMENT NOT NULL, description LONGTEXT DEFAULT NULL, animation LONGTEXT DEFAULT NULL, emotionId INT DEFAULT NULL, INDEX IDX_3E45C8D8881453BA (emotionId), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE score (id INT AUTO_INCREMENT NOT NULL, points INT DEFAULT NULL, date_partie DATETIME NOT NULL, utilisateur_id INT DEFAULT NULL, jeu_id INT DEFAULT NULL, INDEX IDX_32993751FB88E14F (utilisateur_id), INDEX IDX_329937518C9E392E (jeu_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sessions_de_calme (id INT AUTO_INCREMENT NOT NULL, type_activite VARCHAR(255) DEFAULT NULL, declencheur VARCHAR(255) DEFAULT NULL, duree_prevue INT DEFAULT NULL, duree_reelle INT DEFAULT NULL, horodatage DATETIME NOT NULL, feedback_enfant INT DEFAULT NULL, note_parent LONGTEXT DEFAULT NULL, enfant_id INT DEFAULT NULL, INDEX IDX_DCD864FE450D2529 (enfant_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE task (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, status VARCHAR(20) NOT NULL, is_checked TINYINT NOT NULL, due_date DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, file_name VARCHAR(255) DEFAULT NULL, file_size INT DEFAULT NULL, position INT NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, Username VARCHAR(255) NOT NULL, Email VARCHAR(255) NOT NULL, PasswordHash VARCHAR(255) NOT NULL, Role INT NOT NULL, IsActive TINYINT DEFAULT NULL, createdAt DATETIME DEFAULT NULL, notifications_email TINYINT DEFAULT NULL, firstname VARCHAR(100) DEFAULT NULL, lastname VARCHAR(100) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, birthdate DATE DEFAULT NULL, avatar VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE carnet_educatif ADD CONSTRAINT FK_5F40B66BA76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCFA207516 FOREIGN KEY (carnet_id) REFERENCES carnet_educatif (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE correction_quiz ADD CONSTRAINT FK_45AD42F4E62CA5DB FOREIGN KEY (id_question) REFERENCES question_quiz (id_question)');
        $this->addSql('ALTER TABLE exercices ADD CONSTRAINT FK_1387EAE14C28E2B5 FOREIGN KEY (cree_par) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE humeur_journaliere ADD CONSTRAINT FK_1674A11E881453BA FOREIGN KEY (emotionId) REFERENCES emotion (id)');
        $this->addSql('ALTER TABLE lecon ADD CONSTRAINT FK_94E6242E134FCDAC FOREIGN KEY (id_cours) REFERENCES cours (id_cours)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E8C9E392E FOREIGN KEY (jeu_id) REFERENCES jeu (id)');
        $this->addSql('ALTER TABLE question_exercice ADD CONSTRAINT FK_87107741B4C32BD8 FOREIGN KEY (id_exercice) REFERENCES exercices (id)');
        $this->addSql('ALTER TABLE question_quiz ADD CONSTRAINT FK_FAFC177D2F32E690 FOREIGN KEY (id_quiz) REFERENCES quiz (id_quiz)');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92134FCDAC FOREIGN KEY (id_cours) REFERENCES cours (id_cours)');
        $this->addSql('ALTER TABLE reponse_user_exercice ADD CONSTRAINT FK_2F511A806B3CA4B FOREIGN KEY (id_user) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE reponse_user_exercice ADD CONSTRAINT FK_2F511A80B4C32BD8 FOREIGN KEY (id_exercice) REFERENCES exercices (id)');
        $this->addSql('ALTER TABLE reponse_user_exercice ADD CONSTRAINT FK_2F511A8076271F56 FOREIGN KEY (id_question_exercice) REFERENCES question_exercice (id_question_exercice)');
        $this->addSql('ALTER TABLE reponse_user_quiz ADD CONSTRAINT FK_10ADDE4B6B3CA4B FOREIGN KEY (id_user) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE reponse_user_quiz ADD CONSTRAINT FK_10ADDE4B2F32E690 FOREIGN KEY (id_quiz) REFERENCES quiz (id_quiz)');
        $this->addSql('ALTER TABLE reponse_user_quiz ADD CONSTRAINT FK_10ADDE4BE62CA5DB FOREIGN KEY (id_question) REFERENCES question_quiz (id_question)');
        $this->addSql('ALTER TABLE reponse_user_quiz ADD CONSTRAINT FK_10ADDE4B717216F FOREIGN KEY (id_correction) REFERENCES correction_quiz (id_correction)');
        $this->addSql('ALTER TABLE scenario ADD CONSTRAINT FK_3E45C8D8881453BA FOREIGN KEY (emotionId) REFERENCES emotion (id)');
        $this->addSql('ALTER TABLE score ADD CONSTRAINT FK_32993751FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE score ADD CONSTRAINT FK_329937518C9E392E FOREIGN KEY (jeu_id) REFERENCES jeu (id)');
        $this->addSql('ALTER TABLE sessions_de_calme ADD CONSTRAINT FK_DCD864FE450D2529 FOREIGN KEY (enfant_id) REFERENCES utilisateur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE carnet_educatif DROP FOREIGN KEY FK_5F40B66BA76ED395');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCFA207516');
        $this->addSql('ALTER TABLE correction_quiz DROP FOREIGN KEY FK_45AD42F4E62CA5DB');
        $this->addSql('ALTER TABLE exercices DROP FOREIGN KEY FK_1387EAE14C28E2B5');
        $this->addSql('ALTER TABLE humeur_journaliere DROP FOREIGN KEY FK_1674A11E881453BA');
        $this->addSql('ALTER TABLE lecon DROP FOREIGN KEY FK_94E6242E134FCDAC');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E8C9E392E');
        $this->addSql('ALTER TABLE question_exercice DROP FOREIGN KEY FK_87107741B4C32BD8');
        $this->addSql('ALTER TABLE question_quiz DROP FOREIGN KEY FK_FAFC177D2F32E690');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92134FCDAC');
        $this->addSql('ALTER TABLE reponse_user_exercice DROP FOREIGN KEY FK_2F511A806B3CA4B');
        $this->addSql('ALTER TABLE reponse_user_exercice DROP FOREIGN KEY FK_2F511A80B4C32BD8');
        $this->addSql('ALTER TABLE reponse_user_exercice DROP FOREIGN KEY FK_2F511A8076271F56');
        $this->addSql('ALTER TABLE reponse_user_quiz DROP FOREIGN KEY FK_10ADDE4B6B3CA4B');
        $this->addSql('ALTER TABLE reponse_user_quiz DROP FOREIGN KEY FK_10ADDE4B2F32E690');
        $this->addSql('ALTER TABLE reponse_user_quiz DROP FOREIGN KEY FK_10ADDE4BE62CA5DB');
        $this->addSql('ALTER TABLE reponse_user_quiz DROP FOREIGN KEY FK_10ADDE4B717216F');
        $this->addSql('ALTER TABLE scenario DROP FOREIGN KEY FK_3E45C8D8881453BA');
        $this->addSql('ALTER TABLE score DROP FOREIGN KEY FK_32993751FB88E14F');
        $this->addSql('ALTER TABLE score DROP FOREIGN KEY FK_329937518C9E392E');
        $this->addSql('ALTER TABLE sessions_de_calme DROP FOREIGN KEY FK_DCD864FE450D2529');
        $this->addSql('DROP TABLE alertes_email');
        $this->addSql('DROP TABLE carnet_educatif');
        $this->addSql('DROP TABLE commentaire');
        $this->addSql('DROP TABLE correction_quiz');
        $this->addSql('DROP TABLE cours');
        $this->addSql('DROP TABLE emotion');
        $this->addSql('DROP TABLE exercice_enfant');
        $this->addSql('DROP TABLE exercices');
        $this->addSql('DROP TABLE humeur_journaliere');
        $this->addSql('DROP TABLE jeu');
        $this->addSql('DROP TABLE lecon');
        $this->addSql('DROP TABLE login_anomaly');
        $this->addSql('DROP TABLE objectifs');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE question_exercice');
        $this->addSql('DROP TABLE question_quiz');
        $this->addSql('DROP TABLE quiz');
        $this->addSql('DROP TABLE reponse_exercice');
        $this->addSql('DROP TABLE reponse_user_exercice');
        $this->addSql('DROP TABLE reponse_user_quiz');
        $this->addSql('DROP TABLE scenario');
        $this->addSql('DROP TABLE score');
        $this->addSql('DROP TABLE sessions_de_calme');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
