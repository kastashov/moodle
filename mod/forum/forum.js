var timefromitems = ['fromday','frommonth','fromyear','fromhour', 'fromminute'];
var timetoitems = ['today','tomonth','toyear','tohour','tominute'];

function forum_produce_subscribe_link(forumid, backtoindex, ltext, ltitle) {
    var elementid = "subscriptionlink";
    var subs_link = document.getElementById(elementid);
    if(subs_link){
        subs_link.innerHTML = "<a title='"+ltitle+"' href='"+M.cfg.wwwroot+"/mod/forum/subscribe.php?id="+forumid+backtoindex+"&amp;sesskey="+M.cfg.sesskey+"'>"+ltext+"<\/a>";
    }
}

function forum_produce_tracking_link(forumid, ltext, ltitle) {
    var elementid = "trackinglink";
    var subs_link = document.getElementById(elementid);
    if(subs_link){
        subs_link.innerHTML = "<a title='"+ltitle+"' href='"+M.cfg.wwwroot+"/mod/forum/settracking.php?id="+forumid+"'>"+ltext+"<\/a>";
    }
}

function lockoptions_timetoitems() {
    lockoptions('searchform','timefromrestrict', timefromitems);
}

function lockoptions_timefromitems() {
    lockoptions('searchform','timetorestrict', timetoitems);
}

function lockoptions(formid, master, subitems) {
    // Subitems is an array of names of sub items.
    // Optionally, each item in subitems may have a
    // companion hidden item in the form with the
    // same name but prefixed by "h".
    var form = document.forms[formid], i;
    if (form[master].checked) {
        for (i=0; i<subitems.length; i++) {
            unlockoption(form, subitems[i]);
        }
    } else {
        for (i=0; i<subitems.length; i++) {
            lockoption(form, subitems[i]);
        }
    }
    return(true);
}


function lockoption(form,item) {
    form[item].setAttribute('disabled', 'disabled');
    if (form.elements['h'+item]) {
        form.elements['h'+item].value=1;
    }
}

function unlockoption(form,item) {
    form[item].removeAttribute('disabled');
    if (form.elements['h'+item]) {
        form.elements['h'+item].value=0;
    }
}


//YUI AJAX thread load and close

var divposts = [];
var divdiscussionthread = [];
var openedthread = 0;

function load_thread(parent) {
    var load_thread_url = M.cfg.wwwroot+'/mod/forum/ajax_get_post_content.php?id='+parent;
    if (openedthread > 0) {
        close_thread(openedthread);
    }
    divslide('divdiscussionthread',parent,'up');
    document.getElementById('divpostsloading'+parent).innerHTML = 'Loading...';
    document.getElementById('divpostsloading'+parent).style.display = 'block';
    document.getElementById('divpostsloading'+parent).style.visibility = 'visible';
    var transaction = YAHOO.util.Connect.asyncRequest('GET', load_thread_url, {
        success: function(o) {
                document.getElementById('divpostsloading'+parent).innerHTML = '';
                document.getElementById('divpostsloading'+parent).style.display = 'none';
                document.getElementById('divpostsloading'+parent).style.visibility = 'hidden';
                document.getElementById('divposts'+parent).innerHTML = o.responseText;
                document.getElementById('divdiscussionthreadclose'+parent).style.display = 'block';
                document.getElementById('divdiscussionthreadclose'+parent).style.visibility = 'visible';
                divslide('divposts',parent,'down');
                if (document.getElementById('divposts'+parent).style.visibility == 'hidden'){
                    document.getElementById('divposts'+parent).style.visibility = 'visible';
                }
        },
        failure: function(o) {
            document.getElementById('divpostsloading'+parent).innerHTML = '';
            document.getElementById('divpostsloading'+parent).style.display = 'none';
            document.getElementById('divpostsloading'+parent).style.visibility = 'hidden';
            alert("There was a problem connecting to the server. Try viewing this forum in Nested view.");
            divslide('divdiscussionthread',parent,'down');
        }
    }, null);
}

function close_thread(parent) {
   divslide('divposts',parent,'up');
   divslide('divdiscussionthread',parent,'down');
   document.getElementById('divdiscussionthreadclose'+parent).style.display = 'none';
   document.getElementById('divdiscussionthreadclose'+parent).style.visibility = 'hidden';
   openedthread = 0;
   if (!parseInt(divdiscussionthread[parent])>0){ // cap for IE7, it will not animate anyway, just hide it
       document.getElementById('divposts'+parent).style.visibility = 'hidden';
   }
}

function divslide(divname,divnum, mode) {
    if (mode=='down') {
        var animnode = '#'+divname+'box'+divnum;
    } else {
        var animnode = '#'+divname+'box'+divnum;
    }
    YUI().use('node', 'event', 'anim', function(Y) {
        var heightAnim = new Y.Anim({
          node: animnode,
          to: {
            height: function(node) {
                if (divname=='divposts') {
                    if (typeof(divposts[divnum]) === 'undefined') {
                        var inner = YAHOO.util.Dom.get(divname+divnum);
                        divposts[divnum] = parseInt(YAHOO.util.Dom.getStyle(inner,'height'));
                    } 
                } else {
                    if (typeof(divdiscussionthread[divnum]) === 'undefined') {
                        var inner = YAHOO.util.Dom.get(divname+divnum);
                        divdiscussionthread[divnum] = parseInt(YAHOO.util.Dom.getStyle(inner,'height'));
                    }
                }

                if (mode=='down') {
                    if (divname=='divposts') {
                        if (parseInt(divposts[divnum])>0) { // IE7 does not return valid height after assigning new innerHTML :(
                            retval = divposts[divnum];
                        } else {
                            retval = 200;
                        }
                    } else {
                        if (parseInt(divdiscussionthread[divnum])>0) {
                            retval = divdiscussionthread[divnum];
                        } else {
                            retval = 200;
                        }
                    }
                } else {
                    retval = 0;
                }
                return retval;
            }
          },
          duration: 0.6,
          easing: Y.Easing.backIn
        });

        heightAnim.on('end', function () {
            if (mode=='down') {
                document.getElementById(divname+'box'+divnum).style.height = 'auto';
            } else {
                document.getElementById(divname+'box'+divnum).style.height='0';
            }
            openedthread = divnum;
        });
        
      heightAnim.run();

    });
}
