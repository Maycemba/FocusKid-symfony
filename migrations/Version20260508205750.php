<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260508205750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
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
        $this->addSql('ALTER TABLE utilisateur ADD reset_token VARCHAR(255) DEFAULT NULL, ADD reset_token_expiry DATETIME DEFAULT NULL');
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
        $this->addSql('ALTER TABLE utilisateur DROP reset_token, DROP reset_token_expiry');
    }
}
