<?php

/**
 * Institution Type Master Configuration
 *
 * Single source of truth for:
 *  - Institution type dropdown options (slug => label)
 *  - Allowed Academic Class numeric range per institution type
 *
 * IMPORTANT:
 *  - "slug" MUST exactly match the value stored in institutions.institution_type
 *    (string column). Do not rename existing slugs (school, college,
 *    school-college, madrasa) without a data migration, or existing
 *    institution records will lose their type mapping.
 *  - "min_numeric" / "max_numeric" correspond to academic_classes.numeric.
 *  - "sort_order" controls the order options appear in the dropdown.
 */

return [

    'kindergarten' => [
        'label'       => 'Kindergarten',
        'min_numeric' => 0,
        'max_numeric' => 5,
        'sort_order'  => 1,
    ],

    'primary' => [
        'label'       => 'Primary School',
        'min_numeric' => 1,
        'max_numeric' => 5,
        'sort_order'  => 2,
    ],

    'school' => [
        'label'       => 'High School',
        'min_numeric' => 1,
        'max_numeric' => 10,
        'sort_order'  => 3,
    ],

    'college' => [
        'label'       => 'Higher Secondary / College',
        'min_numeric' => 11,
        'max_numeric' => 12,
        'sort_order'  => 4,
    ],

    'school-college' => [
        'label'       => 'School & College',
        'min_numeric' => 1,
        'max_numeric' => 12,
        'sort_order'  => 5,
    ],

    'madrasa' => [
        'label'       => 'Madrasa',
        'min_numeric' => 1,
        'max_numeric' => 12,
        'sort_order'  => 6,
    ],

];