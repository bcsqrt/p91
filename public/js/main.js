$(document).ready(function(){
    $("#Btmain").click(function(e){
        e.preventDefault();
        $co=$(this).attr("colony");
        window.location.href="index.php?p=main&co="+$co;
    });
    $("#btnmessage").click(function(e){
        e.preventDefault();
        $co=$(this).attr("colony");
        window.location.href="index.php?p=Message&co="+$co;
    });
    $("#Btmanagement").click(function(e){
        e.preventDefault();
        $co=$(this).attr("colony");
        window.location.href="index.php?p=tmanagement&co="+$co;
    });
    $("#Btunits").click(function(e){
        e.preventDefault();
        $co=$(this).attr("colony");
        window.location.href="index.php?p=units&co="+$co;
    });
    $("#Btmilitaryunits").click(function(e){
        e.preventDefault();
        $co=$(this).attr("colony");
        window.location.href="index.php?p=militaryunits&co="+$co;
    });
    $("#Btdefansiveunits").click(function(e){
        e.preventDefault();
        $co=$(this).attr("colony");
        window.location.href="index.php?p=defansiveunits&co="+$co;
    });
    $("#Btcivilunits").click(function(e){
        e.preventDefault();
        $co=$(this).attr("colony");
        window.location.href="index.php?p=civilunits&co="+$co;
    });
    $("#Btmap").click(function(e){
        e.preventDefault();
        $co=$(this).attr("colony");
        window.location.href="index.php?p=map&co="+$co;
    });
    $("#Btcolony").click(function(e){
        e.preventDefault();
        $co=$(this).attr("colony");
        window.location.href="index.php?p=colony&co="+$co;
    });
    $("#Btbarrack").click(function(e){
        e.preventDefault();
        $co=$(this).attr("colony");
        window.location.href="index.php?p=Barrack&co="+$co;
    });
    $("#Btchurch").click(function(e){
        e.preventDefault();
        $co=$(this).attr("colony");
        window.location.href="index.php?p=Church&co="+$co;
    });
    $("#Btsoul").click(function(e){
        e.preventDefault();
        $co=$(this).attr("colony");
        window.location.href="index.php?p=soul&co="+$co;
    });
    $("#btnlogout").click(function(e){
        e.preventDefault();
        window.location.href="login.php?p=logout";
    });

    $("#maphome").click(function(){
        $lat=$(this).attr("lat");
        $long=$(this).attr("long");
        $co=$("#mapform").attr("colony");
        window.location.href="index.php?p=map&lat="+$lat+"&long="+$long+"&co="+$co;
    });

    $("#mapup").click(function(){
        $lat=parseInt($("#maplat").val())-1;
        if ($lat<1) {
            $lat=1;
        }
        $("#maplat").val($lat);
        $("#mapform").submit();
    });

    $("#mapdown").click(function(){
        $lat=parseInt($("#maplat").val())+1;
        if ($lat>100) {
            $lat=100;
        }
        $("#maplat").val($lat);
        $("#mapform").submit();
    });

    $("#mapright").click(function(){
        $lat=parseInt($("#maplong").val())+1;
        if ($lat>100) {
            $lat=100;
        }
        $("#maplong").val($lat);
        $("#mapform").submit();
    });

    $("#mapleft").click(function(){
        $lat=parseInt($("#maplong").val())-1;
        if ($lat<1) {
            $lat=1;
        }
        $("#maplong").val($lat);
        $("#mapform").submit();
    });

    $("#colonyselect").on("change",function(){
        $co=$("#colonyselect").val();
        $page=$.trim($("#pagesec").val());
        window.location.href="index.php?p="+$page+"&co="+$co;
    });

    $("#user-dialog-panel").dialog({
        autoOpen:false,
    });

    $("#btnprofile").on("click",function(){
        $("#user-dialog-panel").dialog({
            title:"Profile",
        });
        $("#user-dialog-panel").load("ajaxdialog.php?p=Profile").dialog("open");
    });
    $("#btnsettings").on("click",function(){
        $("#user-dialog-panel").dialog({
            title:"Settings",
        });
        $("#user-dialog-panel").load("ajaxdialog.php?p=Settings").dialog("open");
    });

    $("#mapdialog").dialog({
        autoOpen: false,
        height: "auto",
        width: 250,
        modal:true,
        buttons: {
            "Mission": function() {
              $( this ).dialog( "close" );
              $lat=$("#mapbtaction").attr("lat");
              $long=$("#mapbtaction").attr("long");
              $cid=$("#mapform").attr("colony");
              window.location.href="index.php?p=tmanagement&lat="+$lat+"&long="+$long+"&co="+$cid;
            }
        }
    });

    $(".maptarget").on("click",function(){
        $Title=$(this).attr("title");
        $Area=$(this).attr("area");
        $("#mapdialog").dialog("option", "title", $Title+"-"+$Area);
        $("#mapdialog").dialog("open");
        $id=$(this).attr("id");
        $.ajax({
            type: "POST",
            url: "ajaxpost.php?p=MapDialog",
            data: {"id":$id,"do":"details"},
            success: function (response) {
                $("#mapdialog").html(response);
            }
        });
    });

    $( "#Unitconfirm" ).dialog({
        resizable: false,
        height: "auto",
        width: 250,
        modal: true,
        buttons: {
          "OK": function() {
            $( this ).dialog( "close" );
            window.location.href=window.location.href+"&s=1";
          },
          Cancel: function() {
            $( this ).dialog( "close" );
          }
        }
    });

    $( "#Uniterror" ).dialog({
        resizable: false,
        height: "auto",
        width: 250,
        modal: true,
    });

    $(".abandon").on("click", function(){
        $cid=$(this).attr("colony");
        $("#AbandonColony").attr("colony",$cid);
        $("#AbandonColony").dialog("option", "title", "Abandon Area "+$cid);
        $("#AbandonColony").dialog("open");
    });

    $( "#AbandonColony" ).dialog({
        autoOpen: false,
        resizable: false,
        height: "auto",
        width: 250,
        modal: true,
        buttons: {
          "OK": function() {
            $( this ).dialog( "close" );
            $cid=$(this).attr("colony");
            window.location.href="index.php?p=colony&ab=1&co="+$cid;
          },
          Cancel: function() {
            $( this ).dialog( "close" );
          }
        }
    });

    $("#thirdtropform").submit(function(event){
        event.preventDefault();
        $("#thirdtropform").find("button[type=submit]").attr("disabled", true);
        $("#troopdialog").dialog("open");
        
        $cid=$("#thirdtropform").find("input[name='cid']").val();
        $lat=$("#thirdtropform").find("input[name='lat']").val();
        $long=$("#thirdtropform").find("input[name='long']").val();
        $speedrate=$("#thirdtropform").find("input[name='speedrate']").val();
        $Wood=$("#thirdtropform").find("input[name='Wood']").val();
        $Stone=$("#thirdtropform").find("input[name='Stone']").val();
        $Gold=$("#thirdtropform").find("input[name='Gold']").val();
        $Food=$("#thirdtropform").find("input[name='Food']").val();
        $tid=$("#thirdtropform").find("input[name='tid']").val();
        $value=$("#thirdtropform").find("input[name='value']").val();
        $do=$("#thirdtropform").find("input[name='do']:checked").val();

        $lat=
        $.ajax({
            type: "POST",
            url: "ajaxtroop.php",
            data: {
                    "cid":$cid,
                    "lat":$lat,
                    "long":$long,
                    "speedrate":$speedrate,
                    "Wood":$Wood,
                    "Stone":$Stone,
                    "Gold":$Gold,
                    "Food":$Food,
                    "tid":$tid,
                    "value":$value,
                    "do":$do
            },
            success: function (response) {
                $("#troopdialog").html(response);
                setTimeout(function(){$("#troopdialog").dialog("close")},5000);
                setTimeout(function(){location.href="?p=tmanagement&co="+$cid},5000);                              
            }
        });
    });

    $("#troopdialog").dialog({
        autoOpen: false,
        show: {
          effect: "highlight",
          duration: 1000
        },
        hide: {
          effect: "explode",
          duration: 1000
        }
    });

    $(".tvalue").on("click", function(){
        $value=$(this).attr("tvalue");
        $(this).find("input[type=text]").val($value);
    });

});
/* if(typeof(EventSource) !== "undefined") {
    var source = new EventSource("sse.php");
    source.onmessage = function(event) {
      document.getElementById("Deneme").innerHTML = event.data + "<br>";
    };
  } else {
    document.getElementById("Deneme").innerHTML = "Sorry, your browser does not support server-sent events...";
  } */