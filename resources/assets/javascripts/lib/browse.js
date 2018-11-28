const Browse = {
    selectUser: function(username) {
        window.location.href = STUDIP.URLHelper.getURL('dispatch.php/profile', { username: username });
    }
};

export default Browse;
