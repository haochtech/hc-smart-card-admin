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
        type:1,//1：每日海报；2 自定义海报
        page_type:1,//1：海报页面；2海报生成图片
        user:{},
        bgimg:'vue/img/default.jpg',
        pic_path:'vue/img/add_pic.png',
        wx_config:false,
        serverId:'',
        loaded:false,
        show:true,
        show_loading:false,
        loading:false
    },
    watch: {
        serverId: function (val) {
            console.log(val)
            if (val) {
                this.show_loading=true;
                this.upload_pic();
            }
        }
    },
    created: function () {
        var that=this;
        this.getuserinfo();
        if (!this.wx_config) {
            var wxurl = window.location.href;
            this.$http.get(url + 'Userinfo/GetWxConfig', {
                url: wxurl,
                uid: __UID__
            }).then(function (res) {
                console.log(res);
                if (typeof res.data == 'string') {
                    res.data = json_parse(res.data);
                }
                if (res.data.code == 0) {
                    console.log(res.data.info);
                    wx.config({
                        debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
                        appId: res.data.info.appId, // 必填，企业号的唯一标识，此处填写企业号corpid
                        timestamp: res.data.info.timestamp, // 必填，生成签名的时间戳
                        nonceStr: res.data.info.nonceStr, // 必填，生成签名的随机串
                        signature: res.data.info.signature,// 必填，签名，见附录1
                        jsApiList: [
                            'chooseImage',
                            'previewImage',
                            'uploadImage',
                            'downloadImage'
                        ] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
                    });
                    that.wx_config = true;
                } else {
                    layer.open({
                        content: res.data.msg
                        , skin: 'msg'
                        , time: 2 //2秒后自动关闭
                    });
                }
            }, function (res) {
            });
        }
    },
    methods:{
        getuserinfo:function(){
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
        //选择图片
        ChooseImage: function() {
            var that=this;
            this.show_loading=true;
            wx.chooseImage({
                count: 1,
                sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
                sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
                defaultCameraMode: "normal", //表示进入拍照界面的默认模式，目前有normal与batch两种选择，normal表示普通单拍模式，batch表示连拍模式，不传该参数则为normal模式。（注：用户进入拍照界面仍然可自由切换两种模式）
                success: function (res) {
                    console.log(res)
                    var localIds = res.localIds; // 返回选定照片的本地ID列表，
                    console.log(localIds);
                    var phone_system = CheckPhone();
                    console.log(phone_system)
                    if (phone_system == 1) {
                        wx.uploadImage({
                            localId: localIds[0], // 需要上传的图片的本地ID，由chooseImage接口获得
                            isShowProgressTips: 1, // 默认为1，显示进度提示
                            success: function (res) {
                                console.log(res)
                                that.serverId = res.serverId; // 返回图片的服务器端ID
                            }
                        });
                        // that.pic_path = getBase64Image(localIds[0]);
                    } else {
                        wx.getLocalImgData({
                            localId: localIds[0], // 图片的localID
                            success: function (res) {
                                console.log(res)
                                that.pic_path = res.localData; // localData是图片的base64数据，可以用img标签显示
                            }
                        });
                    }
                    that.show_loading=false;
                    //图片上传

                },
                fail:function(t){
                    msg(t.errMsg);
                }
            });
            //  ChooseImage(this, 1);
        },

        upload_pic:function(){
            var that = this;
            this.$http.get(url + 'Webindex/upload_pic', {uid: __UID__,'pic_path':that.serverId}).then(function (res) {
                this.show_loading=false;
                if (typeof res.data == 'string') {
                    res.data = json_parse(res.data);
                }
                console.log(res.data)
                if (res.data.code == 0) {
                    msg('上传成功');
                    that.pic_path = res.data.info;
                }else{
                    msg('图片上传失败');
                    that.pic_path='vue/img/add_pic.png';
                }
            });
        }
    }
});
function chImg() {
    $.ajax({
        type: "get",
        url: url + 'Userinfo/randbgimg',
        success: function (data) {
            console.log(data);
            if(data.length<10){
                data='vue/img/default.jpg';
                msg("请先下载海报素材");
            }
            vu.bgimg = data+"";
        }
    });
}
var running = false;
function saveImg() {
    if(vu.loading){return}
    vu.loading=true;
    html2canvas(document.querySelector("#app"),{
        useCORS:true,
        logging:false,
    }).then(canvas => {
        var dataUrl = canvas.toDataURL();
        vu.page_type=2;
        var src=dataUrl;
        vu.loading=false;
        $("#img").attr('src',src);
    });
}

function saveImg2(){
    if(vu.loading){return}
    vu.loading=true;
    html2canvas(document.querySelector("#app2"),{
        useCORS:true,
        logging:true,
    }).then(canvas => {
        var dataUrl = canvas.toDataURL();
    vu.page_type=2;
    var src=dataUrl;
    vu.loading=false;
    //$("#img").val(src);
    $("#img").attr('src',src);
//        $("#show_change").empty();
//        $("#show_change").append( '长按保存到相册, 可分享给好友或朋友圈');
});
}
$(document).ready(function (e) {
    var counter = 0;
    if (window.history && window.history.pushState) {
        $(window).on('popstate', function () {
            window.history.pushState('forward', null, '#');
            counter++;
            if(counter>0){

                if (vu.page_type == 2) {
                    window.history.forward(1);
                    vu.page_type = 1;
                }else {
                    window.location.href = "my.html?uid=" + __UID__ + "&mch_id=" + MCHID;
                }

            }
        })
    }
    window.history.pushState('forward', null, '#');
    window.history.forward(1);
})
function getBase64Image(img) {
    var dataURL='';
    var canvas = document.createElement("canvas");
    canvas.width = img.width;
    canvas.height = img.height;
    var ctx = canvas.getContext("2d");
    ctx.drawImage(img, 0, 0, img.width, img.height);
    dataURL = canvas.toDataURL("image/png");
    return dataURL; // return dataURL.replace("data:image/png;base64,", "");
}