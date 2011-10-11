/*!
 * Copyright (c) 2009 Andreas Blixt <andreas@blixt.org>
 * Contributors: Aaron Ogle <aogle@avencia.com>,
 *               Matti Virkkunen <mvirkkunen@gmail.com>
 * This and more JavaScript libraries: http://blixt.org/js
 * MIT License: http://www.opensource.org/licenses/mit-license.php
 * 
 * Hash handler
 * Keeps track of the history of changes to the hash part in the address bar.
 */
/* WARNING for Internet Explorer 7 and below:
 * If an element on the page has the same ID as the hash used, the history will
 * get messed up.
 *
 * Does not support history in Safari 2 and below.
 * 
 * Example:
 *     function handler(newHash, initial) {
 *         if (initial)
 *             alert('Hash is "' + newHash + '"');
 *         else
 *             alert('Hash changed to "' + newHash + '"');
 *     }
 *     Hash.init(handler, document.getElementById('hidden-iframe'));
 *     Hash.go('abc123');
 * 
 * Updated by Matti Virkkunen (mvirkkunen@gmail.com) on 2009-11-16:
 *   - Added second argument to callback that indicated whether the callback is due
 *     to initial state (true) or due to an actual change to the hash (false).
 * 
 * Updated by Aaron Ogle (aogle@avencia.com) on 2009-08-11:
 *   - Fixed bug where Firefox automatically unescapes location.hash but no other
 *     browsers do. Always get the hash by parsing location.href and never use
 *     location.hash.
 */

var Hash = (function () {
var
// Import globals
window = this,
documentMode = document.documentMode,
history = window.history,
location = window.location,
// Plugin variables
callback, hash,
// IE-specific
iframe,

getHash = function () {
    // Internet Explorer 6 (and possibly other browsers) extracts the query
    // string out of the location.hash property into the location.search
    // property, so we can't rely on it. The location.search property can't be
    // relied on either, since if the URL contains a real query string, that's
    // what it will be set to. The only way to get the whole hash is to parse
    // it from the location.href property.
    //
    // Another thing to note is that in Internet Explorer 6 and 7 (and possibly
    // other browsers), subsequent hashes are removed from the location.href
    // (and location.hash) property if the location.search property is set.
    //
    // Via Aaron: Firefox 3.5 (and below?) always unescape location.hash which
    // causes poll to fire the hashchange event twice on escaped hashes. This is
    // because the hash variable (escaped) will not match location.hash
    // (unescaped.) The only consistent option is to rely completely on
    // location.href.
    var index = location.href.indexOf('#');
    return (index == -1 ? '' : location.href.substr(index + 1));
},

// Used by all browsers except Internet Explorer 7 and below.
poll = function () {
    var curHash = getHash();
    if (curHash != hash) {
        hash = curHash;
        callback(curHash, false);
    }
},

// Used to create a history entry with a value in the iframe.
setIframe = function (newHash) {
    try {
        var doc = iframe.contentWindow.document;
        doc.open();
        doc.write('<html><body>' + newHash + '</body></html>');
        doc.close();
        hash = newHash;
    } catch (e) {
        setTimeout(function () { setIframe(newHash); }, 10);
    }
},

// Used by Internet Explorer 7 and below to set up an iframe that keeps track
// of history changes.
setUpIframe = function () {
    // Don't run until access to the iframe is allowed.
    try {
        iframe.contentWindow.document;
    } catch (e) {
        setTimeout(setUpIframe, 10);
        return;
    }

    // Create a history entry for the initial state.
    setIframe(hash);
    var data = hash;

    setInterval(function () {
        var curData, curHash;

        try {
            curData = iframe.contentWindow.document.body.innerText;
            if (curData != data) {
                data = curData;
                location.hash = hash = curData;
                callback(curData, true);
            } else {
                curHash = getHash();
                if (curHash != hash) setIframe(curHash);
            }
        } catch (e) {
        }
    }, 50);
};

return {
    init: function (cb, ifr) {
        // init can only be called once.
        if (callback) return;

        callback = cb;

        // Keep track of the hash value.
        hash = getHash();
        cb(hash, true);

        // Run specific code for Internet Explorer.
        if (window.ActiveXObject) {
            if (!documentMode || documentMode < 8) {
                // Internet Explorer 5.5/6/7 need an iframe for history
                // support.
                iframe = ifr;
                setUpIframe();
            } else  {
                // Internet Explorer 8 has onhashchange event.
                window.attachEvent('onhashchange', poll);
            }
        } else {
            // Change Opera navigation mode to improve history support.
            if (history.navigationMode) history.navigationMode = 'compatible';

            setInterval(poll, 50);
        }
    },

    go: function (newHash) {
        // Cancel if the new hash is the same as the current one, since there
        // is no cross-browser way to keep track of navigation to the exact
        // same hash multiple times in a row. A wrapper can handle this by
        // adding an incrementing counter to the end of the hash.
        if (newHash == hash) return;
        if (iframe) {
            setIframe(newHash);
        } else {
            location.hash = hash = newHash;
            callback(newHash, false);
        }
    }
};
})();
/*!
 * Copyright (c) 2009 Andreas Blixt <andreas@blixt.org>
 * This and more JavaScript libraries: http://blixt.org/js
 * MIT License: http://www.opensource.org/licenses/mit-license.php
 * 
 * jQuery hash plugin (Depends on jQuery, Hash)
 * Plugin for detecting changes to the hash and for adding history support for
 * hashes to certain browsers.
 */
/* A blank HTML page (blank.html) is needed for Internet Explorer 7 and below
 * support.
 *
 * Example:
 *     // Add events before calling init to make sure they are triggered for
 *     // initial hash value.
 *     $('div#log').hashchange(function (e, newHash) {
 *         $(this).prepend('<p>New hash: <b>"' + newHash + '"</b></p>');
 *     });
 *     // Initialize. Here, the src of the iframe is passed in the init
 *     // function. If you've got multiple libraries using this plugin, so
 *     // the init function is called from them, you can also set the iframe
 *     // src in the iframeSrc variable in the beginning of the code in
 *     // jquery.hash.js.
 *     $.hash.init('blank.html');
 *     $.hash.go('abc123');
 *     // Changes hash when the anchor is clicked. Also automatically sets the
 *     // href attribute to "#def456", unless a second argument with a false
 *     // value is supplied.
 *     $('a#my-anchor').hash('def456');
 * 
 * WARNING for Internet Explorer 7 and below:
 * If an element on the page has the same ID as the hash used, the history will
 * get messed up.
 *
 * Does not support history in Safari 2 and below.
 */

(function (jQuery, Hash) {
var
// Plugin settings
iframeId = 'jquery-history',
iframeSrc = '/js/blank.html',
eventName = 'hashchange',
eventDataName = 'hash.fn',
init,
// Import globals
window = this,
documentMode = document.documentMode,

// Called whenever the hash changes.
callback = function (newHash) {
    jQuery.event.trigger(eventName, [newHash]);
};

jQuery.hash = {
    init: function (src) {
        // init can only be called once.
        if (init) return;
        init = 1;
        
        var iframe;
        if (window.ActiveXObject && (!documentMode || documentMode < 8)) {
            // Create an iframe for Internet Explorer 7 and below.
            jQuery('body').prepend(
                '<iframe id="' + iframeId + '" style="display:none;" ' +
                'src="' + (src || iframeSrc) + '"></iframe>');
            iframe = jQuery('#' + iframeId)[0];
        }
        
        Hash.init(callback, iframe);
    },

    go: Hash.go
};

jQuery.fn.hash = function (newHash, changeHref) {
    var fn = this.data(eventDataName);
    if (fn) this.unbind('click', fn);

    if (typeof newHash == 'string') {
        fn = function () { Hash.go(newHash); return false; };
        this.data(eventDataName, fn);
        this.click(fn);
        if (changeHref || changeHref === undefined)
            this.attr('href', '#' + newHash);
    }
    
    return this;
};

jQuery.fn[eventName] = function (fn) {
    return this.bind(eventName, fn);
};
})(jQuery, Hash);