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

    public function createConfigLocalInc($host, $user, $password, $database, $env)
    {
        $replacements = [
            'DB_STUDIP_HOST'     => $host,
            'DB_STUDIP_USER'     => $user,
            'DB_STUDIP_PASSWORD' => $password,
            'DB_STUDIP_DATABASE' => $database,
        ];

        $template = file_get_contents($this->base_path . '/config/config_local.inc.php.dist');

        foreach ($replacements as $needle => $replacement) {
            $template = preg_replace(
                '/(\$' . $needle. '\\s*=\\s*(["\']))(?:.*)(?:\2);/',
                "\${$needle} = '{$replacement}';",
                $template
            );
        }

        $template = preg_replace(
            '/const ENV = (["\'])(?:.*?)(?:\1);/',
            "const ENV = '{$env}';",
            $template
        );

        return $template;
    }
}
