$( function() {
    var dialog, form,

      emailRegex = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/,
   name = $( "#name" ),
   email = $( "#email" ),
   password = $( "#password" ),
   allFields = $( [] ).add( name ).add( email ).add( password );

 function updateTips(t) {
   alert(t);
 }

 function checkLength( o, n, min, max,t2 ) {
   if ( o.val().length > max || o.val().length < min ) {
     o.addClass( "ui-state-error" );
     updateTips( "Length of " + n + " must be between " +
       min + " and " + max + ".");
     return false;
   } else {
     return true;
   }
 }

 function checkRegexp( o, regexp, n) {
   if ( !( regexp.test( o.val() ) ) ) {
     o.addClass( "ui-state-error" );
     updateTips( n );
     return false;
   } else {
     return true;
   }
 }

 function addUser() {
   var valid = true;
   allFields.removeClass( "ui-state-error" );

   valid = valid && checkLength( name, "username", 3, 16 );
   valid = valid && checkLength( email, "email", 6, 80 );
   valid = valid && checkLength( password, "password", 5, 16 );

   valid = valid && checkRegexp( name, /^[a-z]([0-9a-z_\s])+$/i, "Username may consist of a-z, 0-9, underscores, spaces and must begin with a letter.");
   valid = valid && checkRegexp( email, emailRegex, "eg. info@planet91.com");
   valid = valid && checkRegexp( password, /^([0-9a-zA-Z])+$/, "Password field only allow : a-z 0-9" );

   if ( valid ) {
    $.ajax({
      method: "POST",
      url: "ajaxpost.php",
      data:{"uname":name.val(),"upass":password.val(),"umail":email.val(),"pro":"register"},     
      success: function (response) {
        dialog.dialog("close");
        alert (response);
      }

    });   
    
   }
   return valid;
 }

 dialog = $( "#dialog-form" ).dialog({
   autoOpen: false,
   height: 300,
   width: 300,
   modal: true,
   buttons: {
     "Create an account": addUser,
     Cancel: function() {
       dialog.dialog( "close" );
     }
   },
   close: function() {
     form[ 0 ].reset();
     allFields.removeClass( "ui-state-error" );
   }
 });

 form = dialog.find( "form" ).on( "submit", function( event ) {
   event.preventDefault();
   addUser();
 });

 $( "#create-user" ).button().on( "click", function() {
   dialog.dialog( "open" );
 });

/*  $("#Loginform").on("submit",function(event){
   $( "#Lbutton" ).prop( "disabled", true );
   event.preventDefault();
   var valid = true;
   $( [] ).add( "#Lusername" ).add( "#Lpass" ).removeClass( "ui-state-error" );

   valid = valid && checkLength( $("#Lusername"), "username", 3, 16);
   valid = valid && checkLength( $("#Lpass"), "password", 5, 16);

   valid = valid && checkRegexp( $("#Lusername"), /^[a-z]([0-9a-z_\s])+$/i, "Username may consist of a-z, 0-9, underscores, spaces and must begin with a letter.");
   valid = valid && checkRegexp( $("#Lpass"), /^([0-9a-zA-Z])+$/, "Password field only allow : a-z 0-9");

   if ( valid ) {
    $( "#Lbutton" ).prop( "disabled", false);
   }
   return valid;

 }); */
} );