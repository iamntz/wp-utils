<?php

namespace iamntz\wpUtils\db;

/**
 * Class DB
 *
 * Usage: extend this class and instead of `$wpdb->*` use `$this->db()->`
 *
 * @package iamntz\wpUtils
 */

abstract class DB
{
    protected $tableName = null;
    private $wpdb;

    public function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;

        $this->tableName = $this->wpdb->prefix . $this->getTableName();
    }

    protected function getSchemaVersionOption()
    {
        return $this->getTableName() . '_schema_version';
    }

    protected function db()
    {
        if (version_compare(
            $this->getSchemaVersion(),
            get_option($this->getSchemaVersionOption(), ''),
            '<='
        )) {
            return $this->wpdb;
        }

        $this->prepareDatabaseStructure();

        return $this->wpdb;
    }

    private function prepareDatabaseStructure()
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $schema = $this->getSchema();

        // todo: add error handling
        $delta = array_map('dbDelta', $schema);

        update_option($this->getSchemaVersionOption(), $this->getSchemaVersion());
    }

    public function empty()
    {
        return $this->db()->get_results("DELETE FROM {$this->tableName}");
    }

    /**
     * An array of db queries
     * @return array
     */
    abstract protected function getSchema(): array;

    /**
     * SemVer formatted
     * @return string
     */
    abstract protected function getSchemaVersion(): string;

    abstract protected function getTableName(): string;
}