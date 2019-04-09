<?php

class PdfLogoConfiguration extends Migration
{
    /**
     * short description of this migration
     */
    function description()
    {
        return 'adds a configuration for an optional logo for pdf-output';
    }

    /**
     * perform this migration
     */
    function up()
    {
        $options[] =
            [
            'name'        => 'PDF_LOGO',
            'type'        => 'string',
            'value'       => '',
            'section'     => 'global',
            'description' => 'Geben Sie hier den absoluten Pfad auf Ihrem Server (also ohne http) zu einem Logo an, das bei PDF-Exporten im Kopfbereich verwendet wird.'
            ];

        $stmt = DBManager::get()->prepare("
                INSERT IGNORE INTO config
                    (config_id, field, value, is_default, type, section, mkdate, chdate, description)
                VALUES
                    (MD5(:name), :name, :value, 1, :type, :section, UNIX_TIMESTAMP(),  UNIX_TIMESTAMP(), :description)
                ");

        foreach ($options as $option) {
            $stmt->execute($option);
        }
    }

    /**
     * revert this migration
     */
    function down()
    {
        $db = DBManager::get()->exec("DELETE FROM config WHERE field = 'PDF_LOGO'");
    }
}
