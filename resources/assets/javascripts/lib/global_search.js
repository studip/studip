const GlobalSearch = {
    lastSearch: null,

    /**
     * Toggles visibility of search input field and hints.
     * @param visible boolean indicating whether shown or not
     * @param cleanup boolean whether to clear search term and results
     * @returns {boolean}
     */
    toggleSearchBar: function(visible, cleanup) {
        $('#globalsearch-searchbar').toggleClass('is-visible', visible);
        $('#globalsearch-input').toggleClass('hidden-small-down', !visible);
        $('#globalsearch-icon').toggleClass('hidden-small-down', visible);
        $('#globalsearch-clear').toggleClass('hidden-small-down', !visible);

        if (!visible && cleanup) {
            GlobalSearch.lastSearch = null;
            $('#globalsearch-searchbar').removeClass('has-value');
            $('#globalsearch-results').html('');
            $('#globalsearch-input').blur().val('');
        }

        $('html:not(.size-large)').toggleClass('globalsearch-visible', visible);

        return false;
    },

    /**
     * Performs the actual search.
     */
    doSearch: function() {
        var searchterm = $('#globalsearch-input').val().trim();
        var hasValue = searchterm.length >= 3;
        var results = $();
        var resultsDiv = $('#globalsearch-results');
        var resultsPerType = resultsDiv.data('results-per-type');
        var moreResultsText = resultsDiv.data('more-results');
        var limit = resultsPerType * 3;
        var currentSemester = resultsDiv.data('current-semester');
        var wrapper = $('#globalsearch-searchbar');

        if (searchterm === '') {
            return;
        }

        wrapper.toggleClass('has-value', hasValue);

        if (!hasValue || GlobalSearch.lastSearch === searchterm) {
            return;
        }

        GlobalSearch.lastSearch = searchterm;

        // Display spinner symbol, user should always see something is happening.
        wrapper.addClass('is-searching');

        // Call AJAX endpoint and get search results.
        $.getJSON(STUDIP.URLHelper.getURL('dispatch.php/globalsearch/find/' + limit), {
            search: searchterm,
            filters: '{"category":"show_all_categories","semester":"' + currentSemester + '"}'
        }).done(function(json) {
            resultsDiv.empty();

            // No results found...
            if (!$.isPlainObject(json) || $.isEmptyObject(json)) {
                wrapper.removeClass('is-searching');
                resultsDiv.html(resultsDiv.data('no-result'));
                return;
            }

            // Iterate over each result category.
            $.each(json, function(name, value) {
                // Create an <article> for category.
                var category = $(`<article id="globalsearch-${name}">`),
                    header = $('<header>').appendTo(category),
                    counter = 0;

                // Create header name
                $('<a href="#">')
                    .text(value.name)
                    .wrap('<div class="globalsearch-category">')
                    .parent() // Element is now the wrapper
                    .data('category', name)
                    .appendTo(header);

                // We have more search results than shown, provide link to
                // full search if available.
                if (value.more && value.fullsearch !== '') {
                    $('<a>')
                        .attr('href', value.fullsearch)
                        .text(moreResultsText)
                        .wrap('<div class="globalsearch-more-results">')
                        .parent() // Element is now the wrapper
                        .appendTo(header);
                }

                // Process results and create corresponding entries.
                $.each(value.content, function(index, result) {
                    // Create single result entry.
                    var single = $('<section>'),
                        data = $('<div class="globalsearch-result-data">'),
                        details = $('<div class="globalsearch-result-details">');

                    if (counter >= resultsPerType) {
                        single.addClass('globalsearch-extended-result');
                    }

                    var dataDialog = (name === 'GlobalSearchFiles' ? dataDialog = 'data-dialog' : dataDialog = '');
                    var link = $(`<a href="${result.url}" ${dataDialog}>`).appendTo(single);

                    // Optional image...
                    if (result.img !== null) {
                        $(`<img src="${result.img}">`)
                            .wrap('<div class="globalsearch-result-img">')
                            .parent() // Element is now the wrapper
                            .appendTo(link);
                    }

                    link.append(data);

                    // Name/title
                    $('<div class="globalsearch-result-title">')
                        .html(result.name)
                        .appendTo(data);

                    // Details: Descriptional text
                    if (result.description !== null) {
                        $('<div class="globalsearch-result-description">')
                            .html(result.description)
                            .appendTo(details);
                    }

                    // Details: Additional information
                    if (result.additional !== null) {
                        $('<div class="globalsearch-result-additional">')
                            .html(result.additional)
                            .appendTo(details);
                    }

                    data.append(details);

                    // Date/Time of entry
                    if (result.date !== null) {
                        $('<div class="globalsearch-result-time">')
                            .html(result.date)
                            .appendTo(link);
                    }

                    // "Expand" attribute for further, result-related search
                    // (e.g. search in course of found forum entry)
                    if (result.expand !== null && result.expand !== value.fullsearch && value.more) {
                        $(`<a href="${result.expand}" title="${result.expandtext}">`)
                            .wrap('<div class="globalsearch-result-expand">')
                            .parent() // Element is now the wrapper
                            .appendTo(single);
                    }
                    category.append(single);

                    counter += 1;
                });
                results = results.add(category);
            });

            resultsDiv.html(results);
            wrapper.removeClass('is-searching');
        }).fail(function(xhr, status, error) {
            if (error) {
                window.alert(error);
            }
        });
    },

    /**
     * Clear search term and remove results for previous search term.
     */
    resetSearch: function() {
        GlobalSearch.lastSearch = null;

        $('#globalsearch-searchbar').removeClass('is-visible has-value');
        $('#globalsearch-input').val('');
        $('#globalsearch-results').html('');
        $('#globalsearch-input').focus();
    },

    /**
     * Expand a single category, showing more results, and hide other
     * categories.
     * @param category
     * @returns {boolean}
     */
    expandCategory: function(category) {
        // Hide other categories.
        $(`#globalsearch-results article:not([id="globalsearch-${category}"])`).hide();
        // Show all results.
        $(`#globalsearch-${category} section.globalsearch-extended-result`).removeClass(
            'globalsearch-extended-result'
        );
        $(`article#globalsearch-${category}`).get(0).scrollIntoView();
        // Reassign category click to closing extended view.
        $(`#globalsearch-results article#globalsearch-${category} header div.globalsearch-category a`)
            .off('click')
            .on('click', function() {
                GlobalSearch.showAllCategories(category);
                return false;
            });
        return false;
    },

    /**
     * Close expanded view of a single category, showing normal view with
     * all categories again.
     * @param currentCategory
     */
    showAllCategories: function(currentCategory) {
        $(`#globalsearch-results article#globalsearch-${currentCategory} header div.globalsearch-category a`)
            .off('click')
            .on('click', function() {
                GlobalSearch.expandCategory(currentCategory);
                return false;
            });
        var resultCount = $('#globalsearch-results').data('results-per-type') - 1;
        $(`#globalsearch-${currentCategory} section:gt(${resultCount})`).addClass(
            'globalsearch-extended-result'
        );
        $('#globalsearch-results')
            .children(`article:not([id="globalsearch-${currentCategory}"])`)
            .show();
        return false;
    }
};

export default GlobalSearch;
