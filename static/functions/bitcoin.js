/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


function CheckAddressLoadNext(id, eur_rate, numconfirms, maxid, state) {
    $('#btc_balance_'+id).raw().innerHTML="fetching....";
    var address =  $('#address_'+id).raw().innerHTML;
	ajax.get("ajax.php?action=check_donation&address="+address+"&numt="+numconfirms, function(response){
        var euros = response*eur_rate;
        // $('#btc_button_'+id).raw().innerHTML= "<a href=\"#\" onclick=\"CheckAddress("+id+","+eur_rate+",'"+address+"','6');return false;\"><img src=\"/static/common/symbols/reload1.gif\" title=\"query btc balance\" alt=\"query\" /></a> &nbsp;" + parseFloat(response)  ;
        $('#btc_balance_'+id).raw().innerHTML= parseFloat(response);
                           
        $('#euros_'+id).raw().innerHTML=" &euro;"+euros.toFixed(2) ;
        if(state=='1' && $('#status_'+id).raw().innerHTML=='submitted' && parseFloat(response)==0){
            $('#state_button_'+id).raw().innerHTML= '<input type="button" onclick="ChangeState('+id+',\''+address+'\',\'cleared\')" value="change state to cleared" />' ;
        }
        var next = parseInt(id) + 1;
        if (next<=maxid) {
            setTimeout("CheckAddressLoadNext("+next+","+eur_rate+",'6',"+maxid+","+state+")", 800 );
        }
	});
}
 


function CheckAddress(id, eur_rate, address, numconfirms, state) {
    $('#btc_balance_'+id).raw().innerHTML="fetching....";
	ajax.get("ajax.php?action=check_donation&address="+address+"&numt="+numconfirms, function(response){
        var euros = response*eur_rate;
        //$('#btc_button_'+id).raw().innerHTML= "<a href=\"#\" onclick=\"CheckAddress("+id+","+eur_rate+",'"+address+"','6',"+state+");return false;\"><img src=\"/static/common/symbols/reload1.gif\" title=\"query btc balance\" alt=\"query\" /></a> &nbsp;" + parseFloat(response)  ;
        $('#btc_balance_'+id).raw().innerHTML= parseFloat(response);
        $('#euros_'+id).raw().innerHTML=" &euro;"+euros.toFixed(2)+"";
        if(state=='1' && $('#status_'+id).raw().innerHTML=='submitted' && parseFloat(response)==0){
            $('#state_button_'+id).raw().innerHTML= '<input type="button" onclick="ChangeState('+id+',\''+address+'\',\'cleared\')" value="change state to cleared" />' ;
        }
	});
}


function ChangeState(id, address, newstate) {
    if (!in_array(newstate, new Array( 'unused','submitted','cleared'))) newstate='unused';
	ajax.get("ajax.php?action=change_donation&address="+address+"&state="+newstate, function(response){
        if(response==1){ 
            $('#status_'+id).raw().innerHTML= newstate;
            $('#state_button_'+id).raw().innerHTML= "";
        } else { // error
            $('#state_button_'+id).raw().innerHTML= response;
        }
	});
}


function ChangeStateToClearedLoadNext(id, maxid) {
    //if (!in_array(newstate, new Array( 'unused','submitted','cleared'))) newstate='unused';
    if ( $('#state_button_'+id).raw().innerHTML == "" ) return;
     
    var address =  $('#address_'+id).raw().innerHTML;
	ajax.get("ajax.php?action=change_donation&address="+address+"&state=cleared", function(response){
        if(response==1){ 
            $('#status_'+id).raw().innerHTML= 'cleared';
            $('#state_button_'+id).raw().innerHTML= "";
        } else { // error
            $('#state_button_'+id).raw().innerHTML= response;
        }
	});
}


function ChangeStatesToCleared(maxid) {
    //if (!in_array(newstate, new Array( 'unused','submitted','cleared'))) newstate='unused';
       
    
	ajax.get("ajax.php?action=change_donation&address="+address+"&state="+newstate, function(response){
        if(response==1){ 
            $('#status_'+id).raw().innerHTML= newstate;
            $('#state_button_'+id).raw().innerHTML= "";
        } else { // error
            $('#state_button_'+id).raw().innerHTML= response;
        }
	});
}
