// Navigation: Load any url in this very same dialog
$(document).on('click', '.smiley-picker .navigation a', STUDIP.SmileyPicker.handleNavigationClick);

// Smiley:
// Execute select handler with selected smiley's code
// (typically adds the code to a certain textarea)
$(document).on('click', '.smiley-picker .smiley', STUDIP.SmileyPicker.handleSmileyClick);
