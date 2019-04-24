<?php
// "Rules"/definitions for critical changes in 4.2
return [
    'get_perm' => 'Use the #{yellow:CourseMember} or #{yellow:InstitutMember} model instead.',
    'get_vorname' => 'Use #{yellow:User::find($id)->vorname} instead',
    'get_nachname' => 'Use #{yellow:User::find($id)->nachname} instead',
    'get_range_tree_path' => false,
    'get_seminar_dozent' => 'Use #{yellow:Course::find($id)->getMembersWithStatus(\'dozent\')} instead.',
    'get_seminar_tutor' => 'Use #{yellow:Course::find($id)->getMembersWithStatus(\'tutor\')} instead.',
    'get_seminar_sem_tree_entries' => false,
    'get_seminars_users' => 'Use #{yellow:CourseMember::findByUser($user_id)} instead to aquire all courses.',
    'remove_magic_quotes' => false,
    'text_excerpt' => false,
    'check_group_new' => false,
    'insertNewSemester' => 'Use the #{yellow:Semester} model instead.',
    'updateExistingSemester' => 'Use the #{yellow:Semester} model instead.',
];
