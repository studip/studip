module("markup toolbar", {

  setup: function () {
    this.klon = $("#textarea-editor").clone().removeAttr('id');
    $("#textarea-editor").after(this.klon);
    this.textarea = this.klon.find("textarea");
  },

  addToolbar: function (buttonSet) {
    $(this.textarea).addToolbar(buttonSet);
    return $(this.textarea).prev(".editor_toolbar");
  }
});


test("default toolbar for textarea", function () {
  $(this.textarea).addToolbar(STUDIP.Markup.buttonSet);
  var toolbar = this.textarea.prev(".editor_toolbar");
  ok(toolbar);
  equals(toolbar.find("button").length, STUDIP.Markup.buttonSet.length);
});

test("empty toolbar for textarea", function () {
  var toolbar = this.addToolbar([]);
  ok(toolbar);
  equals(toolbar.find("button").length, 0);
});

test("add single button to toolbar", function () {
  var toolbar = this.addToolbar([{name: "klass", "label": "label", open: "<", close: ">"}]);

  var buttons = toolbar.find("button");
  equals(buttons.length, 1);

  ok(buttons.first().hasClass("klass"));
  equals(buttons[0].innerHTML, "label");
  buttons.first().trigger('click');
  equals(this.textarea.val(), "<>");
});
