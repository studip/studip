<?php
/**
 * AdmissionRuleCompatibility.php
 * model class for table admissionrule_compat
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @copyright   2016 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5
 *
 * @property string rule_type database column
 * @property string compat_rule_type database column
 * @property int mkdate database column
 * @property int chdate database column
 */
class AdmissionRuleCompatibility extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'admissionrule_compat';
        parent::configure($config);
    }

    public static function getCompatibilityMatrix()
    {
        $types = AdmissionRule::getAvailableAdmissionRules(false);

        $matrix = [];
        foreach ($types as $class => $data) {
            $compat = self::findByRule_type($class);

            foreach ($compat as $c) {
                $matrix[$class][] = $c->compat_rule_type;
            }
        }

        return $matrix;
    }

}
