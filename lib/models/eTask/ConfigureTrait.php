<?php

namespace eTask;

/**
 * eTask SORM class relationship configuration trait.
 *
 * eTask models are meant to be subclassed. As the current SORM
 * implementation hardcodes the types of relationships, this trait is
 * used to re-wire the relationships according to the applications
 * needs.
 *
 * In this case your application should add configuration either via
 * the #configure method or via another trait.
 *
 * Example:
 * \code
 *        $config['relationTypes'] = [
 *           'Assignment'      => '\\Example\\Assignment',
 *           'AssignmentRange' => '\\Example\\AssignmentRange',
 *           'Attempt'         => '\\Example\\Attempt',
 *           'Response'        => '\\Example\\Response',
 *           'Task'            => '\\Example\\Task',
 *           'Test'            => '\\Example\\Test',
 *           'TestTask'        => '\\Example\\TestTask'
 *       ];
 * \encode
 */
trait ConfigureTrait
{
    /**
     * @property array relationTypes lookup table of potential
     * subclasses to be used as types of the eTask SORMs
     */
    protected $relationTypes = [];

    // set default relationship types
    private static function configureClassNames($config = [])
    {
        $defaultTypes = [
            'Assignment' => '\\eTask\\Assignment',
            'AssignmentRange' => '\\eTask\\AssignmentRange',
            'Attempt' => '\\eTask\\Attempt',
            'Response' => '\\eTask\\Response',
            'Task' => '\\eTask\\Task',
            'Test' => '\\eTask\\Test',
            'TestTask' => '\\eTask\\TestTask'
        ];

        $types = [];

        if (!isset($config['relationTypes'])) {
            $types = $defaultTypes;
        } else {
            foreach ($defaultTypes as $key => $classname) {
                $types[$key] = $config['relationTypes'][$key] ?: $classname;
            }
        }

        return $types;
    }
}
