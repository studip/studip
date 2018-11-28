const Archive = {
    removeArchivedCourses: function(courseIds) {
        /*
         * Removes courses that are archived from the course list
         * seen in the admin/courses controller.
         */

        for (var i = 0; i < courseIds.length; i++) {
            courseIds[i] = '#course-' + courseIds[i];
            jQuery(courseIds[i]).remove();
        }
    }
};

export default Archive;
