<?php
namespace Assets;

/**
 * Compiler interface for all compilers that may compile assets.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.4
 */
interface Compiler
{
    /**
     * Returns an instance of the compiler
     * @return Compiler instance
     */
    public static function getInstance();

    /**
     * Compiles an input string. Additional variables may be passed.
     *
     * @param String $input     Content to compile
     * @param Array  $variables Additional variables for the compilation
     * @return String containing the generated CSS
     */
    public function compile($input, array $variables = []);
}
