window.onload = function () {
    var theRequest = GetRequest();
    var __UID__ = theRequest["uid"];
    var MCHID= theRequest["mch_id"];
    var u = theRequest["user_id"];
    var domain = window.location.host;
    var path_name=window.location.pathname;
    var child_name=path_name.split("/addons");
    var url="https://"+domain+child_name[0]+"/addons/yb_mingpian/core/index.php?mch_id="+MCHID+"&s=/web/";
    var vue_obj = new Vue({
        el: '#app',
        data: {
            //下拉刷新
            top: 0,
            startY: 0,
            touching: false,
            enableRefresh: true,
            type: 1,//1:聊天列表 ；2聊天室
            bug: false,
            loaded: false,
            show: true,
            //type==1时
            list: [],//好友列表
            //type==2时
            chat_footer_more: false,//底部发送图片是否显示
            innerText: '',//对话输入框文字
            pic_path: '',//图片预览地址
            serverId: '',
            news: [],
            u: 0,//客户id
            //消息待发区
            send_ok:false,
            new_show: false,
            news_state: 0,//1：发送中；2失败；0成功
            news_text: '',//消息内容
            userinfo: {},
            wd_news: 0,//未读消息
            post_type:1,
            show_huashu: false,//话术库显示
            huashu_list: [],
            index_huashu: 0,
            wx_config: false,
            back_type:1,
            go_backa:1
        },
        created: function () {
            var that = this;
            // that.get_skin();
            if (u) {
                that.u = u;
                that.back_type=2;
                that.type = 2;
                that.newslist();
                that.get_user_name(u);//修改标题名称
                this.$http.get(url + 'Webindex/UserInfo', {uid: __UID__}).then(function (res) {
                    that.loaded = false;
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    if (res.data.code == 0) {
                        that.userinfo = res.data.info;
                    } else {
                        layer.open({
                            content: res.data.msg
                            , skin: 'msg'
                            , time: 2 //2秒后自动关闭
                        });
                    }
                });
                //话术库
                that.huashu();
            } else {
                that.get_list();
                this.$http.get(url + 'Webindex/UserInfo', {uid: __UID__}).then(function (res) {
                    that.loaded = false;
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    if (res.data.code == 0) {
                        that.userinfo = res.data.info;
                    } else {
                        layer.open({
                            content: res.data.msg
                            , skin: 'msg'
                            , time: 2 //2秒后自动关闭
                        });
                    }
                });
                //未读消息
                that.news_count();
            }
        },
        watch: {
            type: function (val) {
                var that = this;
                if (val == 2) {
                    that.news=[];
                    that.get_user_name(that.u);//修改标题名称
                    var wxurl = window.location.href;
                    //GetWxConfig(__UID__, that, wxurl);
                    that.GetWxConfig1();
                    //话术库
                    that.huashu();
                    // that.newslist();
                    var interval = setInterval(function () {
                        if (this.type != 2) {
                            clearInterval(interval);
                        }
                        that.newslist();
                    }.bind(this), 1000);
                }
                if (val == 1) {
                    document.title='消息';
                    that.get_list();
                    var interval2 = setInterval(function () {
                        if (this.type != 1) {
                            clearInterval(interval2);
                        }
                        that.news_count();
                    }.bind(this), 2000);
                }
            },
            serverId: function (val) {
                if (val) {
                    this.send_msg();
                }
            },
            pic_path: function (val) {
                if (val) {
                }
            }
        },
        methods: {
            // get_skin:function () {
            //     var ta=this;
            //     this.$http.get(url+"People/get_skins",{mch_id:MCHID}).then(function (res) {
            //         $(document).ready(function () {
            //             $("#nnn").append('<link rel="stylesheet" href="./vue/css/'+res.data+'/news.css">');
            //         });
            //     })
            // },
            get_user_name:function(u){
                var that = this;
                this.$http.get(url + 'Webindex/user_name', {uid: u}).then(function (res) {
                    document.title=res.data;
                });
            },
            GetWxConfig1: function () {
                if (!this.wx_config) {
                    var wxurl = window.location.href;
                    this.$http.get(url + 'Userinfo/GetWxConfig', {
                        url: wxurl,
                        uid: __UID__
                    }).then(function (res) {
                        if (typeof res.data == 'string') {
                            res.data = json_parse(res.data);
                        }
                        if (res.data.code == 0) {
                            wx.config({
                                debug: false,
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
                            this.wx_config = true;
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
            //未读消息
            news_count: function () {
                var that = this;
                this.$http.get(url + 'Webindex/wd_news', {uid: __UID__}).then(function (res) {
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    if (res.data.code == 0) {
                        that.wd_news = res.data.info;
                    }
                });
            },
            //选择图片
            ChooseImage: function () {
                ChooseImage(this, 1);
                this.post_type = 2;
                this.new_show = true;
                this.news_state = 1;
                this.chat_footer_more = false;
            },
            //话术库分类+信息
            huashu: function () {
                var that = this;
                this.$http.get(url + 'Webindex/wordpool', {uid: __UID__}).then(function (res) {
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    if (res.data.code == 0) {
                        that.huashu_list = res.data.info;
                    }
                });
            },
            //选中话术
            select_huashu: function (e) {
                this.innerText = e;
                this.show_huashu = false;//话术库不显示
            },
            url: function (e) {
                window.location.href = e + ".html?uid=" + __UID__+"&mch_id="+MCHID;
            },
            go_back: function () {
                if(this.back_type==2){
                    window.location.href="people.html?uid="+__UID__+"&mch_id="+MCHID+'&UID='+this.u;
                }else{
                    this.type =1;
                }

            },
            to_detail: function (u) {//u:客户id
                this.u = u;
                this.type = 2;
                // this.newslist();
                // console.log(s);
                // console.log(u);
            },
            previewImage:function(url){
                wx.previewImage({
                    current:url, // 当前显示图片的http链接
                    urls: [url] // 需要预览的图片http链接列表

                });
            },
            get_list: function () {//聊天列表
                var that = this;
                that.loaded = true;
                this.$http.get(url + 'Webindex/NewsList', {uid: __UID__}).then(function (res) {
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    that.loaded = false;
                    if (res.data.code == 0) {
                        that.list = res.data.info;
                    } else {
                        layer.open({
                            content: res.data.msg
                            , skin: 'msg'
                            , time: 2 //2秒后自动关闭
                        });
                    }
                }, function (res) {
                    that.loaded = false;
                    that.bug = true;
                });
            },
            for__back: function () {//聊天列表
                this.$http.get(url + 'Webindex/NewsList', {for_back:1}).then(function (res) {
                }, function (res) {
                });
            },
            newslist:function(){//消息列表
                var that = this;
                that.loaded = true;
                var list = that.news;
                this.$http.get(url + 'Webindex/timelynews', {user_id: that.u, uid: __UID__}).then(function (res) {
                    // console.log(res);
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    that.loaded = false;
                    if (res.data.code == 0) {
                        //if (list.length != res.data.info.length) {
                        if (that.send_ok) {
                            that.new_show = false;
                            that.news_state = 0;
                            that.news_text = '';
                            that.post_type=1;
                            that.serverId='';
                            that.send_ok=false;
                        }
                        that.news = res.data.info;

                        that.$nextTick(function (){
                            scrollToEnd(that.news.length-1);
                            that.go_backa=2;
                        });

                    } else {
                        layer.open({
                            content: res.data.msg
                            , skin: 'msg'
                            , time: 2 //2秒后自动关闭
                        });
                    }
                }, function (res) {
                    that.loaded = false;
                    that.bug = true;
                });
            },
            //发送消息
            send_msg: function(){
                var that = this;
                var type=that.post_type;
                that.show_huashu = false;
                that.chat_footer_more = false;
                if(type==2){
                    post_message = that.serverId;
                }else{
                    post_message =that.innerText.replace(/\s*/g, "");
                }
                if (post_message == '') {
                    msg('内容不能为空');
                    return;
                }
                that.news_text = post_message;
                that.new_show = true;
                that.news_state = 1;
                that.innerText = '';
                this.$http.get(url + 'Webindex/addnews', {
                    user_id: that.u,
                    uid: __UID__,
                    post_type:type,
                    post_message: post_message
                }).then(function (res) {
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    if (res.data.code == 0) {
                        that.send_ok=true;
                        //that.new_show = false;
                        //that.news_state = 0;
                        //that.news_text = '';
                    } else {
                        that.news_state = 2;
                        layer.open({
                            content: res.data.msg
                            , skin: 'msg'
                            , time: 2 //2秒后自动关闭
                        });
                    }
                }, function (res) {
                    that.news_state = 2;
                    if(type==2){
                        msg('图片发送失败');
                    }else{
                        msg('消息发送失败');
                    }
                });
            },
            //取消弹框
            clear_alert: function () {
                // this.show_huashu=false;
                this.chat_footer_more = false;
            }
        },
    });
//滚动到底部
    function scrollToEnd(count) {
        //document.location.replace(getFilterUrl(count));
        window.location.hash = "#info"+count;
    }
    //function getFilterUrl(hash) {
    //    return document.location.protocol + '//' + document.location.host + document.location.pathname + document.location.search + '#' + hash;
    //}
    //$(document).ready(function (e) {
    //    window.onpopstate=function()
    //    {
    //        //// if (window.history && window.history.pushState) {
    //        ////     $(window).bind("popstate", function (e) {
    //        ////         console.log(1);
    //        ////         console.log(e);
    //        ////         window.addEventListener("popstate", function (e) {
    //        //            console.log(2);
    //        //            // console.log(e);
    //        //            window.location.href = "news_ai.html?uid=" + __UID__ + "&mch_id=" + MCHID;
    //        //        // }, false);
    //        //    // })
    //        //// }
    //    }
    //})
};