var domain = window.location.host;
var path_name=window.location.pathname;
var child_name=path_name.split("/addons");
var interval2;
var theRequest = GetRequest();
var __UID__ = theRequest["uid"];
var __HB__=theRequest["hb"];
var __GOODS__=theRequest["goods"];
var MCHID= theRequest["mch_id"];
var url="https://"+domain+child_name[0]+"/addons/yb_mingpian/core/index.php?mch_id="+MCHID+"&s=/web/";
var vue_obj;
window.onload = function () {
    vue_obj = new Vue({
        el: "#app",
        data: {
            page: 1,
            //下拉刷新
            top: 0,
            startY: 0,
            touching: false,
            enableRefresh: true,
            type: "",
            loaded:false,
            //音频播放
            is_voice:false,
            str_time:'0:00',//当前时间（显示）
            end_time:'',//总时长（显示）
            duration:0,//总时长
            currentTime:0,//当前时间
            progress:0,//进度
            zd_end:false,//自动播放结束
            //录音
            Record_localId:'',
            Record_time:0, //录音时长
            Record_status:false,//播放录音
            stopRecord:1,//1等待录音，2:录音中；3录音完成
            userinfo: {},//用户基本信息
            edit_userinfo: {
                id: "",
                tel: "",
                wechat_number: "",
                phone: "",
                email: "",
                old_head:"",
                head_photo: ""
            },
            edit_usersummary: {
                profile: "",
                effect_tag: {},
                vioce_profile:'',
                wall_photo:[]
            },
            proposalgoods: [],//推荐产品
            goods: [],//公司产品
            v_show: {//页面模块显示隐藏
                my_recommend_product: false,
                no_my_recommend: true,
                no_data: false,
                loading_data: false,
                product_nav: false,
                product_alert: false,
                my_instruce_help_mask: false,
                mall_mask: false,
                del_tag_show:false,
                add_tag_show:false,

            },
            content_show: {
                product_nav_content: "全部产品",
                product_alert_content: "",
               // share_code: "",
                my_instruce_help_mask_content: "Hi!欢迎来到我的个人名片。我是XXX，在XXX公司XXX部（部门）担任XXX（职位），负责XXX工作（工作职责，如销售）。" +
                    "进入XXX公司以前，我曾在XXX、XXX（过往任职公司）任XXX、XXX（职位），" +
                    "拥有X年的XXX能力（核心能力，如：软件销售），在XXX领域（专业领域，如：软件销售）经验丰富。" +
                    "工作之外，我喜欢阅读和跑步，与家人一起探索新的旅程是我最大的乐趣。（兴趣爱好）" +
                    "您可以通过名片中的官网、企业动态、商城更多的了解XXX公司，如有业务合作或其他任何问题，请通过IM功能与我联系，我会第一时间回复您！",
                effect_tag_default: {
                    "诚信至上": 0,
                    "豪爽耿直": 0,
                    "广交朋友": 0,
                    "原则性强": 0,
                    "互联网创业者": 0,
                    "有俩把刷子": 0,
                    "温暖的小太阳": 0,
                    "小奶猫": 0
                }
            },
            variable: {
                edit_head_photo:"",
                nav: 0,
                goods_id: 0,
                proposal: 0, 
                profile_length: 0,
                effect_tag_count: 0,
                del_effect_tag: "",
                add_effect_tag: "",
                wx_config:0,
                wall_photo_count:0,
                wall_photo_data:{},
                tui:2
               // head_photo_base64:"",
                //log_photo_base64:""
            },
            wx_config:false,
            pic_path:"",
            pic_arr:[],
            serverId:""
        },
        created: function () {
            this.GetUserinfo();
        },
        watch: {
            type: function (val) {
                if(val==="edit_info_wrap"){
                    this.GetWxConfig();
                    this.variable.edit_head_photo=this.userinfo.head_photo;
                }else if (val === "my-recommend-box") {
                    this.GetProposalGoods();
                } else if (val === "goods") {
                    this.variable.nav = 0;
                    this.goods = [];
                    this.page = 1;
                    this.GetGoods();
                } else if (val === "poster_container") {
                    //this.GetPoster();
                } else if (val === "edit_usersummary") {
                    this.GetWxConfig();
                    this.edit_usersummary.profile = this.userinfo.profile;
                    this.edit_usersummary.vioce_profile = this.userinfo.vioce_profile;
                    if(this.userinfo.effect_tag){
                        this.edit_usersummary.effect_tag = this.userinfo.effect_tag;
                    }
                    for (var key in this.edit_usersummary.effect_tag) {
                        this.variable.effect_tag_count = this.variable.effect_tag_count+1;
                    }
                    for (var key in this.content_show.effect_tag_default) {
                        for (var tag_key in this.edit_usersummary.effect_tag) {
                            if (key === tag_key) {
                                this.content_show.effect_tag_default[key] = 1;
                            }
                        }
                    }
                    this.variable.wall_photo_data={};
                    if(this.userinfo.wall_photo!=""&&this.userinfo.wall_photo!=null){
                        var wall_photo_arr=eval('('+this.userinfo.wall_photo+')');
                        var wall_photo_obj={};
                        for (var i=0;i<wall_photo_arr.length;i++){
                            wall_photo_obj[i]=wall_photo_arr[i];
                            vue_obj.variable.wall_photo_count=vue_obj.variable.wall_photo_count+1;
                        }
                        this.variable.wall_photo_data=wall_photo_obj;
                    }
                }

                if(val !== "edit_usersummary" && this.is_voice){//离开时暂停播放
                    this.bf();
                    // this.is_voice=false;
                    // this.str_time='0:00';
                    // this.end_time='';
                    // this.currentTime=0;
                    // this.progress=0;

                }
            },
            is_voice:function (val) {
                var that=this;
                if(val){ //获取音频播放进度
                    var interval = setInterval(function () {
                        if (!that.is_voice) {
                            clearInterval(interval);
                        }else{
                            console.log(that.currentTime)
                            that.currentTime=that.currentTime+1;
                            that.str_time=that.miao_to_time(that.currentTime);
                            that.progress = parseInt((that.currentTime/ that.duration) * 100)

                        }
                    }.bind(this), 1000);
                }
            },
            stopRecord:function(val){
                var that=this;
                if(val===2){
                    var interval2 = setInterval(function () {

                        if(that.stopRecord!==2){
                            clearInterval(interval2);
                            console.log(that.Record_time);
                        }else{
                            that.Record_time=that.Record_time+1;
                            if(that.Record_time>59){
                                that.clearLoop();//结束录音
                            }
                        }

                    }.bind(this), 1000);
                }

            }

        },
        methods: {
            get_skin:function () {
                var ta=this;
                this.$http.get(url+"People/get_skins",{mch_id:MCHID}).then(function (res) {
                    $(document).ready(function () {
                        $("#nnn").append('<link rel="stylesheet" href="./vue/css/'+res.data+'/userinfo.css">');
                        $("#nnn").append('<link rel="stylesheet" href="./vue/css/'+res.data+'/index.css">');
                    });
                })
            },
            GetUserinfo: function () {
                var that=this;
                that.loaded=true;
                this.$http.get(url + 'Userinfo/GetUserinfo', {uid: __UID__,is_poster:1}).then(function (res) {
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    that.loaded=false;
                    if (res.data.code == 0) {
                        //这是网上的一张图片链接
                        this.userinfo = res.data.info;
                        this.edit_userinfo.id=res.data.info.id;
                        this.edit_userinfo.tel=res.data.info.tel;
                        this.edit_userinfo.phone=res.data.info.phone;
                        this.edit_userinfo.email=res.data.info.email;
                        this.edit_userinfo.head_photo=res.data.info.head_photo;
                        this.edit_userinfo.old_head=res.data.info.head_photo;
                       // this.edit_userinfo.vioce_profile=res.data.info.vioce_profile;
                        this.variable.edit_head_photo=res.data.info.head_photo;

                        this.edit_userinfo.wechat_number=res.data.info.wechat_number;
                        var flag=true;

                        if(typeof (__GOODS__)!="undefined"){
                            flag=false;
                            this.type="my-recommend-box";
                        }
                        if(flag){
                            this.type="lookcard_wapper";
                        }
                    } else {
                        layer.open({
                            content: res.data.msg
                            , skin: 'msg'
                            , time: 2 //2秒后自动关闭
                        });
                    }
                }, function (res) {
                    that.loaded=false;
                });
            },
            //音频播放
            bf:function(){
                var that=this;
                var audio = document.getElementById('music1');
                audio.loop = false;
                // alert(that.edit_usersummary.vioce_profile);
                audio.addEventListener('ended', function () {
                    console.log('播放停止');
                    that.is_voice=false;
                        that.str_time='0:00';
                        that.end_time='';
                        that.currentTime=0;
                        that.progress=0;
                        that.zd_end=true;
                        //在这个方法里写相应的逻辑就可以了
                }, false);
                //audio.addEventListener("loadeddata", //歌曲一经完整的加载完毕( 也可以写成上面提到的那些事件类型)
                //    function() {
                //        console.log('歌曲加载完毕')
                //
                //    }, false);
                var duration=audio.duration;
                var currentTime=audio.currentTime;
                if(that.zd_end){
                    currentTime=0;
                    that.zd_end=false;
                }
                that.duration=duration;
                that.currentTime=currentTime;
                var end_time = that.miao_to_time(duration);
                that.str_time  = that.miao_to_time(currentTime);
                 if(end_time=="NaN:NaN"){
                     // that.edit_usersummary.vioce_profile=that.userinfo.vioce_profile;
                     msg('受微信解析影响,建议直接保存后试听');
                     end_time=60;
                 }
                that.end_time=end_time;
                console.log(that.end_time);
              //  console.log(that.str_time);
                if(audio!==null){
                    //检测播放是否已暂停.audio.paused 在播放器播放时返回false.
                    if(audio.paused)
                    {
                        audio.play();//audio.play();// 这个就是播放
                        that.is_voice=true;

                    }else{
                        that.is_voice=false;
                        audio.pause();// 这个就是暂停
                    }
                }
            },
            //时间转换
            miao_to_time: function (currentTime) {
                if(typeof currentTime!=='number' || currentTime<0){
                    return '0:00';
                }
                currentTime = Math.round(currentTime);
                var a = currentTime - Math.floor(currentTime / 60) * 60;
                a = a.toString();
                if (a.length == 1) {
                    a = '0' + a;
                }
                return Math.floor(currentTime / 60) + ":" + a;
            },
            //录音
            sound_recording:function(){
                var that=this;
                wx.startRecord({
                    success: function() {
                        console.log('开始录音');
                        that.stopRecord=2;
                    },
                    cancel: function() {
                        alert('用户拒绝授权录音');
                    }
                });
            },
            clearLoop(e) {
                console.log('结束录音');
                var that=this;
                that.stopRecord=3;
                wx.stopRecord({
                    success: function (res) {
                        var localId = res.localId;
                        console.log( localId)
                        that.Record_localId=localId;
                    }
                });
            },
            //播放/停止
            play_voice:function(){
                console.log('播放/停止');
                var that=this;
                if(that.Record_status){
                    wx.stopVoice({
                        localId: that.Record_localId // 需要停止的音频的本地ID，由stopRecord接口获得
                    });
                    that.Record_status=false;
                }else{
                    wx.playVoice({
                        localId: that.Record_localId// 需要播放的音频的本地ID，由stopRecord接口获得
                    });
                    that.Record_status=true;
                }


            },
            //重录
            restart_mall_record:function(){
               var that=this;
                if(that.Record_status){
                    wx.stopVoice({
                        localId: that.Record_localId // 需要停止的音频的本地ID，由stopRecord接口获得
                    });
                    that.Record_status=false;
                }
                that.Record_localId='';
                that.Record_time=0;
                that.stopRecord=1;
            },
            //下载录音
            uploadVoice:function(){
                var that=this;
                if(!that.Record_localId){
                    alert('录音下载失败');
                    return;
                }
                var xz_loading=layer.open({
                    type: 2
                    ,content: '下载中...'
                });
                console.log(that.Record_localId)
                wx.uploadVoice({
                    localId: that.Record_localId, // 需要上传的音频的本地ID，由stopRecord接口获得
                    isShowProgressTips: 0, // 默认为1，显示进度提示
                    success: function (res) {
                        //把录音在微信服务器上的id（res.serverId）发送到自己的服务器供下载。
                        that.$http.get(url + 'userinfo/uploadVoice', {Record_serverId:res.serverId,uid: __UID__}).then(function (res) {
                            layer.close(xz_loading);
                            if (typeof res.data == 'string') {
                                res.data = json_parse(res.data);
                            }
// alert(res.data.info);
                            console.log(res.data);
                            if(res.data.code==0){
                                    that.edit_usersummary.vioce_profile=res.data.info;
                                    that.close_mall_record();
                                }else{
                                    layer.open({
                                        content: res.data.msg
                                        ,btn: '确定'
                                    });

                                }
                        });
                    },
                    fail:function (e) {
                        console.log(e)
                        layer.close(xz_loading);
                        layer.open({
                            content: '录音下载失败'
                            ,btn: '确定'
                        });
                    }
                });
            },
            //删除录音
            delete_record:function(){
              this.edit_usersummary.vioce_profile='';
            },
            //打开录音页面
            open_mall_record:function() {
                var that = this;
                that.v_show.mall_mask=true;
                that.Record_localId='';
                that.Record_time=0;
                that.stopRecord=1;
                if(that.is_voice){
                    that.bf();
                }
                that.str_time = '0:00';//当前时间（显示）
                that.end_time = '';//总时长（显示）
                that.duration = 0;//总时长
                that.currentTime = 0;//当前时间
                that.progress = 0;//进度
            },
            //关闭录音页面
            close_mall_record:function(){
                var that=this;
                if(that.Record_status){
                    wx.stopVoice({
                        localId: that.Record_localId // 需要停止的音频的本地ID，由stopRecord接口获得
                    });
                    that.Record_status=false;
                }
                that.v_show.mall_mask=false;
                that.Record_localId='';
                that.Record_time=0;
                that.stopRecord=1;
            },
            EditUserinfo: function () {
                // alert(that.edit_usersummary.vioce_profile);
                var that=this;
                this.$http.post(url + 'Userinfo/EditUserinfo', this.edit_userinfo, {emulateJSON: true}).then(function (res) {
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    console.log(res.data)
                    if (res.data.code == 0) {
                        layer.open({
                            content: res.data.msg
                            , skin: 'msg'
                            , time: 2 //2秒后自动关闭
                        });
                        that.type = "card-box";
                        that.userinfo = res.data.info;
                    } else {
                        layer.open({
                            content: res.data.msg
                            , skin: 'msg'
                            , time: 2 //2秒后自动关闭
                        });
                    }
                }, function (res) {
                    console.log(res.status);
                });
            },
            FileChange: function (obj) {
                var edit_userinfo = this.edit_userinfo;
                var x = new FileReader;
                x.readAsDataURL(obj.target.files[0]);
                x.onloadend = function () {
                    edit_userinfo.head_photo = this.result;
                }
            },
            GetProposalGoods: function () {
                this.$http.get(url + 'Userinfo/GetProposalGoods', {uid: this.userinfo.id}).then(function (res) {
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    if (res.data.code == 0) {
                        if (res.data.info.length > 0) {
                            this.v_show.no_my_recommend = false;
                            this.v_show.my_recommend_product = true;
                            this.proposalgoods = res.data.info;
                        }
                    } else {
                        layer.open({
                            content: res.data.msg
                            , skin: 'msg'
                            , time: 2 //2秒后自动关闭
                        });
                    }
                }, function (res) {
                });
            },
            GetGoods: function () {
                //this.type = "goods";
                this.v_show.loading_data = true;
                this.$http.get(url + 'Userinfo/GetGoods', {
                    uid: this.userinfo.id,
                    nav: this.variable.nav,
                    page: this.page
                }).then(function (res) {
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    if (res.data.code == 0) {
                        if (res.data.info.length > 0) {
                            this.goods = this.goods.concat(res.data.info);
                            this.v_show.loading_data = false;
                            this.page = this.page + 1;
                        } else {
                            this.v_show.loading_data = true;
                        }
                    } else {
                        layer.open({
                            content: res.data.msg
                            , skin: 'msg'
                            , time: 2 //2秒后自动关闭
                        });
                    }
                }, function (res) {
                });
            },
            SelectProdeuctNav: function (type, nav, navcontent) {
                if (navcontent != '') {
                    this.content_show.product_nav_content = navcontent;
                }
                if (type === 0) {
                    if (this.v_show.product_nav) {
                        this.v_show.product_nav = false;
                    } else {
                        this.v_show.product_nav = true;
                    }
                } else {
                    this.v_show.product_nav = false;
                    this.variable.nav = nav;
                    this.goods = [];
                    this.page = 1;
                    this.GetGoods();
                }
            },
            ProposalAlert: function (goods_id,tui) {
                var proposal = $("#goods_state_" + goods_id).val();
                if (proposal == 2) {
                    this.content_show.product_alert_content = "取消推荐";
                } else {
                    this.content_show.product_alert_content = "推荐TA";
                }
                this.variable.tui = tui;
                this.variable.goods_id = goods_id;
                this.variable.proposal = proposal;
                this.v_show.product_alert = true;
            },
            CheckProposalGoods: function () {
                if (this.variable.proposal === 2) {
                    layer.open({
                        content: '确定取消推荐产品？'
                        , btn: ['取消推荐', '取消']
                        , yes: function (index) {
                            vue_obj.ProposalGoods();
                        }
                    });
                } else {
                    vue_obj.ProposalGoods();
                }
            },
            ProposalGoods: function () {
                this.$http.post(url + 'Userinfo/ProposalGoods', {
                    uid: this.userinfo.id,
                    tui: this.variable.tui,
                    goods_id: this.variable.goods_id,
                    proposal: this.variable.proposal
                }, {emulateJSON: true}).then(function (res) {
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    if (res.data.code == 0) {
                        if (this.variable.proposal == 2) {
                            $("#goods_state_" + this.variable.goods_id).val(1);
                            $("#goods_state_name_" + this.variable.goods_id).html("已发布");
                        } else {
                            $("#goods_state_" + this.variable.goods_id).val(2);
                            $("#goods_state_name_" + this.variable.goods_id).html("推荐");
                        }
                        this.v_show.product_alert = false;
                    } else {
                        layer.open({
                            content: res.data.msg
                            , skin: 'msg'
                            , time: 2 //2秒后自动关闭
                        });
                    }
                }, function (res) {
                    console.log(res.status);
                });
            },
            GetPoster: function () {
                return;
                this.$http.get(url + 'Userinfo/GetShareCode', {
                    uid: __UID__,
                    mch_id: this.userinfo.mch_id
                }).then(function (res) {
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    if (res.data.code == 0) {
                        this.content_show.share_code = res.data.info;

                        Vue.nextTick(function () {
                            const canvas = $("#svg_box").find('svg')[0];
                            try {
                                svgAsPngUri(canvas, null, uri => $("#poster_box_img").attr("src",uri));
                            } catch (err) {
                                console.log(err);
                            }
                        });

                    } else {
                        layer.open({
                            content: res.data.msg
                            , skin: 'msg'
                            , time: 2 //2秒后自动关闭
                        });
                    }
                }, function (res) {
                });
            },
            AddTag:function(tag){
                if(""==tag){
                    var tag_obj = this.edit_usersummary.effect_tag;
                    tag_obj[this.variable.add_effect_tag] = 0;
                    this.edit_usersummary.effect_tag = {};
                    this.edit_usersummary.effect_tag=tag_obj;
                    this.variable.effect_tag_count = this.variable.effect_tag_count+1;
                    for (var key in this.content_show.effect_tag_default) {
                        if(key==this.variable.add_effect_tag){
                            this.content_show.effect_tag_default[this.variable.add_effect_tag]=1;
                            break;
                        }
                    }
                    this.v_show.add_tag_show=false;
                }else {
                    if(this.content_show.effect_tag_default[tag]==0){
                        var tag_obj = this.edit_usersummary.effect_tag;
                        tag_obj[tag] = 0;
                        this.edit_usersummary.effect_tag = {};
                        this.edit_usersummary.effect_tag=tag_obj;
                        this.content_show.effect_tag_default[tag]=1;
                        this.variable.effect_tag_count = this.variable.effect_tag_count+1;
                    }
                }
            },
            DelTag:function () {
                var tag_obj = this.edit_usersummary.effect_tag;
                delete tag_obj[this.variable.del_effect_tag];
                this.edit_usersummary.effect_tag = {};
                this.edit_usersummary.effect_tag=tag_obj;
                for (var key in this.content_show.effect_tag_default) {
                    if(key==this.variable.del_effect_tag){
                        this.content_show.effect_tag_default[this.variable.del_effect_tag]=0;
                        break;
                    }
                }
                this.v_show.del_tag_show=false
                this.variable.effect_tag_count = this.variable.effect_tag_count-1;
            },
            GetWxConfig:function(){
                if(this.variable.wx_config==0){
                    var wxurl=window.location.href;
                    this.$http.get(url + 'Userinfo/GetWxConfig', {
                        url: wxurl,
                        uid: __UID__
                    }).then(function (res) {
                        if (typeof res.data == 'string') {
                            res.data = json_parse(res.data);
                        }
                        if (res.data.code == 0) {
                            // alert(res.data.info);
                            // console.log(res.data.info);
                            wx.config({
                                debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
                                appId: res.data.info.appId, // 必填，企业号的唯一标识，此处填写企业号corpid
                                timestamp: res.data.info.timestamp, // 必填，生成签名的时间戳
                                nonceStr:res.data.info.nonceStr, // 必填，生成签名的随机串
                                signature: res.data.info.signature,// 必填，签名，见附录1
                                jsApiList: [
                                    'chooseImage',
                                    'previewImage',
                                    'uploadImage',
                                    'downloadImage',
                                    'startRecord',
                                    'stopRecord',
                                    'playVoice',
                                    'stopVoice',
                                    'uploadVoice',
                                ] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
                            });
                            this.variable.wx_config=1;
                        } else {
                            layer.open({
                                content: res.data.msg
                                , skin: 'msg'
                                , time: 2 //2秒后自动关闭1
                            });
                        }
                    }, function (res) {
                    });
                }
            },
            ChooseImage:function(){
                wx.chooseImage({
                    count: 1, // 默认9
                    sizeType: [ 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
                    sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
                    defaultCameraMode: "normal", //表示进入拍照界面的默认模式，目前有normal与batch两种选择，normal表示普通单拍模式，batch表示连拍模式，不传该参数则为normal模式。（注：用户进入拍照界面仍然可自由切换两种模式）
                    success: function (res) {
                        var localIds = res.localIds; // 返回选定照片的本地ID列表，
                        var phone_system=CheckPhone();
                        if(phone_system==1){
                            vue_obj.variable.edit_head_photo=localIds[0];
                        }else{
                            wx.getLocalImgData({
                                localId: localIds[0], // 图片的localID
                                success: function (res) {
                                    var localData = res.localData; // localData是图片的base64数据，可以用img标签显示
                                    vue_obj.variable.edit_head_photo=localData;
                                }
                            });
                        }
                        // andriod中localId可以作为img标签的src属性显示图片；
                        // 而在IOS中需通过上面的接口getLocalImgData获取图片base64数据，从而用于img标签的显示
                        //图片上传
                        wx.uploadImage({
                            localId: localIds[0], // 需要上传的图片的本地ID，由chooseImage接口获得
                            isShowProgressTips: 0, // 默认为1，显示进度提示
                            success: function (res) {
                                var serverId = res.serverId; // 返回图片的服务器端ID
                                vue_obj.edit_userinfo.head_photo=serverId;
                            }
                        });
                    }
                });
            },
            ChooseImageArr:function(){
                var count=9-this.variable.wall_photo_count;
                if(count==0){
                    msg("最多上传9张照片！");
                    return;
                }
                wx.chooseImage({
                    count: count, // 默认9
                    sizeType: [ 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
                    sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
                    defaultCameraMode: "normal", //表示进入拍照界面的默认模式，目前有normal与batch两种选择，normal表示普通单拍模式，batch表示连拍模式，不传该参数则为normal模式。（注：用户进入拍照界面仍然可自由切换两种模式）
                    success: function (res_a) {
                        var localIds = res_a.localIds; // 返回选定照片的本地ID列表，
                        var phone_system=CheckPhone();
                        var wall_photo_data_obj=vue_obj.variable.wall_photo_data;
                        var server_count=0;
                        for (var i=0;i<localIds.length;i++){
                            if(phone_system==2){
                                wx.getLocalImgData({
                                    localId: localIds[i], // 图片的localID
                                    success: function (res_b) {
                                        var localData = res_b.localData; // localData是图片的base64数据，可以用img标签显示
                                        localIds[i]=localData;
                                    }
                                });
                            }
                            // andriod中localId可以作为img标签的src属性显示图片；
                            // 而在IOS中需通过上面的接口getLocalImgData获取图片base64数据，从而用于img标签的显示
                            //图片上传
                            wx.uploadImage({
                                localId: localIds[i], // 需要上传的图片的本地ID，由chooseImage接口获得
                                isShowProgressTips: 0, // 默认为1，显示进度提示
                                success: function (res_c) {
                                    var serverId = res_c.serverId; // 返回图片的服务器端ID
                                    wall_photo_data_obj[serverId]=localIds[server_count];
                                    if(server_count==localIds.length-1){
                                        vue_obj.variable.wall_photo_data={};
                                        vue_obj.variable.wall_photo_data=wall_photo_data_obj;
                                    }
                                    ++server_count;
                                }
                            });
                            vue_obj.variable.wall_photo_count=vue_obj.variable.wall_photo_count+1;
                        }
                    }
                });
            },
            DelWallPhoto:function(key){
                var wall_photo_data_obj=vue_obj.variable.wall_photo_data;
                delete wall_photo_data_obj[key];
                vue_obj.variable.wall_photo_data={};
                vue_obj.variable.wall_photo_data=wall_photo_data_obj;
                vue_obj.variable.wall_photo_count=vue_obj.variable.wall_photo_count-1;
            },
            EditUserSummary:function(){
                var wall_photo_arr=[];
                for ( var key in this.variable.wall_photo_data){
                    wall_photo_arr.push(key);
                }
                this.edit_usersummary.wall_photo=wall_photo_arr;
                var edit_usersummary_data={};
                edit_usersummary_data["id"]=this.userinfo.id;
                edit_usersummary_data["profile"]=$("#profile_div").html();
                edit_usersummary_data["effect_tag"]=JSON.stringify(this.edit_usersummary.effect_tag);
                if(wall_photo_arr.length<=0){
                    edit_usersummary_data["wall_photo"]="";
                }else {
                    edit_usersummary_data["wall_photo"]=JSON.stringify(wall_photo_arr);
                }

                edit_usersummary_data["vioce_profile"]=this.edit_usersummary.vioce_profile;
                this.$http.post(url + 'Userinfo/EditUserSummary', edit_usersummary_data, {emulateJSON: true}).then(function (res) {
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    if (res.data.code == 0) {
                        layer.open({
                            content: res.data.msg
                            , skin: 'msg'
                            , time: 2 //2秒后自动关闭
                        });
                        this.type = "card-box";
                        this.userinfo = res.data.info;
                    } else {
                        layer.open({
                            content: res.data.msg
                            , skin: 'msg'
                            , time: 2 //2秒后自动关闭
                        });
                    }
                }, function (res) {
                    console.log(res.status);
                });
            },
            TouchStart(e) {
                this.startY = e.targetTouches[0].pageY
                this.touching = true
            },
            ProfileChange: function (e) {
                this.edit_usersummary.profile = $(e.target).html();
                console.log(this.content_show.my_instruce_help_mask_content.length)
            },
            TouchMove(e) {
                if (this.touching) {
                    this.top = e.changedTouches[0].pageY - this.startY;
                }
            },
            TouchEnd(e) {
                if (!this.enableRefresh) return
                this.touching = false;
                this.top = 0;
                if (e.changedTouches[0].pageY - this.startY > 60) {
                    console.log('下拉刷新中')
                    if (this.type === "goods") {
                        this.variable.nav = 0
                        this.goods = [];
                        this.page = 1;
                        this.GetGoods();
                    }
                } else if (this.startY - e.changedTouches[0].pageY > 60) {
                    console.log('上拉加载更多');
                    if (this.type === "goods") {
                        if (!this.v_show.loading_data) {
                            this.GetGoods();
                        }
                    }
                }
            }
        }
    })
}

function CheckPhone() {
    var u = navigator.userAgent;
    if (u.indexOf('Android') > -1 || u.indexOf('Linux') > -1) {//安卓手机
        return 1;
    } else if (u.indexOf('iPhone') > -1) {//苹果手机
        return 2;
    } else if (u.indexOf('Windows Phone') > -1) {//winphone手机
        return 3;
    }
    
}
$(document).ready(function (e) {
    var counter = 0;
    if (window.history && window.history.pushState) {
        $(window).on('popstate', function () {
            window.history.pushState('forward', null, '#');
            counter++;
            if(counter>0){
                window.history.forward(1);
                    if (vue_obj.type == 'lookcard_wapper') {
                        window.location.href = "my.html?uid=" + __UID__ + "&mch_id=" + MCHID;
                    } else {
                        if(vue_obj.type == 'card-box'){
                            vue_obj.type = 'lookcard_wapper';
                        }else{
                            vue_obj.type = 'card-box';
                        }
                    }
            }
        })
    }
    window.history.pushState('forward', null, '#');
    window.history.forward(1);
})
//安卓手机微信浏览器中长按提示“在浏览器打开”解决方法
document.oncontextmenu = function(e){
    e.preventDefault();
}
