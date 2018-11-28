//must be overridden to display html in autocomplete like avatars:
var method_name = "_renderItem";
jQuery.ui.autocomplete.prototype[method_name] = function (ul, item) {
    return jQuery("<li></li>")
        .data("item.autocomplete", item)
        .append(jQuery("<a></a>").html(item.label))
        .appendTo(ul);
};
