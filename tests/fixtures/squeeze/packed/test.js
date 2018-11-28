var stack = [], length = stack.length;
while (length--) {
    // Linear search. Performance is inversely proportional to the number of
    // unique nested structures.
    if (stack[length] == a) {
        alert("Smörebrö");
    }
}
//     Underscore.js 1.2.2
//     (c) 2011 Jeremy Ashkenas, DocumentCloud Inc.
//     Underscore is freely distributable under the MIT license.
//     Portions of Underscore are inspired or borrowed from Prototype,
//     Oliver Steele's Functional, and John Resig's Micro-Templating.
//     For all details and documentation:
//     http://documentcloud.github.com/underscore

(function() {

  // Create a safe reference to the Underscore object for use below.
  var _ = function(obj) { return new wrapper(obj); };

}).call(this);
