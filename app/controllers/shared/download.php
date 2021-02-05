<?php
class Shared_DownloadController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        $this->allow_nobody = Config::get()->COURSE_SEARCH_IS_VISIBLE_NOBODY;

        parent::before_filter($action, $args);
    }
    
    /**
     * @param type $format only pdf is implememted yet
     * @param type $semester_id
     * @param type $version_id
     * @param type $language
     */
    public function modulhandbuch_action($format, $semester_id, $version_id, $language = 'DE', $size = 'medium')
    {
        //de_DE|en_GB
        $current_lang = $_SESSION['_language'];
        if ($language === 'DE') {
            $_SESSION['_language'] = 'de_DE';
            init_i18n('de_DE');
        } else if ($language === 'EN') {
            init_i18n('en_GB');
            $_SESSION['_language'] = 'en_GB';
        }

        include  $GLOBALS['STUDIP_BASE_PATH'] . '/config/mvv_config.php';
        
        $this->MHBPdf($semester_id, $version_id, $language);

        init_i18n($current_lang);
        $_SESSION['_language'] = $current_lang;
        include  $GLOBALS['STUDIP_BASE_PATH'] . '/config/mvv_config.php';
    }
    
    private function MHBPdf($semester_id, $version_id, $language)
    {
        $semester = Semester::find($semester_id);

        $this->StgteilVersion  = StgteilVersion::find($version_id);
        $this->module          = self::getVersionModules($this->StgteilVersion, $semester);
        $this->semName         = $semester->name;
        $this->Stgteile        = [];
        $this->modulseminare   = NULL;
        $this->veranstaltungen = NULL;
        $this->dozenten        = NULL;
        $this->archiv          = $this;
        $this->language        = $language;
        PageLayout::removeStylesheet('style.css');

        $needle = '<div style="page-break-after:always;">';
        $style =  '<style>
                    table.mvv-modul-details {
                        padding: 3px;
                        border-collapse: collapse;
                        hyphens: auto;
                        font: 2pt normal;
                        width: 100%;
                    }
                    table.mvv-modul-details td, table.mvv-modul-details th {
                        border-bottom: 1px solid #c9cccf;
                        hyphens: auto;
                        margin: 3px;
                    }
                    table.mvv-modul-details th {
                        text-align: left;
                        vertical-align: top;
                        hyphens: auto;
                    }
                    img {
                        display: none;
                    }
                    a { text-decoration: none;
                        color: black;
                    }</style>';
        $html = $this->render_template_as_string('shared/download/mhb');
        $split = explode($needle, $html);
        $next = false;
        $blocks = [];
        foreach($split as $block) {
            $blocks[] = $style . (($next) ? $needle : '') . $block;
            $next =  true;
        }
        $this->exportTcpdf($blocks, self::sanitizeFilename(
            _('Modulhandbuch'),
            trim($this->StgteilVersion->studiengangteil->getDisplayName()),
            $semester->semester_token ?: $semester->name,
            $language
        ));
    }
    
    /**
     * Renders a template and outputs it as a PDF file.
     * @param string $view_path the path of the template (controller/view...)
     * @param string $title the title, optional. If not set, takes title from pagelayout.
     * @param bool $force_pdf forces PDF download, oterwise can be overridden by setting the request no_export.
     * @return type
     */
    protected function exportTcpdf($html, string $title = '')
    {
        $pdf = new ExportPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        // setting defaults
        $this->config = Config::GetInstance();
        $pdf->SetCreator('Stud.IP - ' . $this->config->getValue('UNI_NAME_CLEAN'));
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', 8));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', 8));
        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        //set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        //set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        // set default font subsetting mode
        $pdf->setFontSubsetting(true);
        // Set font
        $pdf->SetFont('helvetica', '', 7, '', true);

        $pdf->AddPage();

        $blocks = is_array($html) ? $html : [$html];
        foreach($blocks as $html_block) {
            $pdf->writeHTMLCell(0, 0, '', '', $html_block , 0, 1, 0, true, '', true);
        }

        $output = $pdf->Output(null, 'S');

        $filename = trim($title ?: PageLayout::getTitle());

        $this->set_content_type('application/pdf');
        $this->response->add_header('Content-Disposition', sprintf(
            'attachment;filename="%s.pdf"',
            preg_replace('/_{2,}/', '_', preg_replace('/\W/', '_', $filename))
        ));
        $this->response->add_header('Content-Length', strlen($output));
        $this->render_text($output);
    }
    
    private static function sanitizeFilename($filename)
    {
        $replacements = [
            'ä' => 'ae',
            'ö' => 'oe',
            'ü' => 'ue',
            'Ä' => 'Ae',
            'Ö' => 'Oe',
            'Ü' => 'Ue',
            'ß' => 'ss',
        ];

        $filename = implode(' ', func_get_args());
        $filename = str_replace(array_keys($replacements), array_values($replacements), $filename);
        $filename = preg_replace('/\W+/', '_', $filename);
        $filename = preg_replace('/_{2,}/', '_', $filename);

        return $filename;
    }
    
    public function getMVVPluginModulDescription($modul, $display_language = null)
    {
        if ($display_language == null) {
            $display_language = $GLOBALS['MVV_LANGUAGES']['default'];
        }

        $path = $GLOBALS['STUDIP_BASE_PATH'] . '/app/views/shared/modul/';
        $factory = new Flexi_TemplateFactory($path);

        $template = $factory->open('_modul');
        $template->_ = function ($string) { return $this->_($string); };
        $template->modul = $modul;
        $template->display_language = $display_language;
        $content = $template->render();

        $factory = new \Flexi_TemplateFactory($path);
        $type = 1;
        if (count($modul->modulteile) == 1) {
            $modulteil = $modul->modulteile->first();
            $type = 2;
        } elseif (count($modul->modulteile) === 0) {
            $type = 3;
        }
        if ($type === 1 || $type === 2) {
            $template = $factory->open('_pruefungen');
            $template->_ = function ($string) { return $this->_($string); };
            $template->modul =  $modul;
            $template->display_language = $display_language;
            $content .= $template->render();

            if ($type === 1) {
                $template = $factory->open('_modullvs');
                $template->_ = function ($string) { return $this->_($string); };
                $template->modul = $modul;
                $template->display_language = $display_language;
                $content .= $template->render();

            }
            if ($type === 2) {
                $template = $factory->open('_modullv');
                $template->_ = function ($string) { return $this->_($string); };
                $template->modul = $modul;
                $template->display_language = $display_language;
                $content .= $template->render();
            }
        }

        return $content;
    }
    
    /**
     * Retrieves all modules assigned to the given Studiengangteilversion
     * grouped by Studiengangteilabschnitte
     * 
     * @param StgteilVersion $StgteilVersion
     * @param Semester $semester
     * @return type
     */
    private static function getVersionModules(StgteilVersion $stgteil_version, Semester $semester)
    {
        $stgtv_startsemester = Semester::find($stgteil_version->getValue('start_sem'));
        $stgtv_endsemester   = Semester::find($stgteil_version->getValue('end_sem'));
        $modul_startsemester = $semester;
        $modul_endsemester   = $semester;
        $result              = [];
        $all_semesters       = SimpleORMapCollection::createFromArray(Semester::getAll());
        $public_state        = ModuleManagementModel::getPublicStatus('Modul');

        if ($stgtv_startsemester->beginn > $semester->beginn) {
            return [];
        }
        if($stgtv_endsemester != NULL && $semester->beginn > $stgtv_endsemester->beginn) {
           return [];
        }

        foreach ($stgteil_version->abschnitte as $teilabschnitt) {
            if (!isset($result[$teilabschnitt->id])) {
                $result[$teilabschnitt->id]['part'] = $teilabschnitt;
            }
            $modullist = [];

            foreach ($teilabschnitt->modul_zuordnungen as $abs_modul) {
                $modul = $abs_modul->modul;

                if (!in_array($modul->stat, $public_state)) {
                    continue;
                }

                if ($modul->start != $modul_startsemester->id) {
                    $modul_startsemester = $all_semesters->find($modul['start']);
                }

                if (!empty($modul->end) && $modul->end != $modul_endsemester->id) {
                    $modul_endsemester = $all_semesters->find($modul['end']) ?: false;
                }

                if ($modul_startsemester->beginn <= $semester->beginn) {
                    if(!empty($modul->end) && $semester->beginn > $modul_endsemester->beginn) {
                        continue;
                    }
                    if (count($modul->abschnitte_modul) >= 2) {
                        $ab_modul = $modul->abschnitte_modul->findBy('abschnitt_id', $teilabschnitt->abschnitt_id);
                        if (count($ab_modul) == 1) {
                            $modul->abschnitte_modul = $ab_modul;
                        }
                    }
                    $modullist[$modul->id] = $modul;
                }
            }

            if (!empty($modullist)) {
                $result[$teilabschnitt->abschnitt_id]['modules'] = $modullist;
            } else {
                unset($result[$teilabschnitt->abschnitt_id]);
            }
        }
        return $result;
    }
}