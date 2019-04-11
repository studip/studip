<script type="text/template" class="activity_stream">
    <%- activity.id %>
    <% if (num_entries > 0) {
        var last_date;

        _.each(stream, function(act) { %>
            <%
            var new_date = new Date(act.mkdate * 1000);
            var new_date_string = ('0' + new_date.getDate()).slice(-2) + '.'
                                + ('0' + (new_date.getMonth()+1)).slice(-2) + '.'
                                + new_date.getFullYear();
            if (last_date !=  new_date_string) { %>
                <% last_date = new_date_string; %>
                <span class="activity-day"><%- last_date %></span>
            <% } %>
            <%= activity({
                    activity      : act,
                    user_id       : user_id,
                    activity_urls : activity_urls
                }) %>
        <% });
    } else { %>
        <?= MessageBox::info(_('Keine (weiteren) Aktivitäten gefunden.')) ?>
    <% } %>
</script>

<script type="text/template" class="activity-load-error">
    <?= MessageBox::error(_('Aktivitäten konnten nicht geladen werden')) ?>
</script>

<script type="text/template" class="activity">
    <section class="activity">
        <header>
            <span class="provider_circle <% if (activity.actor.id == user_id) { %>right<% } else { %>left<% } %>">
            <% var treffer = activity.provider.match(/.*\\(.*)Provider/) %>
            <% var provider = treffer[1].toLowerCase(); %>
            <% if (provider === 'blubber') { %>
                <?= Icon::create('blubber', 'info_alt')->asImg(32) ?>
            <% } else if(provider === 'documents') { %>
                <?= Icon::create('files', 'info_alt')->asImg(32) ?>
            <% } else if(provider === 'forum') { %>
                <?= Icon::create('forum', 'info_alt')->asImg(32) ?>
            <% } else if(provider === 'literature') { %>
                <?= Icon::create('literature', 'info_alt')->asImg(32) ?>
            <% } else if(provider === 'message') { %>
                <?= Icon::create('mail', 'info_alt')->asImg(32) ?>
            <% } else if(provider === 'news') { %>
                <?= Icon::create('news', 'info_alt')->asImg(32) ?>
            <% } else if(provider === 'participants') { %>
                <?= Icon::create('persons', 'info_alt')->asImg(32) ?>
            <% } else if(provider === 'schedule') { %>
                <?= Icon::create('schedule', 'info_alt')->asImg(32) ?>
            <% } else if(provider === 'wiki') { %>
                <?= Icon::create('wiki', 'info_alt')->asImg(32) ?>
            <% } else { %>
                <?= Icon::create('activity', 'info_alt')->asImg(32) ?>
            <% } %>
            </span>
            <div class="activity-heading">
            <% if (activity.actor.type !== 'anonymous') { %>
                <a href="<%- STUDIP.URLHelper.resolveURL('dispatch.php/profile?username=' + activity.actor.details.name.username) %>">
                    <img src="<%- activity.actor.details.avatar_medium  %>" class="activity-avatar">
                </a>
            <% } %>
                <h3>
                    <%- activity.title %>
                </h3>
            </div>
            <div class="activity-date">
                <?= _('Am <%- new Date(activity.mkdate * 1000).toLocaleString() %> Uhr') ?>
            </div>


        </header>
        <section class="activity-content">
            <section class="activity-description">
                <span class="activity-details">
                    <%= activity.content %>
                </span>
            </section>
            <div class='clear'></div>
        </section>
        <footer>
            <span class="activity-object-link">
                <%= activity_urls({urls: activity.object_url}) %>
            </span>
        </footer>

    </section>
</script>

<script type="text/template" class="activity-urls">
    <ul>
    <% _.each(urls, function(name, link) { %>
        <li>
            <a href="<%= link %>">
                <%- name %>
            </a>
        </li>
    <% }) %>
    </ul>
</script>
