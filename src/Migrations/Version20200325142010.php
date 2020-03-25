<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200325142010 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(127) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(127) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team_user (team_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_5C722232296CD8AE (team_id), INDEX IDX_5C722232A76ED395 (user_id), PRIMARY KEY(team_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, host_id INT NOT NULL, winner_id INT DEFAULT NULL, team1_id INT NOT NULL, team2_id INT NOT NULL, number_of_rounds INT NOT NULL, status INT NOT NULL, INDEX IDX_232B318C1FB8D185 (host_id), UNIQUE INDEX UNIQ_232B318C5DFCD4B8 (winner_id), INDEX IDX_232B318CE72BCFA4 (team1_id), INDEX IDX_232B318CF59E604A (team2_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE round (id INT AUTO_INCREMENT NOT NULL, game_id INT NOT NULL, spy_master_1_id INT NOT NULL, spy_master_2_id INT NOT NULL, starting_team_id INT DEFAULT NULL, winner_id INT DEFAULT NULL, map JSON DEFAULT NULL, words JSON DEFAULT NULL, progress JSON DEFAULT NULL, status INT NOT NULL, INDEX IDX_C5EEEA34E48FD905 (game_id), INDEX IDX_C5EEEA34E9F27319 (spy_master_1_id), INDEX IDX_C5EEEA34FB47DCF7 (spy_master_2_id), INDEX IDX_C5EEEA349EB078C3 (starting_team_id), UNIQUE INDEX UNIQ_C5EEEA345DFCD4B8 (winner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE word (id INT AUTO_INCREMENT NOT NULL, text VARCHAR(127) NOT NULL, lang VARCHAR(7) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE turn (id INT AUTO_INCREMENT NOT NULL, spy_master_id INT NOT NULL, round_id INT NOT NULL, word VARCHAR(255) NOT NULL, number INT NOT NULL, pointed JSON NOT NULL, status INT NOT NULL, INDEX IDX_2020154714CAA930 (spy_master_id), INDEX IDX_20201547A6005CA0 (round_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE team_user ADD CONSTRAINT FK_5C722232296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE team_user ADD CONSTRAINT FK_5C722232A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318C1FB8D185 FOREIGN KEY (host_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318C5DFCD4B8 FOREIGN KEY (winner_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318CE72BCFA4 FOREIGN KEY (team1_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318CF59E604A FOREIGN KEY (team2_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE round ADD CONSTRAINT FK_C5EEEA34E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE round ADD CONSTRAINT FK_C5EEEA34E9F27319 FOREIGN KEY (spy_master_1_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE round ADD CONSTRAINT FK_C5EEEA34FB47DCF7 FOREIGN KEY (spy_master_2_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE round ADD CONSTRAINT FK_C5EEEA349EB078C3 FOREIGN KEY (starting_team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE round ADD CONSTRAINT FK_C5EEEA345DFCD4B8 FOREIGN KEY (winner_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE turn ADD CONSTRAINT FK_2020154714CAA930 FOREIGN KEY (spy_master_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE turn ADD CONSTRAINT FK_20201547A6005CA0 FOREIGN KEY (round_id) REFERENCES round (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE team_user DROP FOREIGN KEY FK_5C722232A76ED395');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318C1FB8D185');
        $this->addSql('ALTER TABLE round DROP FOREIGN KEY FK_C5EEEA34E9F27319');
        $this->addSql('ALTER TABLE round DROP FOREIGN KEY FK_C5EEEA34FB47DCF7');
        $this->addSql('ALTER TABLE turn DROP FOREIGN KEY FK_2020154714CAA930');
        $this->addSql('ALTER TABLE team_user DROP FOREIGN KEY FK_5C722232296CD8AE');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318C5DFCD4B8');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318CE72BCFA4');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318CF59E604A');
        $this->addSql('ALTER TABLE round DROP FOREIGN KEY FK_C5EEEA349EB078C3');
        $this->addSql('ALTER TABLE round DROP FOREIGN KEY FK_C5EEEA345DFCD4B8');
        $this->addSql('ALTER TABLE round DROP FOREIGN KEY FK_C5EEEA34E48FD905');
        $this->addSql('ALTER TABLE turn DROP FOREIGN KEY FK_20201547A6005CA0');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE team_user');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE round');
        $this->addSql('DROP TABLE word');
        $this->addSql('DROP TABLE turn');
    }
}
