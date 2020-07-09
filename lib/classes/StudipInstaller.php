<?php
final class StudipInstaller
{
    const USERNAME_REGEX = '/^([a-zA-Z0-9_@.-]{4,})$/';
    const PASSWORD_REGEX = '/^([[:print:]]{8,72})$/';

    private $base_path;

    public function __construct($base_path)
    {
        $this->base_path = rtrim($base_path, '/');
    }

    public function updateConfigInDatabase(PDO $pdo, $key, $value)
    {
        $query = "INSERT INTO `config_values` (
                    `field`, `range_id`, `value`, `mkdate`, `chdate`, `comment`
                  ) VALUES (
                    :field, 'studip', :value, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), ''
                  )
                  ON DUPLICATE KEY
                     UPDATE `value` = VALUES(`value`),
                            `chdate` = VALUES(`chdate`)";
        $statement = $pdo->prepare($query);
        $statement->bindValue(':field', $key);
        $statement->bindValue(':value', $value);
        return $statement->execute();
    }

    public function createConfigLocalInc($host, $user, $password, $database, $env, $uri)
    {
        $template = file_get_contents($this->base_path . '/config/config_local.inc.php.dist');

        $replacements = [
            'DB_STUDIP_HOST'     => $host,
            'DB_STUDIP_USER'     => $user,
            'DB_STUDIP_PASSWORD' => $password,
            'DB_STUDIP_DATABASE' => $database,

            'ABSOLUTE_URI_STUDIP' => rtrim($uri, '/') . '/',
        ];
        foreach ($replacements as $needle => $replacement) {
            $template = $this->replaceVariable($needle, $replacement, $template);
        }

        $template = $this->replaceConst('ENV', $env, $template);

        return $template;
    }

    public function createConfigInc($uni_url, $uni_contact)
    {
        $template = file_get_contents($this->base_path . '/config/config.inc.php.dist');

        $replacements = [
            'UNI_URL'     => $uni_url,
            'UNI_CONTACT' => $uni_contact
        ];
        foreach ($replacements as $needle => $replacement) {
            $template = $this->replaceVariable($needle, $replacement, $template);
        }

        return $template;
    }

    public function createLibraryConfigInc()
    {
        file_put_contents(
            $this->base_path . '/config/library_config.inc.php',
            file_get_contents($this->base_path . '/config/library_config.inc.php.dist')
        );
    }

    private function replaceVariable($variable, $replacement, $subject)
    {
        return preg_replace(
            '/(?:\/\/\\s*)?(\$' . $variable. '\\s*=\\s*(["\']))(?:.*)(?:\2);/',
            "\${$variable} = '{$replacement}';",
            $subject
        );
    }

    private function replaceConst($constant, $replacement, $subject)
    {
        return preg_replace(
            '/const\\s+' . $constant . '\\s*=\\s*(["\'])(?:.*?)(?:\1);/',
            "const {$constant} = '{$replacement}';",
            $subject
        );

    }
}
