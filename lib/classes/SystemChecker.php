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

        $valid = $version['valid'];

        $modules = [];
        foreach ($requirements['modules'] as $module => $requirement) {
            $modules[$module] = in_array($module, get_loaded_extensions());
            if (!$modules[$module] && $requirement === true) {
                $valid = false;
            }
        }

        $settings = [];
        foreach ($requirements['settings'] as $setting => $state) {
            $settings[$setting] = $this->compareSetting(ini_get($setting), $state);

            $valid = $valid && $settings[$setting]['valid'];
        }

        return [
            'valid'   => $valid,
            'version' => $version,
            'modules' => [
                'required' => $requirements['modules'],
                'present'  => $modules,
            ],
            'settings' => $settings,
        ];
    }

    public function checkMySQLConnection($host, $user, $password, $database)
    {
        return new PDO(
            "mysql:host={$host};dbname={$database};charset=utf8mb4",
            $user,
            $password
        );
    }

    public function checkMySQLRequirements($host, $user, $password, $database)
    {
        $pdo = $this->checkMySQLConnection($host, $user, $password, $database);

        $requirements = $this->getRequirements('mysql');

        $version = $this->compareVersion(
            $pdo->query('SELECT VERSION()')->fetchColumn(),
            $requirements['version']
        );

        $valid = $version['valid'];

        $variables = $pdo->query('SHOW VARIABLES')->fetchAll(PDO::FETCH_KEY_PAIR);
        $variables['innodb_large_prefix'] = 'ON';

        // echo '<dl>';
        // foreach ($variables as $var => $value) {
        //     echo "<dt>{$var}</dt><dd>{$value}</dd>";
        // }
        // echo '</dl>';
        // die;

        $settings = [];
        foreach ($requirements['settings'] as $setting => $state) {
            $settings[$setting] = $this->compareSetting($variables[$setting], $state, $version['present']);
        }

        return [
            'valid'   => $valid,
            'version' => $version,
            'settings' => $settings,
        ];
    }

    public function checkPermissions()
    {
        $requirements = $this->getRequirements('writable');

        $writable = [];
        foreach ($requirements as $f) {
            $writable[$f] = is_writable($this->base_path . '/' . $f);
        }
        return $writable;
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
