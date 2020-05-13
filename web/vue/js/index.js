window.onload=function(){
    var theRequest = GetRequest();
    var __UID__ = theRequest["uid"];
    var MCHID= theRequest["mch_id"];
    var domain = window.location.host;
    var path_name=window.location.pathname;
    var child_name=path_name.split("/addons");
    var url="https://"+domain+child_name[0]+"/addons/yb_mingpian/core/index.php?mch_id="+MCHID+"&s=/web/";


   var vue_obj= new Vue({
        el:'#app',
        data:{
            //下拉刷新
            top: 0,
            startY: 0,
            touching: false,
            enableRefresh:true,
            type:1,//1时间，2行为，3人
            bug:false,
            loaded:false,
            show:false,
            show_skin:false,
            show_jurisdiction:false,
            show_jurisdiction_msg:'该名片已被禁用，请联系公司管理员',
            //行为
            index_behavior:{},
            str_time_b:'',
            end_time_b:'',
            //时间
            index_data:[],
            page:1,
            down:true,
            //人
            index_people:[],
            page_p:1,
            down_p:true,
            str_time_p:'',
            end_time_p:'',
            hidden:-1,//选项卡人，信息展开折叠
            show_date:false,//日期是否显示
            wd_news:0,//未读消息
            static_data:{
                "show_7":"7天内被查看的行为统计",
                "show_zhi":"至",
                "show_error":"请求超时或网络出错,点击再次请求"
            }
        },
        created:function(){
            var that=this;
            that.get_list();
             var interval = setInterval(function () {
                      if (!that.wd_news && that.wd_news!=0) {
                        clearInterval(interval);
                      }
                      that.news_count();
                     }.bind(this), 2000);    
        },
        watch : {
            type:function(val) {
                console.log(val)
                if(val==2){
                    this.get_list2();
                }else if(val==3){
                    if(this.index_people.length==0){
                        this.get_list3();
                    }
                }
            }
        },
        methods:{
            get_skin:function () {
                var ta=this;
                this.$http.get(url+"People/get_skins",{mch_id:MCHID}).then(function (res) {
                    console.log(res)
                    $(document).ready(function () {
                        $("#nnn").append('<link rel="stylesheet" href="./vue/css/'+res.data+'/index.css?v=1.8">');
                        $(".choosed_tab").parent().children(0).attr("src","vue/img/"+res.data+"/home2.png");
                    });
                })
            },
            //未读消息
            news_count:function(){
                var that=this;
                 this.$http.get(url+'Webindex/wd_news',{uid:__UID__}).then(function(res){
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    if(res.data.code==0){
                        that.wd_news=res.data.info;
                    }
                });
            },
            //页面跳转
            url:function(e,f){
                f=f||'';
                if(e=="tonews"){
                    window.location.href = "news_ai.html?uid=" + __UID__ + "&mch_id=" + MCHID+"&user_id="+f;
                }else {
                    window.location.href = e + ".html?uid=" + __UID__ + "&mch_id=" + MCHID;
                }
            },
            //时间
            get_list:function() {
                var that=this;
                that.loaded=true;
                this.$http.get(url+'Webindex/index_time',{uid:__UID__,page:that.page}).then(function(res){

                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }

                    that.loaded=false;
                    if(res.data.code==0){
                        that.show=true;
                        that.index_data=that.index_data.concat(res.data.info),
                            that.page= res.data.info.length == 0 ? that.page : (that.page + 1),
                            that.down=res.data.info.length < 10 ? false : true;
                    }else{
                        that.show_jurisdiction=true;
                        that.show_jurisdiction_msg=res.data.msg;
                          layer.open({
                                    content: '账号未授权！'
                                    ,skin: 'msg'
                                    ,time: 2 //2秒后自动关闭
                                  });
                    }
                },function(res){
                    that.loaded=false;
                    that.bug=true;
                });
            },
            //行为
            get_list2:function() {
                var that=this;
                that.loaded=true;
                this.$http.get(url+'Webindex/index_behavior',{uid:__UID__,
                    str_time:that.str_time_b,
                    end_time:that.end_time_b
                }).then(function(res){
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    that.loaded=false;
                    if(res.data.code==0){
                        that.index_behavior=res.data.info;
                    }else{
                          layer.open({
                                    content: res.data.msg
                                    ,skin: 'msg'
                                    ,time: 2 //2秒后自动关闭
                                  });
                    }
                },function(res){
                    that.loaded=false;
                    that.bug=true;
                });
            },
            //人
            get_list3:function() {
                var that=this;
                that.loaded=true;
                this.$http.get(url+'Webindex/index_people',{uid:__UID__,
                    str_time:that.str_time_p,
                    end_time:that.end_time_p,
                    page:that.page_p}).then(function(res){
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    that.loaded=false;
                    if(res.data.code==0){
                        that.index_people=that.index_people.concat(res.data.info),
                            that.page_p= res.data.info.length == 0 ? that.page_p : (that.page_p + 1),
                            that.down_p=res.data.info.length < 10 ? false : true;
                    }else{
                          layer.open({
                                    content: res.data.msg
                                    ,skin: 'msg'
                                    ,time: 2 //2秒后自动关闭
                                  });
                    }
                },function(res){
                    that.loaded=false;
                    that.bug=true;
                });
            },
            //日期选择
            time_select:function(){
                var that=this;
                var y= $("#dateSelectorOne").attr('data-year');
                var m= $("#dateSelectorOne").attr('data-month');
                var d= $("#dateSelectorOne").attr('data-day');
                var y2= $("#dateSelectorTwo").attr('data-year');
                var m2= $("#dateSelectorTwo").attr('data-month');
                var d2= $("#dateSelectorTwo").attr('data-day');
                if(!y || !m ||!d){
                    msg('请选择开始日期');return;
                }
                if(!y2 || !m2 ||!d2){
                    msg('请选择结束日期');return;
                }
               var str=y+'-'+m+'-'+d;
               var end=y2+'-'+m2+'-'+d2;
               console.log(str);console.log(end);
               that.show_date=false;//关闭日期选择弹框
               if(that.type==3){
                    that.str_time_p=str;
                    that.end_time_p=end;
                    that.page_p=1;
                    that.down_p=true;
                    that.index_people=[];
                    that.get_list3();
               }else if(that.type==2){
                    that.str_time_b=str;
                    that.end_time_b=end;
                    that.index_behavior={};
                    that.get_list2();
               }
            },
            //清除时间选择
            clear_time:function(){
                var that=this;
                if(that.type==3){
                    that.str_time_p='';
                    that.end_time_p='';
                    that.page_p=1;
                    that.down_p=true;
                    that.index_people=[];
                    that.get_list3();
                }else if(that.type==2){
                    that.str_time_b='';
                    that.end_time_b='';
                   that.index_behavior={};
                    that.get_list2();
                }
            },
            //跳转至详情
            to_detail:function(s,n){
                window.location.href="index_detail.html?uid="+__UID__+"&mch_id="+MCHID+'&op_type='+s+'&op_name='+escape(n);
            },
            //跳转至客户详情
            to_cus_detail:function(u,k){
                window.location.href="people.html?histor=index_ai&uid="+__UID__+"&mch_id="+MCHID+'&UID='+u;
            },
            touchStart(e) {
                this.startY = e.targetTouches[0].pageY
                this.touching = true
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
                console.log(e.changedTouches[0].pageY-this.startY);
                if(e.changedTouches[0].pageY-this.startY>60){
                    console.log('下拉刷新中')
                    if(this.type==1){
                        this.index_data=[];
                            this.page= 1;
                            this.down=true;
                        this.get_list();
                    }else if(this.type==3){
                        this.index_people=[];
                            this.page_p= 1;
                            this.down_p=true;
                        this.get_list3();
                    }
                }else if(this.startY-e.changedTouches[0].pageY>60){
                    console.log('上拉加载更多');
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