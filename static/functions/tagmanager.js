
var started = new Array(0);
var multitaglist = new Array(0);

function Select_Tag(char_search, tagID, tagname) {
    if (!in_array(char_search, started)) return;
    if(tagID==0) return;
    if(in_array(tagID, multitaglist)) return;
    
    multitaglist.push(tagID);
    //alert("Tag: "+tagID +" '"+tagname+"'");
    var div = ($('#multiID').raw().value !='')?',':'';
    $('#multiID').raw().value += div+tagID;
    $('#showmultiID').raw().value += div+tagID;
    $('#multiNames').raw().innerHTML += div+tagname;
    
    //$('#movetagid').raw().selectedIndex = 0;
}


function Clear_Multi() {
    $('#multiID').raw().value = '';
    $('#showmultiID').raw().value = '';
    $('#multiNames').raw().innerHTML = '';
    multitaglist = new Array(0);
}


function Get_Taglist(select_id, char_search) {
    if (in_array(char_search, started)) return;
    //alert('fetching list for ' + char_search + '...');
    started.push(char_search);
 
    ajax.get('ajax.php?action=get_taglist&char='+char_search, function(response) {
        //alert('fetched tags beginning with '+ char_search);
        var x = json.decode(response);
        if ( is_array(x)){
            $('#'+select_id).raw().innerHTML = x[0];
        } else {
            alert(x);
        }
    });
}

