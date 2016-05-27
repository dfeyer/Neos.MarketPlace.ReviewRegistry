<?php
namespace TYPO3\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Introduce event log
 */
class Version20160527175019 extends AbstractMigration
{

    /**
     * @return string
     */
    public function getDescription() {
        return 'Introduce event log';
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');
        
        $this->addSql('CREATE TABLE neos_marketplace_reviewregistry_domain_model_aggregate (uid INT UNSIGNED AUTO_INCREMENT NOT NULL, timestamp DATETIME NOT NULL, type VARCHAR(255) NOT NULL, version INT NOT NULL, PRIMARY KEY(uid)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE neos_marketplace_reviewregistry_domain_model_event (uid INT UNSIGNED AUTO_INCREMENT NOT NULL, aggregate INT UNSIGNED DEFAULT NULL, timestamp DATETIME NOT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:flow_json_array)\', version INT NOT NULL, INDEX IDX_A936871DB77949FF (aggregate), PRIMARY KEY(uid)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE neos_marketplace_reviewregistry_domain_model_event ADD CONSTRAINT FK_A936871DB77949FF FOREIGN KEY (aggregate) REFERENCES neos_marketplace_reviewregistry_domain_model_aggregate (uid)');
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');
        
        $this->addSql('ALTER TABLE neos_marketplace_reviewregistry_domain_model_event DROP FOREIGN KEY FK_A936871DB77949FF');
        $this->addSql('DROP TABLE neos_marketplace_reviewregistry_domain_model_aggregate');
        $this->addSql('DROP TABLE neos_marketplace_reviewregistry_domain_model_event');
    }
}
