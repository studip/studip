<div class="searchform">
    <div class="oneliner">
        <div class="frame">
            <span v-if="category != null"
                  class="category activefilter" title="<?= _('Aktiver Filter der Kategorie') ?>">
                <span>{{ category }}</span>
                <a href="#"
                   @click.prevent="clearCategory"
                   class="erasefilter"
                   title="<?= _('Filter der Kategorie entfernen') ?>">
                    <studip-icon shape="decline" role="clickable" size="16" class="text-bottom"></studip-icon>
                </a>
            </span>

            <span v-if="difficulty[0] != 1 || difficulty[1] != 12"
                  class="niveau activefilter"
                  title="<?= _('Aktiver Filter für das Niveau') ?>">
                <?= _('Niveau') ?>: &nbsp;
                <span>{{ difficulty[0] }}</span>
                -
                <span>{{ difficulty[1] }}</span>
                <a href="#"
                   @click.prevent="clearDifficulty"
                   class="erasefilter"
                   title="<?= _('Filter des Niveaus entfernen') ?>">
                    <studip-icon shape="decline" role="clickable" size="16" class="text-bottom"></studip-icon>
                </a>
            </span>

            <input type="text"
                   name="search"
                   @focus="showFilterPanel"
                   @keyup="sync_search_text"
                   @keydown.enter.prevent="search">

            <button v-if="difficulty[0] != 1 || difficulty[1] != 12 || (category != null) || (searchtext.length > 0)"
                    class="erase"
                    type="button"
                    title="<?= _('Suchformular zurücksetzen') ?>"
                    @click="clearAllFilters">
                <studip-icon shape="decline" role="clickable"></studip-icon>
            </button>

            <button @click="triggerFilterPanel"
                    type="button"
                    title="<?= _('Suchfilter einstellen') ?>"
                    :class="activeFilterPanel ? 'active' : ''">
                <studip-icon shape="filter" :role="activeFilterPanel ? 'info_alt' : 'clickable'"></studip-icon>
            </button>

            <div v-if="activeFilterPanel" class="filterpanel_shadow"></div>

            <div v-if="activeFilterPanel" class="filterpanel">
                <div>
                    <h3><?= _('Kategorien') ?></h3>
                    <ul class="clean">
                        <li>
                            <a href="<?= $controller->link_for("oer/market", ['category' => "audio"]) ?>" @click.prevent="category = 'audio'">
                                <studip-icon v-if="category != 'audio'" shape="radiobutton-unchecked" role="clickable" size="16" class="text-bottom"></studip-icon>
                                <studip-icon v-if="category == 'audio'" shape="radiobutton-checked" role="clickable" size="16" class="text-bottom"></studip-icon>
                                <?= _('Audio') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= $controller->link_for("oer/market", ['category' => "video"]) ?>" @click.prevent="category = 'video'">
                                <studip-icon v-if="category != 'video'" shape="radiobutton-unchecked" role="clickable" size="16" class="text-bottom"></studip-icon>
                                <studip-icon v-if="category == 'video'" shape="radiobutton-checked" role="clickable" size="16" class="text-bottom"></studip-icon>
                                <?= _('Video') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= $controller->link_for("oer/market", ['category' => "presentation"]) ?>" @click.prevent="category = 'presentation'">
                                <studip-icon v-if="category != 'presentation'" shape="radiobutton-unchecked" role="clickable" size="16" class="text-bottom"></studip-icon>
                                <studip-icon v-if="category == 'presentation'" shape="radiobutton-checked" role="clickable" size="16" class="text-bottom"></studip-icon>
                                <?= _('Folien') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= $controller->link_for("oer/market", ['category' => "elearning"]) ?>" @click.prevent="category = 'elearning'">
                                <studip-icon v-if="category != 'elearning'" shape="radiobutton-unchecked" role="clickable" size="16" class="text-bottom"></studip-icon>
                                <studip-icon v-if="category == 'elearning'" shape="radiobutton-checked" role="clickable" size="16" class="text-bottom"></studip-icon>
                                <?= _('Lernmodule') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= $controller->link_for("oer/market", ['get' => "all"]) ?>">
                                <studip-icon shape="link-intern" role="clickable" size="16" class="text-bottom"></studip-icon>
                                <?= _('Alles') ?>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="level_filter">
                    <h3><?= _('Niveau') ?></h3>
                    <div class="level_labels">
                        <div><?= _('Kindergarten') ?></div>
                        <div><?= _('Aktuelle Forschung') ?></div>
                    </div>
                    <div class="level_numbers">
                        <? for ($i = 1; $i <= 12; $i++) : ?>
                            <div><?= ($i < 10 ? "&nbsp;" : "").$i ?></div>
                        <? endfor ?>
                    </div>
                    <div id="difficulty_slider"></div>

                    <input type="hidden" id="difficulty" name="difficulty" value="">
                </div>
            </div>


            <button title="<?= _('Suche starten') ?>" @click.prevent="search" @focus="hideFilterPanel">
                <studip-icon shape="search" role="clickable"></studip-icon>
            </button>
        </div>

    </div>


</div>

<div class="browser">

    <div v-if="browseMode === false" class="intro">
        <img src="<?= Assets::image_path("oer-keyvisual.svg") ?>" width="300px">
        <div>
            <h3><?= _('Wertvolle Lernmaterialien entdecken!') ?></h3>
            <div class="responsive-hidden">
                <?= _('Neue und spannende Lernmaterialien zu finden, ist ganz einfach. Mit dem Entdeckermodus können Sie nach Schlagwörtern stöbern und durch Themengebiete surfen.') ?>
            </div>

            <div>
                <?= \Studip\LinkButton::create(_('Zum Entdeckermodus'), "#", ['@click.prevent' => "browseMode=true"]) ?>
            </div>
        </div>
    </div>

    <div v-if="browseMode === true" class="tagcloud">
        <div>
            <h3><?= _('Wertvolle Materialien entdecken!') ?></h3>
            <?= _('Klicken Sie auf die Schlagwörter und entdecken Sie Lernmaterialien zum Thema.') ?>
        </div>
        <a v-if="tagHistory.length" href="" @click.prevent="backInCloud" class="back-button">
            <studip-icon shape="arr_1left" role="clickable" size="50"></studip-icon>
        </a>
        <ul class="tags clean">
            <li v-for="tag in tags">
                <a href="#"
                   class="button"
                   :style="getTagStyle(tag.tag_hash)"
                   :title="tag.name"
                   @click.prevent="browseTag(tag.tag_hash, tag.name)">{{"#" + tag.name}}</a>
            </li>
        </ul>
    </div>

</div>

<ul class="results oer_material_overview">
    <li v-for="result in results" :key="result.material_id">
        <article class="contentbox" :title="result.name">
            <a :href="getMaterialURL(result.material_id)">
                <header>
                    <h1>
                        <studip-icon :shape="getIconShape(result)"
                                     role="clickable"
                                     size="20"
                                     class="text-bottom"></studip-icon>
                        {{ result.name }}
                    </h1>
                </header>
                <div class="image" :style="'background-image: url(' + result.logo_url + ');' + (!result.front_image_content_type ? ' background-size: 60% auto;': '')"></div>
            </a>
        </article>
    </li>
</ul>
