STUDIP.Feedback = {

	initiate: function(feedback)
	{
        var range_id = $(feedback).attr('for');
        var range_type = $(feedback).attr('type');
        var course_id = $(feedback).attr('context');

		$(feedback).load(STUDIP.URLHelper.getURL('dispatch.php/course/feedback/index_for/' + range_id + '/' + range_type + '?cid=' + course_id), function()
		{
			if ($('.feedback-delete').length) {
				$('.feedback-delete').prop("onclick", null).off("click");
				$('.feedback-delete').click(function (event) {
					event.preventDefault();
					var id = $(this).attr('data-id');
					STUDIP.Dialog.confirm($(this).attr('data-confirm')).done(function() {
						STUDIP.Feedback.delete(id,feedback);
					});
				});
			}
			STUDIP.Feedback.initiateView();
		});
	},

	initiateView: function() {
		$('.feedback-entry-add').prop("onclick", null).off("click");
		$('.feedback-entry-add').find('.accept').click(function (event) {
			event.preventDefault();
			var id = $(this).closest('article').attr('data-id');
			var feedback_id = $(this).closest('form').serialize();
			STUDIP.Feedback.addEntry(id,feedback_id);
		});
		$('.feedback-entry-edit').prop("onclick", null).off("click");
		$('.feedback-entry-edit').click(function (event) {
			event.preventDefault();
			var entry_id = $(this).closest('article').attr('data-id');
			var feedback_id = $(this).closest('.feedback-stream').attr('data-id');
			STUDIP.Feedback.editEntryForm(entry_id,feedback_id) ;
		});
		$('.feedback-entry-delete').prop("onclick", null).off("click");
		$('.feedback-entry-delete').click(function (event) {
			event.preventDefault();
			var entry_id = $(this).closest('article').attr('data-id');
			var feedback_id = $(this).closest('.feedback-stream').attr('data-id');
			STUDIP.Dialog.confirm($(this).attr('data-confirm')).done(function() {
				STUDIP.Feedback.deleteEntry(entry_id,feedback_id);
			});
		});
		STUDIP.Feedback.initiateFeedbackEntryForm();
		if ($('table.sortable-table').length) {
			$('table.sortable-table').each(function(index, element) {
				STUDIP.Table.enhanceSortableTable(element);
			});
		}
	},
	delete: function(id,feedback)
	{
		var url = STUDIP.URLHelper.getURL('dispatch.php/course/feedback/delete/' + id);
		request = $.ajax({
            url: url,
            type: 'post'
		});
		request.done(function()
		{
			STUDIP.Feedback.initiate(feedback);
		});
	},
	addEntry: function(feedback_id,data)
	{
		var url = STUDIP.URLHelper.getURL('dispatch.php/course/feedback/entry_add/' + feedback_id);
		request = $.ajax({
            url: url,
            type: 'post',
            data: data
		});
		request.done(function()
		{
			STUDIP.Feedback.reloadView(feedback_id);
		});

	},
	editEntryForm: function(entry_id,feedback_id)
	{
		url = STUDIP.URLHelper.getURL('dispatch.php/course/feedback/entry_edit_form/' + entry_id);
		$('#feedback-stream-' + feedback_id).find('.feedback-view').load(url, function() {
			STUDIP.Feedback.initiateFeedbackEntryForm();
			$('#feedback-stream-' + feedback_id).find('.accept ').prop("onclick", null).off("click");
			$('#feedback-stream-' + feedback_id).find('.accept ').click(function (event) {
				event.preventDefault();
				var data = $(this).closest('form').serialize();
				STUDIP.Feedback.editEntry(entry_id,feedback_id,data);
			});
			$('#feedback-stream-' + feedback_id).find('.cancel').prop("onclick", null).off("click");
			$('#feedback-stream-' + feedback_id).find('.cancel').click(function (event) {
				event.preventDefault();
				STUDIP.Feedback.reloadView(feedback_id);
			});
		});
	},
	editEntry: function(entry_id,feedback_id,data)
	{
		var url = STUDIP.URLHelper.getURL('dispatch.php/course/feedback/entry_edit/' + entry_id);
		request = $.ajax({
            url: url,
            type: 'post',
            data: data
		});
		request.done(function()
		{
			STUDIP.Feedback.reloadView(feedback_id);
		});
	},
	deleteEntry: function(entry_id,feedback_id)
	{
		var url = STUDIP.URLHelper.getURL('dispatch.php/course/feedback/entry_delete/' + entry_id);
		request = $.ajax({
            url: url,
            type: 'post',
		});
		request.done(function()
		{
			STUDIP.Feedback.reloadView(feedback_id);
		});
	},
	reloadView: function(feedback_id) {
		url = STUDIP.URLHelper.getURL('dispatch.php/course/feedback/view/' + feedback_id);
		$('#feedback-stream-' + feedback_id).find('.feedback-view').load(url, function() {
			STUDIP.Feedback.initiateView();
		});
	},
	initiateFeedbackEntryForm: function() {
		if ($('.star-rating').length) {
			$('.star-rating').hover(
				function() {
					$(this).addClass('hover');
				  	$(this).prevAll('.star-rating').addClass('hover');
				  	$(this).nextAll('.star-rating').addClass('out');
				}, function() {
					$(this).removeClass('hover');
					$(this).siblings('.star-rating').removeClass('hover out');
				}
			);
			$('.star-rating-input').change(
				function() {
					$(this).parent().addClass('checked');
					$(this).parent().prevAll('.star-rating').addClass('checked');
					$(this).parent().nextAll('.star-rating').removeClass('checked');
				}
			);
		}
		if ($('.feedback-entry-cancel').length) {
			$('.feedback-entry-cancel').prop("onclick", null).off("click");
			$('.feedback-entry-cancel').click(function (event) {
				event.preventDefault();
				$(this).closest('form')[0].reset();
				$(this).closest('form').find('.star-rating').removeClass('checked');
			});
		}
	}
}

STUDIP.ready(function (event) {
	STUDIP.Feedback.initiateFeedbackEntryForm();
    if ($('div.feedback-elements').length) {
		$('div.feedback-elements', event.target).each((index, element) => {
			STUDIP.Feedback.initiate(element);
		});
	}
});
