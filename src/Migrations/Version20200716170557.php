<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200716170557 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE classification__category (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, media_id INT DEFAULT NULL, context VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, enabled TINYINT(1) NOT NULL, position INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, path VARCHAR(255) DEFAULT NULL, level INT DEFAULT NULL, tree_lock_time DATE DEFAULT NULL, tree_path_hash VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_43629B36B548B0F (path), INDEX IDX_43629B36727ACA70 (parent_id), INDEX IDX_43629B36EA9FDD75 (media_id), INDEX IDX_43629B36E25D857E (context), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE classification__collection (id INT AUTO_INCREMENT NOT NULL, context VARCHAR(255) DEFAULT NULL, media_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, slug VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_A406B56AE25D857E (context), INDEX IDX_A406B56AEA9FDD75 (media_id), UNIQUE INDEX tag_collection (slug, context), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE classification__context (id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE classification__tag (id INT AUTO_INCREMENT NOT NULL, context VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, slug VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_CA57A1C7E25D857E (context), UNIQUE INDEX tag_context (slug, context), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE jms_cron_jobs (id INT UNSIGNED AUTO_INCREMENT NOT NULL, lastRunAt DATETIME NOT NULL, command VARCHAR(200) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE jms_jobs (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, state VARCHAR(15) NOT NULL, queue VARCHAR(50) NOT NULL, priority SMALLINT NOT NULL, createdAt DATETIME NOT NULL, startedAt DATETIME DEFAULT NULL, checkedAt DATETIME DEFAULT NULL, workerName VARCHAR(50) DEFAULT NULL, executeAfter DATETIME DEFAULT NULL, closedAt DATETIME DEFAULT NULL, command VARCHAR(255) NOT NULL, args JSON NOT NULL COMMENT \'(DC2Type:json_array)\', output LONGTEXT DEFAULT NULL, errorOutput LONGTEXT DEFAULT NULL, exitCode SMALLINT UNSIGNED DEFAULT NULL, maxRuntime SMALLINT UNSIGNED NOT NULL, maxRetries SMALLINT UNSIGNED NOT NULL, stackTrace LONGBLOB DEFAULT NULL COMMENT \'(DC2Type:jms_job_safe_object)\', runtime SMALLINT UNSIGNED DEFAULT NULL, memoryUsage INT UNSIGNED DEFAULT NULL, memoryUsageReal INT UNSIGNED DEFAULT NULL, originalJob_id BIGINT UNSIGNED DEFAULT NULL, INDEX IDX_704ADB9349C447F1 (originalJob_id), INDEX cmd_search_index (queue), INDEX sorting_index (state, priority, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE jms_job_dependencies (source_job_id BIGINT UNSIGNED NOT NULL, dest_job_id BIGINT UNSIGNED NOT NULL, INDEX IDX_8DCFE92CBD1F6B4F (source_job_id), INDEX IDX_8DCFE92C32CF8D4C (dest_job_id), PRIMARY KEY(source_job_id, dest_job_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media__gallery (id INT AUTO_INCREMENT NOT NULL, primary_media_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, context VARCHAR(64) NOT NULL, default_format VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, updated_at DATETIME NOT NULL, created_at DATETIME NOT NULL, description LONGTEXT DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_3C02B7F5CBE0B084 (primary_media_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media__gallery_tag (tag_id INT NOT NULL, gallery_id INT NOT NULL, INDEX IDX_2212BE7CBAD26311 (tag_id), INDEX IDX_2212BE7C4E7AF8F (gallery_id), PRIMARY KEY(tag_id, gallery_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media__gallery_media (id INT AUTO_INCREMENT NOT NULL, gallery_id INT DEFAULT NULL, media_id INT DEFAULT NULL, position INT NOT NULL, enabled TINYINT(1) NOT NULL, updated_at DATETIME NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_80D4C5414E7AF8F (gallery_id), INDEX IDX_80D4C541EA9FDD75 (media_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media__media (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, enabled TINYINT(1) NOT NULL, provider_name VARCHAR(255) NOT NULL, provider_status INT NOT NULL, provider_reference VARCHAR(255) NOT NULL, provider_metadata LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', width INT DEFAULT NULL, height INT DEFAULT NULL, length NUMERIC(10, 0) DEFAULT NULL, content_type VARCHAR(255) DEFAULT NULL, content_size INT DEFAULT NULL, copyright VARCHAR(255) DEFAULT NULL, author_name VARCHAR(255) DEFAULT NULL, context VARCHAR(64) DEFAULT NULL, cdn_is_flushable TINYINT(1) DEFAULT NULL, cdn_flush_identifier VARCHAR(64) DEFAULT NULL, cdn_flush_at DATETIME DEFAULT NULL, cdn_status INT DEFAULT NULL, updated_at DATETIME NOT NULL, created_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, is_tmp TINYINT(1) DEFAULT NULL, INDEX IDX_5C6DD74E12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media__media_tag (base_media_id INT NOT NULL, tag_interface_id INT NOT NULL, INDEX IDX_F61AAF7A0747E8D (base_media_id), INDEX IDX_F61AAF7B1DC280 (tag_interface_id), PRIMARY KEY(base_media_id, tag_interface_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user__group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_82AAB3645E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user__user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email TEXT DEFAULT NULL COMMENT \'(DC2Type:encrypted_data_string)\', emailCanonical TEXT DEFAULT NULL COMMENT \'(DC2Type:encrypted_data_string)\', enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, date_of_birth TEXT DEFAULT NULL COMMENT \'(DC2Type:encrypted_data_datetime)\', firstname TEXT DEFAULT NULL COMMENT \'(DC2Type:encrypted_data_string)\', lastname TEXT DEFAULT NULL COMMENT \'(DC2Type:encrypted_data_string)\', website VARCHAR(64) DEFAULT NULL, biography VARCHAR(1000) DEFAULT NULL, gender VARCHAR(1) DEFAULT NULL, locale VARCHAR(8) DEFAULT NULL, timezone VARCHAR(64) DEFAULT NULL, phone TEXT DEFAULT NULL COMMENT \'(DC2Type:encrypted_data_string)\', facebook_uid VARCHAR(255) DEFAULT NULL, facebook_name VARCHAR(255) DEFAULT NULL, facebook_data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', twitter_uid VARCHAR(255) DEFAULT NULL, twitter_name VARCHAR(255) DEFAULT NULL, twitter_data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', gplus_uid VARCHAR(255) DEFAULT NULL, gplus_name VARCHAR(255) DEFAULT NULL, gplus_data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', token VARCHAR(255) DEFAULT NULL, two_step_code VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_32745D0A92FC23A8 (username_canonical), UNIQUE INDEX UNIQ_32745D0AC05FB297 (confirmation_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE jms_job_related_entities (job_id BIGINT UNSIGNED NOT NULL, related_class VARCHAR(150) NOT NULL, related_id VARCHAR(100) NOT NULL, INDEX IDX_E956F4E2BE04EA9 (job_id), PRIMARY KEY(job_id, related_class, related_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE jms_job_statistics (job_id BIGINT UNSIGNED NOT NULL, characteristic VARCHAR(30) NOT NULL, createdAt DATETIME NOT NULL, charValue DOUBLE PRECISION NOT NULL, PRIMARY KEY(job_id, characteristic, createdAt)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE classification__category ADD CONSTRAINT FK_43629B36727ACA70 FOREIGN KEY (parent_id) REFERENCES classification__category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE classification__category ADD CONSTRAINT FK_43629B36EA9FDD75 FOREIGN KEY (media_id) REFERENCES media__media (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE classification__category ADD CONSTRAINT FK_43629B36E25D857E FOREIGN KEY (context) REFERENCES classification__context (id)');
        $this->addSql('ALTER TABLE classification__collection ADD CONSTRAINT FK_A406B56AE25D857E FOREIGN KEY (context) REFERENCES classification__context (id)');
        $this->addSql('ALTER TABLE classification__collection ADD CONSTRAINT FK_A406B56AEA9FDD75 FOREIGN KEY (media_id) REFERENCES media__media (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE classification__tag ADD CONSTRAINT FK_CA57A1C7E25D857E FOREIGN KEY (context) REFERENCES classification__context (id)');
        $this->addSql('ALTER TABLE jms_jobs ADD CONSTRAINT FK_704ADB9349C447F1 FOREIGN KEY (originalJob_id) REFERENCES jms_jobs (id)');
        $this->addSql('ALTER TABLE jms_job_dependencies ADD CONSTRAINT FK_8DCFE92CBD1F6B4F FOREIGN KEY (source_job_id) REFERENCES jms_jobs (id)');
        $this->addSql('ALTER TABLE jms_job_dependencies ADD CONSTRAINT FK_8DCFE92C32CF8D4C FOREIGN KEY (dest_job_id) REFERENCES jms_jobs (id)');
        $this->addSql('ALTER TABLE media__gallery ADD CONSTRAINT FK_3C02B7F5CBE0B084 FOREIGN KEY (primary_media_id) REFERENCES media__media (id)');
        $this->addSql('ALTER TABLE media__gallery_tag ADD CONSTRAINT FK_2212BE7CBAD26311 FOREIGN KEY (tag_id) REFERENCES media__gallery (id)');
        $this->addSql('ALTER TABLE media__gallery_tag ADD CONSTRAINT FK_2212BE7C4E7AF8F FOREIGN KEY (gallery_id) REFERENCES classification__tag (id)');
        $this->addSql('ALTER TABLE media__gallery_media ADD CONSTRAINT FK_80D4C5414E7AF8F FOREIGN KEY (gallery_id) REFERENCES media__gallery (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE media__gallery_media ADD CONSTRAINT FK_80D4C541EA9FDD75 FOREIGN KEY (media_id) REFERENCES media__media (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE media__media ADD CONSTRAINT FK_5C6DD74E12469DE2 FOREIGN KEY (category_id) REFERENCES classification__category (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE media__media_tag ADD CONSTRAINT FK_F61AAF7A0747E8D FOREIGN KEY (base_media_id) REFERENCES media__media (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE media__media_tag ADD CONSTRAINT FK_F61AAF7B1DC280 FOREIGN KEY (tag_interface_id) REFERENCES classification__tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE jms_job_related_entities ADD CONSTRAINT FK_E956F4E2BE04EA9 FOREIGN KEY (job_id) REFERENCES jms_jobs (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE classification__category DROP FOREIGN KEY FK_43629B36727ACA70');
        $this->addSql('ALTER TABLE media__media DROP FOREIGN KEY FK_5C6DD74E12469DE2');
        $this->addSql('ALTER TABLE classification__category DROP FOREIGN KEY FK_43629B36E25D857E');
        $this->addSql('ALTER TABLE classification__collection DROP FOREIGN KEY FK_A406B56AE25D857E');
        $this->addSql('ALTER TABLE classification__tag DROP FOREIGN KEY FK_CA57A1C7E25D857E');
        $this->addSql('ALTER TABLE media__gallery_tag DROP FOREIGN KEY FK_2212BE7C4E7AF8F');
        $this->addSql('ALTER TABLE media__media_tag DROP FOREIGN KEY FK_F61AAF7B1DC280');
        $this->addSql('ALTER TABLE jms_jobs DROP FOREIGN KEY FK_704ADB9349C447F1');
        $this->addSql('ALTER TABLE jms_job_dependencies DROP FOREIGN KEY FK_8DCFE92CBD1F6B4F');
        $this->addSql('ALTER TABLE jms_job_dependencies DROP FOREIGN KEY FK_8DCFE92C32CF8D4C');
        $this->addSql('ALTER TABLE jms_job_related_entities DROP FOREIGN KEY FK_E956F4E2BE04EA9');
        $this->addSql('ALTER TABLE media__gallery_tag DROP FOREIGN KEY FK_2212BE7CBAD26311');
        $this->addSql('ALTER TABLE media__gallery_media DROP FOREIGN KEY FK_80D4C5414E7AF8F');
        $this->addSql('ALTER TABLE classification__category DROP FOREIGN KEY FK_43629B36EA9FDD75');
        $this->addSql('ALTER TABLE classification__collection DROP FOREIGN KEY FK_A406B56AEA9FDD75');
        $this->addSql('ALTER TABLE media__gallery DROP FOREIGN KEY FK_3C02B7F5CBE0B084');
        $this->addSql('ALTER TABLE media__gallery_media DROP FOREIGN KEY FK_80D4C541EA9FDD75');
        $this->addSql('ALTER TABLE media__media_tag DROP FOREIGN KEY FK_F61AAF7A0747E8D');
        $this->addSql('ALTER TABLE user__user_group DROP FOREIGN KEY FK_45528670FE54D947');
        $this->addSql('ALTER TABLE user__user_group DROP FOREIGN KEY FK_45528670A76ED395');
        $this->addSql('DROP TABLE classification__category');
        $this->addSql('DROP TABLE classification__collection');
        $this->addSql('DROP TABLE classification__context');
        $this->addSql('DROP TABLE classification__tag');
        $this->addSql('DROP TABLE jms_cron_jobs');
        $this->addSql('DROP TABLE jms_jobs');
        $this->addSql('DROP TABLE jms_job_dependencies');
        $this->addSql('DROP TABLE media__gallery');
        $this->addSql('DROP TABLE media__gallery_tag');
        $this->addSql('DROP TABLE media__gallery_media');
        $this->addSql('DROP TABLE media__media');
        $this->addSql('DROP TABLE media__media_tag');
        $this->addSql('DROP TABLE user__group');
        $this->addSql('DROP TABLE user__user');
        $this->addSql('DROP TABLE jms_job_related_entities');
        $this->addSql('DROP TABLE jms_job_statistics');
    }
}
