window.onload=function(){
    function GetRequest() {
        var url = location.search; //获取url中"?"符后的字串
        var theRequest = new Object();
        if (url.indexOf("?") != -1) {
            var str = url.substr(1);
            strs = str.split("&");
            for (var i = 0; i < strs.length; i++) {
                theRequest[strs[i].split("=")[0]] = unescape(strs[i].split("=")[1]);
            }
        }
        return theRequest;
    }
    var server_count=0;
    var do_num=0;
    var theRequest = GetRequest();
    var __UID__ = theRequest["uid"];
    var MCHID= theRequest["mch_id"];
    var USER_DYNAMIC=theRequest["USER_DYNAMIC"];
    var domain = window.location.host;
    var path_name=window.location.pathname;
    var child_name=path_name.split("/addons");
    var url="https://"+domain+child_name[0]+"/addons/yb_mingpian/core/index.php?mch_id="+MCHID+"&s=/web/";
    var vue_obj;
    vue_obj=new Vue({
        el:'#app',
        data:{
            //下拉刷新
            top: 0,
            all_num:10,
            startY: 0,
            touching: false,
            enableRefresh:true,
            type:1,
            bug:false,
            loaded:false,
            show:true,
            index_behavior:{},
            index_data:[],
            page:1,
            down:true,
            index_people:[],
            page_p:1,
            down_p:true,
            hidden:-1,
            dynamic:[],
            all_ids:[],
            wx_js:'',
            c_con:'',
            c_title:'',
            sub_user_id:'',
            sub_mch_id:'',
            sub_comment_id:'',
            user_add_comments:'',
            show_menu:true,
            show_img_choose:true,
            show_bus_content:false,
            show_like:false,
            show_all_like:true,
            show_part_like:false,
            show_comments:false,
            show_add_comment:false,
            show_add_dynamic:false,
            is_obj:false,
            shaya_content:"",
            wx_config:0,
            pic_arr_count:0,
            pic_data:{},
            USER_DYNAMIC:0
        },
        created:function(){
            var that=this;
            if (USER_DYNAMIC){
                that.USER_DYNAMIC=1;
                that.get_list(1);
            }else {
                that.get_list();
            }

        },
        watch : {
            type:function(val) {
                if(val==9){
                    this.GetWxConfig();
                }
            }
        },
        methods:{
            get_skin:function () {
                var ta=this;
                this.$http.get(url+"People/get_skins",{mch_id:MCHID}).then(function (res) {
                    $(document).ready(function () {
                        $("#nnn").append('<link rel="stylesheet" href="./vue/css/'+res.data+'/message.css?v=1.2">');
                        $("#nnn").append('<link rel="stylesheet" href="./vue/css/'+res.data+'/index.css">');
                        $(".choosed_tab").parent().children(0).attr("src","vue/img/"+res.data+"/find2.png");
                    });
                })
            },
            url:function(e){
                window.location.href=e+".html?uid="+__UID__+"&mch_id="+MCHID;
            },
            //切换列表
            change_com:function () {
                var ta=this;
                var nn=ta.select1;
                for (var a=0;a<ta.select_item.length;a++){
                    if (ta.select_item[a]["value"]==nn){
                        ta.now_select=ta.select_item[a]["text"];
                        break;
                    }
                }
            },
            //liebiao
            get_list:function(is_user='') {
                var that=this;
                that.loaded=true;
                that.show_img_choose=true;
                var da={
                    uid:__UID__,
                    page:that.page,
                    user_dynamic:USER_DYNAMIC,
                    is_user:is_user
                };
                this.$http.get(url+'People/dynamic_list',da).then(function(res){
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    that.loaded=false;
                    if(res.data.code==0){
                        if(that.page==1){
                            that.dynamic=res.data.info;
                        }else {
                            that.dynamic=that.dynamic.concat(res.data.info);
                        }
                        that.page= res.data.info.length == 0 ? that.page : (that.page + 1);
                        that.down=res.data.info.length < 10 ? false : true;
                    }else{
                        layer.open({content: res.data.msg,skin: 'msg',time: 2});
                    }
                },function(res){
                    that.loaded=false;
                    that.bug=true;
                });
            },
            show_content:function (c_con,title) {
                var ta=this;
                ta.loaded=true;
                ta.show_menu=false;
                ta.show_add_dynamic=false;
                ta.show_img_choose=false;
                ta.show_bus_content=true;
                ta.c_con=c_con;
                ta.c_title=title;
                ta.loaded=false;
            },
            show_part_:function (id) {
                var ta=this;
                ta.loaded=true;
                $("p[name="+id+"]")[1].style.display='none';
                $("p[name="+id+"]")[0].style.display='block';
                $("div[id="+id+"]")[0].style.display='none';
                $("div[name="+id+"]")[0].style.display='none';
                ta.loaded=false;
            },
            show_all_:function (id) {
                var ta=this;
                ta.loaded=true;
                $("p[name="+id+"]")[0].style.display='none';
                $("p[name="+id+"]")[1].style.display='block';
                $("div[id="+id+"]")[0].style.display='block';
                $("div[name="+id+"]")[0].style.display='block';
                ta.loaded=false;
            },
            add_comment:function (like_staff,mch_id,id) {
                var ta=this;
                ta.loaded=true;
                ta.show_menu=true;
                ta.sub_mch_id=mch_id;
                ta.sub_user_id=like_staff;
                ta.sub_comment_id=id;
                ta.show_add_comment=true;
                ta.loaded=false;
            },
            go_back_:function () {
                var ta=this;
                ta.loaded=true;
                ta.show_menu=true;
                ta.type=1;
                ta.show_img_choose=true;
                ta.show_bus_content=true;
                ta.show_add_comment=false;
                ta.show_add_dynamic=false;
                ta.loaded=false;
            },
            dynamic_del:function (id) {
                var that=this;
                that.loaded=true;
                this.$http.get(url+'People/dynamic_del',{uid:__UID__,id:id}).then(function(res){
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    that.loaded=false;
                    if(res.data.code==0){
                        $("div[data-show="+id+"]").hide();
                        layer.open({content: "删除成功",skin: 'msg',time: 2});
                    }else{
                        layer.open({content: res.data.msg,skin: 'msg',time: 2});
                    }
                },function(res){
                    that.loaded=false;
                    that.bug=true;
                });
            },
            give_like:function (like_staff,mch_id,id,k) {
                var that=this;
                that.loaded=true;
                this.$http.get(url+'People/dynamic_like',{mch_id:mch_id,user_id:like_staff,id:id}).then(function(res){
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    that.loaded=false;
                    if(res.data.code==0){
                        if(res.data.ok==1) {
                            layer.open({content: "点赞成功", skin: 'msg', time: 2});
                        }
                        if(res.data.ok==2) {
                            layer.open({content: "取消成功", skin: 'msg', time: 2});
                        }
                        that.page=1;
                        that.get_list();
                        that.show_menu=true;
                    }else{
                        layer.open({content: res.data.msg,skin: 'msg',time: 2});
                    }
                },function(res){
                    that.loaded=false;
                    that.bug=true;
                });
            },
            sub_commen:function () {
                var that=this;
                that.loaded=true;
                this.$http.get(url+'People/dynamic_sub',{mch_id:that.sub_mch_id,user_id:that.sub_user_id,id:that.sub_comment_id,comment:that.user_add_comments}).then(function(res){
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    that.loaded=false;
                    if(res.data.code==0){
                        layer.open({content: "评论成功",skin: 'msg',time: 2});
                        that.page=1;
                        that.get_list();
                        that.show_add_comment=false;
                        that.show_menu=true;
                    }else{
                        layer.open({content: res.data.msg,skin: 'msg',time: 2});
                    }
                },function(res){
                    that.loaded=false;
                    that.bug=true;
                });
            },
            GetWxConfig:function(){
                if(this.wx_config==0){
                    var wxurl=window.location.href;
                    this.$http.get(url + 'Userinfo/GetWxConfig', {
                        url: wxurl,
                        uid: __UID__
                    }).then(function (res) {
                        if (typeof res.data == 'string') {
                            res.data = json_parse(res.data);
                        }
                        if (res.data.code == 0) {

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
                                    'downloadImage'
                                ] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
                            });
                            this.wx_config=1;
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
            previewImage:function(url,k,n){
                var urls=[];
                if(n==1){
                     urls=this.dynamic[k].user_img;
                }else{
                    urls=[k];
                }

                wx.previewImage({
                    current:url, // 当前显示图片的http链接
                    urls: urls // 需要预览的图片http链接列表

                });
            },

            upload_pic:function(pic,obj){
                this.$http.get(url + 'People/upload_pic', {
                    pic_path:pic
                }).then(function (res) {
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    typeof obj == "function" && obj(res.data.info)
                })
            },
            //微信
            ChooseImage:function(){
                var that=this;
                var count=9-this.pic_arr_count;
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
                        that.all_ids=res_a.localIds;
                        vue_obj.is_obj=true;
                        var pic_data_obj=vue_obj.pic_data;
                        that.xunhuan(0,pic_data_obj,server_count);
                    },
                    fail:function(t){
                        msg(t.errMsg);
                    }
                });
            },
            dodo:function (do_num2) {
                var that=this;
                vue_obj.is_obj=true;
                var pic_data_obj=vue_obj.pic_data;
                if(that.all_ids.length==1){
                    that.xunhuan(a, pic_data_obj, server_count);
                }else {
                    if(do_num2<that.all_ids.length){
                        var a=localIds[0]
                        localIds.splice(0,1);
                        setTimeout(function () {
                            that.xunhuan(a, pic_data_obj, server_count);
                            that.dodo(localIds);
                        },1000);
                    }
                }
            },
            xunhuan:function (num,pic_data_obj,server_count) {
                var that=this;
                wx.uploadImage({
                    localId: that.all_ids[num], // 需要上传的图片的本地ID，由chooseImage接口获得
                    isShowProgressTips: 1, // 默认为1，显示进度提示
                    success: function (res_c) {
                        var serverId = res_c.serverId; // 返回图片的服务器端ID
                        that.upload_pic(serverId,function(t){
                            pic_data_obj[serverId]=t;
                            vue_obj.pic_data={};
                            vue_obj.pic_data=pic_data_obj;
                        })
                        vue_obj.pic_arr_count=vue_obj.pic_arr_count+1;
                        vue_obj.is_obj=true;
                        var pic_data_obj=vue_obj.pic_data;
                        ++server_count;
                        if(do_num<that.all_ids.length-1) {
                            do_num=num+1;
                            that.xunhuan(do_num, pic_data_obj, server_count);
                        }
                    }
                });
            },
            DelPic:function(key){
                var pic_data_obj=vue_obj.pic_data;
                delete pic_data_obj[key];
                vue_obj.pic_data={};
                vue_obj.pic_data=pic_data_obj;
                vue_obj.pic_arr_count=vue_obj.pic_arr_count-1;
            },
            gagagaga:function () {
                var that=this;
                that.loaded=true;
                that.show_menu=false;
                that.type=9;
                that.show_add_dynamic=true;
                that.loaded=false;
            },
            //发动态
            sub_message:function () {
                var that=this;
                that.loaded=true;
                var serverId_arr=[];
                for ( var key in that.pic_data){
                    serverId_arr.push(that.pic_data[key]);
                }
                if(that.shaya_content==''){
                    msg('内容不能为空');
                    return;
                }
                this.$http.get(url+'People/send_comment',{uid:__UID__,shaya_content:that.shaya_content,shaya_sid:JSON.stringify(serverId_arr)}).then(function(res){
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    that.loaded=false;
                    if(res.data.code==0){
                        layer.open({content: "发布成功",skin: 'msg',time: 2});
                        that.page=that.page-1;
                        that.get_list();
                        that.show_add_comment=false;
                        that.show_add_dynamic=false;
                        that.show_menu=true;
                        that.shaya_content='';
                        that.pic_data={};
                        that.pic_arr_count=0;
                        that.type=1;
                    }else{
                        layer.open({content: res.data.msg,skin: 'msg',time: 2});
                    }
                },function(res){
                    that.loaded=false;
                    that.bug=true;
                });
            },
            touchStart(e) {
                this.startY = e.targetTouches[0].pageY;
                this.touching = true;
            },
            touchMove(e) {
                if(this.touching){
                    this.top =  e.changedTouches[0].pageY-this.startY;
                }
            },
            touchEnd(e) {
                if (!this.enableRefresh) return
                this.touching = false;
                this.top=0;
                if(e.changedTouches[0].pageY-this.startY>60){

                    if(this.type==1){
                        this.index_data=[],
                            this.page= 1,
                            this.down=true;
                        this.get_list();
                    }else if(this.type==3){
                        this.index_people=[],
                            this.page_p= 1,
                            this.down_p=true;
                        this.get_list3();
                    }
                }else if(this.startY-e.changedTouches[0].pageY>60){

                    if(this.type==1){
                        if(this.down){
                            this.get_list();
                        }
                    }else if(this.type==3){
                        if(this.down_p){
                            this.get_list3();
                        }
                    }
                }
            },
        },
    });
};
function json_parse(str) {
    var jsonStr = str;
    jsonStr = jsonStr.replace(" ", "");
    if (typeof jsonStr != 'object') {
        jsonStr = jsonStr.replace(/\ufeff/g, "");//重点
        var obj = JSON.parse(jsonStr);
        return obj
    }
};