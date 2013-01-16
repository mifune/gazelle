
function prompt_before_multiban() {
    var banspeed =  $('#banspeed').raw().options[$('#banspeed').raw().selectedIndex].value;
    return confirm('Are you sure you want to ban and disable all users with a recorded max speed over '+get_size(banspeed)+'/s ?');
}

function change_view_reports(userid, torrentid){
    var selSpeed=$('#viewspeed').raw().options[$('#viewspeed').raw().selectedIndex].value;
    location.href = "tools.php?action=speed_records&viewspeed="+selSpeed+"&userid="+userid+"&torrentid="+torrentid+($('#viewbanned').raw().checked?'&viewbanned=1':'');
}
        
function change_view(){
    var viewspeed=$('#viewspeed').raw().options[$('#viewspeed').raw().selectedIndex].value;
    var banspeed =  $('#banspeed').raw().options[$('#banspeed').raw().selectedIndex].value;
    location.href = "tools.php?action=speed_cheats&viewspeed="+viewspeed+"&banspeed="+banspeed+($('#viewbanned').raw().checked?'&viewbanned=1':'');
}

function preview_users() {
    var speed =  $('#banspeed').raw().options[$('#banspeed').raw().selectedIndex].value;
    window.location = location.protocol + '//' + location.host + 
         "//tools.php?action=speed_cheats&banspeed="+ speed + "&viewspeed="+speed;
}

function remove_records(user_id) {
	ajax.get('ajax.php?action=remove_records&userid=' + user_id, function (response) {
        var x = json.decode(response); 
        if ( is_array(x)){
            alert(x[1]);
            if ( x[0] == true){
               location.reload();
            }
        } else {    // error from ajax
            alert(x);
        } 
	}); 
}




function excludelist_add(user_id, reload) {
    var comm = prompt('Enter a comment for adding this user to the exclude list');
    if (!comm) return;
	ajax.get('ajax.php?action=excludelist_add&userid=' + user_id + '&comm=' + comm, function (response) {
        var x = json.decode(response); 
        if ( is_array(x)){
            if ( x[0] == true){
               $('#xcl').html("[<a onclick=\"excludelist_remove('"+ user_id +"')\" href=\"#\">Remove from exclude list</a>]");
            }
            alert(x[1]);
            if (x[0] == true && reload) location.reload();
        } else {    // error from ajax
            alert(x);
        } 
	}); 
}

function excludelist_remove(user_id, reload) {
	ajax.get('ajax.php?action=excludelist_remove&userid=' + user_id, function (response) {
        var x = json.decode(response); 
        if ( is_array(x)){
            if ( x[0] == true){
               $('#xcl').html("[<a onclick=\"excludelist_add('"+ user_id +"')\" href=\"#\">Add to exclude list</a>]");
            }
            alert(x[1]);
            if (x[0] == true && reload) location.reload();
        } else {    // error from ajax
            alert(x);
        } 
	}); 
}




function watchlist_add(user_id, reload) {
    var comm = prompt('Enter a comment for adding this user to the watchlist');
    if (!comm) return;
	ajax.get('ajax.php?action=watchlist_add&userid=' + user_id + '&comm=' + comm, function (response) {
        var x = json.decode(response); 
        if ( is_array(x)){
            if ( x[0] == true){
               $('#wl').html("[<a onclick=\"watchlist_remove('"+ user_id +"')\" href=\"#\">Remove from watchlist</a>]");
            }
            alert(x[1]);
            if (x[0] == true && reload) location.reload();
        } else {    // error from ajax
            alert(x);
        } 
	}); 
}

function watchlist_remove(user_id, reload) {
	ajax.get('ajax.php?action=watchlist_remove&userid=' + user_id, function (response) {
        var x = json.decode(response); 
        if ( is_array(x)){
            if ( x[0] == true){
               $('#wl').html("[<a onclick=\"watchlist_add('"+ user_id +"')\" href=\"#\">Add to watchlist</a>]");
            }
            alert(x[1]);
            if (x[0] == true && reload) location.reload();
        } else {    // error from ajax
            alert(x);
        } 
	}); 
}



function twatchlist_add(group_id, torrent_id, reload) {
    var comm = prompt('Enter a comment for adding this torrent to the watchlist');
	ajax.get('ajax.php?action=watchlist_add&groupid=' + group_id + '&torrentid=' + torrent_id + '&comm=' + comm, function (response) {
        var x = json.decode(response); 
        if ( is_array(x)){
            if ( x[0] == true){
               $('#wl').html("[<a onclick=\"twatchlist_remove('"+ group_id +"','"+ torrent_id +"')\" href=\"#\">Remove from watchlist</a>]");
            }
            alert(x[1]);
            if (x[0] == true && reload) location.reload();
        } else {    // error from ajax
            alert(x);
        } 
	}); 
}

function twatchlist_remove(group_id, torrent_id) {
	ajax.get('ajax.php?action=watchlist_remove&groupid=' + group_id + '&torrentid=' + torrent_id, function (response) {
        var x = json.decode(response); 
        if ( is_array(x)){
            if ( x[0] == true){
               $('#wl').html("[<a onclick=\"twatchlist_add('"+ group_id +"','"+ torrent_id +"')\" href=\"#\">Add to watchlist</a>]");
            }
            alert(x[1]);
        } else {    // error from ajax
            alert(x);
        } 
	}); 
}


