module("URLHelper#resolveURL", {
    setup: function () {
        this.original_uri = STUDIP.URLHelper.base_url;
        this.setStudipUri("");
    },
    teardown: function () {
        this.setStudipUri(this.original_uri);
    },

    setStudipUri: function (url) {
        STUDIP.URLHelper.base_url = url;
    }
});


test("base_url is undefined", function () {
    this.setStudipUri(undefined);
    var url1 = "identity",
        url2 = "/identity";
    equals(STUDIP.URLHelper.resolveURL(url1), url1);
    equals(STUDIP.URLHelper.resolveURL(url2), url2);
});

test("base_url is empty string", function () {
    this.setStudipUri("");
    var url1 = "identity",
        url2 = "/identity";
    equals(STUDIP.URLHelper.resolveURL(url1), url1);
    equals(STUDIP.URLHelper.resolveURL(url2), url2);
});

test("base_url is well-formed url", function () {
    var host = "proto://host",
        base = host + "/path/",
        url1 = "identity",
        url2 = "/identity";
    this.setStudipUri(base);
    equals(STUDIP.URLHelper.resolveURL(url1), base + url1);
    equals(STUDIP.URLHelper.resolveURL(url2), host + url2);
});

test("base_url is strange but feasible url", function () {
    var host = "S-t.r+ang3://host",
        base = host + "/path/",
        url1 = "identity",
        url2 = "/identity";
    this.setStudipUri(base);
    equals(STUDIP.URLHelper.resolveURL(url1), base + url1);
    equals(STUDIP.URLHelper.resolveURL(url2), host + url2);
});

test("URL containing only the query part", function () {
    var url = "?identity=";
    this.setStudipUri("http://host/path");
    equals(STUDIP.URLHelper.resolveURL(url), url);
});


test("with host + server relative url", function () {
    var host = "http://host",
    studip = host + "/path/",
    url = "/url";
    this.setStudipUri(studip);
    equals(STUDIP.URLHelper.resolveURL(url), host + url);
});

test("with host + relative url", function () {
    var host = "http://host",
    studip = host + "/path/",
    url = "url";
    this.setStudipUri(studip);
    equals(STUDIP.URLHelper.resolveURL(url), studip + url);
});

test("w/o host + server relative url", function () {
    var host = "",
    studip = host + "/path/",
    url = "/url";
    this.setStudipUri(studip);
    equals(STUDIP.URLHelper.resolveURL(url), url);
});

test("w/o host + relative url", function () {
    var host = "",
    studip = host + "/path/",
    url = "url";
    this.setStudipUri(studip);
    equals(STUDIP.URLHelper.resolveURL(url), studip + url);
});

///////////////////////////////////////////////////////////////////////////////

module("URLHelper#getURL", {
    setup: function () {
        this.original_uri = STUDIP.base_url;
        this.setStudipUri("");
        STUDIP.URLHelper.base_url = null;
    },
    teardown: function () {
        this.setStudipUri(this.original_uri);
    },
    setStudipUri: function (url) {
        STUDIP.base_url = url;
    }
});

test("no link param", function () {

    equals(STUDIP.URLHelper.getURL(''), '?');
    equals(STUDIP.URLHelper.getURL('x'), 'x');
    equals(STUDIP.URLHelper.getURL('#x'), '?#x');

    STUDIP.URLHelper.base_url = '/dir/';

    equals(STUDIP.URLHelper.getURL(''), '?');
    equals(STUDIP.URLHelper.getURL('#x'), '?#x');
    equals(STUDIP.URLHelper.getURL('?a=b'), '?a=b');

    equals(STUDIP.URLHelper.getURL('', {a:1,b:2}), '?a=1&b=2');
});

test("w/ anchor and params", function () {
    var url, params, expected;
    url = 'abc#top';
    params = {a: 'b', c: 'd'};
    equals(STUDIP.URLHelper.getURL(url, params), 'abc?a=b&c=d#top');
});

test("w/ implicit params", function () {
    var url, expected;
    url = 'abc?foo=test';
    expected = 'abc?foo=test';
    equals(STUDIP.URLHelper.getURL(url), expected);
});

test("w/ explicit params", function () {
    var url, params, expected;
    url = 'abc';
    params = {foo: 'test'};
    expected = 'abc?foo=test';
    equals(STUDIP.URLHelper.getURL(url, params), expected);
});

test("w/ conflicting implicit and explicit params", function () {
    var url, params, expected;
    url = 'abc?baz=on';
    params = {baz: 'off'};
    expected = 'abc?baz=off';
    equals(STUDIP.URLHelper.getURL(url, params), expected);
});
