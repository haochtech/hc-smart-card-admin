var domain = window.location.host;

var theRequest = GetRequest();
var __UID__ = theRequest["uid"];
var MCHID= theRequest["mch_id"];
var path_name=window.location.pathname;
var child_name=path_name.split("/addons");
var url="https://"+domain+child_name[0]+"/addons/yb_mingpian/core/index.php?mch_id="+MCHID+"&s=/web/";
var vu=new Vue({
    el:'#main',
    data:{
        //下拉刷新
        type:1,//
        user:{},
        bgimg:'vue/img/default.jpg',
        bug:false,
        loaded:false,
        show:true,
        loading:false


    },
    created:function(){
        var that=this;
        that.loaded=true;
        this.$http.get(url+'Userinfo/GetUserinfo',{uid:__UID__}).then(function(res){

            if (typeof res.data == 'string') {
                res.data = json_parse(res.data);
            }
            that.loaded=false;
            if(res.data.code==0){
                that.user=res.data.info;
            }else{
                msg(res.data.msg);
            }
        },function(res){
            that.loaded=false;
            that.bug=true;
        });
    },

});
//    that.loaded=true;
//    $.ajax({
//        type: "get",
//        url: url + 'Userinfo/GetUserinfo&uid='+__UID__,
//        dataType:"json",
//        success: function (data) {
//            console.log(data);
//            if (typeof data == 'string') {
//                data = JSON.parse(data);
//            }
//
//            vu.user = data.info;
//        }
//    });



var running = false;

function saveImg() {
    console.log(vu.loading);
    if(vu.loading){return}
    vu.loading=true;
    //layer.open({type: 2,shadeClose:false});
    html2canvas(document.querySelector("#app"),{
        useCORS:true,
        logging:true,
    }).then(canvas => {
        var dataUrl = canvas.toDataURL();
    var src=dataUrl;
    vu.loading=false;
    //$("#img").val(src);
    $("#img").attr('src',src);
//        $("#show_change").empty();
//        $("#show_change").append( '长按保存到相册, 可分享给好友或朋友圈');
    vu.type=2;

});
}
$(document).ready(function (e) {
    var counter = 0;
    if (window.history && window.history.pushState) {
        $(window).on('popstate', function () {
            window.history.pushState('forward', null, '#');
            counter++;
            if(counter>0){

                if (vu.type == 2) {
                    window.history.forward(1);
                    vu.type = 1;
                }else {
                    window.location.href = "my.html?uid=" + __UID__ + "&mch_id=" + MCHID;
                }
            }
        })
    }
    window.history.pushState('forward', null, '#');
    window.history.forward(1);
})