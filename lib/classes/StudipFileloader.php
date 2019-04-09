<?php
/**
 * @author     <sebastian@phpunit.de>
 * @author     <mlunzena@uos.de>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
class StudipFileloader
{
    /**
     * Loads a PHP sourcefile and transfers all therein defined
     * variables into a specified container.
     * Optionally you may inject more bindings into the scope, if the
     * sourcefile requires them.
     *
     * @param  string $_filename   which file to load
     * @param  array  $_container  where to put the new variables into
     * @param  array  $_injected   optional bindings, to inject into
     *                             the scope before loading
     * @param bool $_allow_overwrite allow overwriting of injected
     */
    public static function load($_filename, &$_container, $_injected = [], $_allow_overwrite = false)
    {
        extract($_injected);

        $_oldVariableNames = array_keys(get_defined_vars());

        foreach (preg_split('/ /', $_filename, -1, PREG_SPLIT_NO_EMPTY) as $file) {
            include $file;
        }
        unset($file);

        $newVariables     = get_defined_vars();

        unset($newVariables['_filename']);
        unset($newVariables['_container']);
        unset($newVariables['_injected']);
        unset($newVariables['_allow_overwrite']);
        unset($newVariables['_oldVariableNames']);

        if ($_allow_overwrite) {
            $newVariableNames = array_keys($newVariables);
        } else {
            $newVariableNames = array_diff(
                array_keys($newVariables), $_oldVariableNames
            );
        }

        foreach ($newVariableNames as $variableName) {
                $_container[$variableName] = $newVariables[$variableName];
        }

    }
}
