<?php
    if ($edit_size) {
        echo $this->render_partial('course/statusgroups/_edit_groups_size', compact('groups'));
    } elseif ($edit_selfassign) {
        echo $this->render_partial('course/statusgroups/_edit_groups_selfassign', compact('groups'));
    } elseif ($askdelete) {
        echo $this->render_partial('course/statusgroups/_askdelete_groups', compact('groups'));
    } elseif ($movemembers) {
        echo $this->render_partial(
            'course/statusgroups/_move_members',
            compact('target_groups', 'members', 'source_group')
        );
    } elseif ($deletemembers) {
        echo $this->render_partial(
            'course/statusgroups/_askdelete_members',
            compact('members', 'source_group')
        );
    } elseif ($cancelmembers) {
        echo $this->render_partial(
            'course/statusgroups/_askcancel_members',
            compact('members')
        );
    }
