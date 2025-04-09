<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250409140434 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE exchange_rate ADD currency VARCHAR(3) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE exchange_rate ADD rate DOUBLE PRECISION NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE exchange_rate ADD date DATE NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE exchange_rate DROP currency
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE exchange_rate DROP rate
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE exchange_rate DROP date
        SQL);
    }
}
