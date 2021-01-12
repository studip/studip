<?php
final class SystemChecker
{
    const REQUIREMENTS_FILE = 'config/studip-requirements.php';

    private $requirements = null;
    private $base_path;

    public function __construct($base_path)
    {
        $this->base_path = rtrim($base_path, '/');
    }

    public function getRequirements($section)
    {
        if ($this->requirements === null) {
            $requirements_file = $this->base_path . '/' . self::REQUIREMENTS_FILE;
            if (!file_exists($requirements_file) || !is_readable($requirements_file)) {
                throw new Exception('Requirements configuration does not exists or is not readable');
            }

            $this->requirements = require $requirements_file;
        }

        if (!isset($this->requirements[$section])) {
            throw new Exception("Unknown installer section '{$section}'");
        }

        return $this->requirements[$section];
    }

    public function checkPHPRequirements()
    {
        $requirements = $this->getRequirements('php');

        $version = $this->compareVersion(phpversion(), $requirements['version']);

        $modules = [];
        $modules_valid = true;
        foreach ($requirements['modules'] as $module => $requirement) {
            $modules[$module] = in_array($module, get_loaded_extensions());
            if (!$modules[$module] && $requirement === true) {
                $modules_valid = false;
            }
        }

        $settings = [];
        $settings_valid = true;
        foreach ($requirements['settings'] as $setting => $state) {
            $settings[$setting] = $this->compareSetting(ini_get($setting), $state);

            $settings_valid = $settings_valid && $settings[$setting]['valid'];
        }

        return [
            'valid'   => $version['valid'] && $modules_valid && $settings_valid,
            'version' => $version,
            'modules' => [
                'valid'    => $modules_valid,
                'required' => $requirements['modules'],
                'present'  => $modules,
            ],
            'settings' => [
                'valid'    => $settings_valid,
                'settings' => $settings,
            ],
        ];
    }

    private function getPDO($dsn, $user, $password)
    {
        $pdo = new PDO($dsn, $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }

    public function getMySQLConnection($host, $user, $password, $database, $create_database = false)
    {
        try {
            $dsn = "mysql:host={$host}";
            if ($database !== null) {
                $dsn .= ";dbname={$database}";
            }
            $dsn .= ";charset=utf8mb4";

            return $this->getPDO($dsn, $user, $password);
        } catch (Exception $e) {
            if (!$create_database || strpos($e->getMessage(), '[1049]') === false) {
                throw $e;
            }

            $dsn = "mysql:host={$host};charset=utf8mb4";
            $pdo = $this->getPDO($dsn, $user, $password);
            $pdo->exec("CREATE DATABASE `{$database}`");
            $pdo->exec("USE `{$database}`");
            return $pdo;
        }
    }

    public function checkMySQLRequirements($host, $user, $password, $database)
    {
        $pdo = $this->getMySQLConnection($host, $user, $password, $database);

        $requirements = $this->getRequirements('mysql');

        $version = $this->compareVersion(
            $pdo->query('SELECT VERSION()')->fetchColumn(),
            $requirements['version']
        );

        $variables = $pdo->query('SHOW VARIABLES')->fetchAll(PDO::FETCH_KEY_PAIR);

        $settings = [];
        $settings_valid = true;
        foreach ($requirements['settings'] as $setting => $state) {
            $settings[$setting] = $this->compareSetting($variables[$setting], $state, $version['present']);

            $settings_valid = $settings_valid && $settings[$setting]['valid'];
        }

        return [
            'valid'   => $version['valid'] && $settings_valid,
            'version' => $version,
            'settings' => [
                'valid'    => $settings_valid,
                'settings' => $settings,
            ],
        ];
    }

    public function checkPermissions()
    {
        $requirements = $this->getRequirements('writable');

        $valid = true;
        $paths = [];
        foreach ($requirements as $p => $required) {
            $paths[$p] = is_writable($this->base_path . '/' . $p);

            $valid = $valid && (!$required || $paths[$p]);
        }
        return compact('paths', 'valid');
    }

    private function compareVersion($present, $required)
    {
        return [
            'required' => $required,
            'present'  => $present,
            'valid'    => version_compare($present, $required, '>='),
        ];
    }

    private function compareSetting($present, $required, $assume = null)
    {
        if (!is_array($required)) {
            $valid = $present == $required;
            $cmp = '=';
        } else {
            if (!$present
                && $assume !== null
                && @$required['assume']
                && version_compare($assume, $required['assume'], '>=')
            ) {
                $present = $required['value'];
            }

            if (@$required['unit']) {
                $present           = $this->parseSize($present);
                $required['value'] = $this->parseSize($required['value']);
            }

            $cmp = $required['cmp'];

            if (is_bool($required['value'])) {
                $valid = $present == $required['value'];
                $cmp = '=';
            } else {
                $state = strnatcasecmp($present, $required['value']);
            }

            if (@$required['contains'] && strpos($present, $required['value']) !== false) {
                $valid = true;
                $cmp = '~=';
            } elseif (@$required['contains_not']) {
                $cmp = '!~=';
                $valid = !preg_match('/' . $required['value'] . '/', $present);
            } elseif (@$required['allow_empty'] && !$present) {
                $valid = true;
                $cmp = '~=';
            } elseif ($cmp === '<') {
                $valid = $state === -1;
            } elseif ($cmp === '<=') {
                $valid = $state !== 1;
            } elseif ($cmp === '>=') {
                $valid = $state !== -1;
            } elseif ($cmp === '>') {
                $valid = $state === 1;
            } elseif (!is_bool($required['value'])) {
                $valid = $state == 0;
                $cmp = '=';
            }

            if (@$required['unit']) {
                $present  = $this->convertSize($present);
                $required = $this->convertSize($required['value']);
            } else {
                $required = $required['value'];
            }
        }

        return compact('required', 'present', 'valid', 'cmp');
    }

    /**
     * @see https://stackoverflow.com/a/25370978/982902
     * @param  string $size
     * @return int
     */
    private function parseSize($size)
    {
        $units = 'BKMGTPEZY';

        $unit = preg_replace("/[^{$units}]/i", '', $size); // Remove the non-unit characters from the size.
        if (!$unit) {
            return round($size);
        }

        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
        return round($size * pow(1024, stripos($units, strtoupper($unit[0]))));
    }

    /**
     * @param  string $size
     * @return int
     */
    private function convertSize($size)
    {
        $units = 'BKMGTPEZY';

        $temp = $size;
        $index = 0;
        while ($temp && $temp % 1024 === 0) {
            $index += 1;
            $temp = $temp / 1024;
        }

        if ($index === 0) {
            return $size;
        }

        return $temp . $units[$index];
    }
}
