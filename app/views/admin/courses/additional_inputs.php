<tr>
    <th colspan="<?= $colspan ?>" style="text-align: right">
        <? switch ($selected_action) {
            case 8 :
                echo $this->render_partial('admin/courses/lock_preselect.php', compact('values', 'semid'));
                break;
            default:
            case 9:
                echo '<label>', _('Alle auswählen'), '<input title="', _('Alle auswählen'), '"
                                type="checkbox" name="all" value="1" data-proxyfor=".course-admin td:last-child :checkbox" aria-label="',
                _('Alle auswählen'), '"/></label>';
                break;
            case 10:
                echo $this->render_partial('admin/courses/aux_preselect.php', compact('values', 'semid'));
                break;
            case 16:
                echo '<label>', _('Alle auswählen'), '<input title="', _('Alle auswählen'), '"
                                type="checkbox" name="all" value="1" data-proxyfor=":checkbox[name^=archiv_sem]" aria-label="',
                _('Alle auswählen'), '"/></label>';
                break;
            case 17:
                echo '<label>', _('Alle auswählen'), '<input title="', _('Alle auswählen'), '"
                                type="checkbox" name="all" value="1" data-proxyfor=":checkbox[name^=admission_locked]" aria-label="',
                _('Alle auswählen'), '"/></label>';
                break;
        }?>
    </th>
</tr>