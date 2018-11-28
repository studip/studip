<?php foreach ($courses as $semid => $values) : ?>
    <?= $this->render_partial('admin/courses/_course', compact('semid', 'values', 'view_filter', 'actions', 'selected_action', 'parent', 'courses')) ?>
<?php endforeach;
