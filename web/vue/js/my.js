window.onload=function(){
    var theRequest = GetRequest();
    var __UID__ = theRequest["uid"];
    var MCHID= theRequest["mch_id"];
    var domain = window.location.host;
    var path_name=window.location.pathname;
    var child_name=path_name.split("/addons");
    var url="https://"+domain+child_name[0]+"/addons/yb_mingpian/core/index.php?mch_id="+MCHID+"&s=/web/";
    var nv=new Vue({
        el:'#app',
        data:{
            //下拉刷新
            top: 0,
            startY: 0,
            touching: false,
            enableRefresh:true,
            type:1,
            bug:false,
            loaded:false,
            show:true,
            userinfo:{},
            wd_news:0//未读消息
           
        },
        created:function(){
            var that=this;
            that.loaded=true;
            this.$http.get(url+'Webindex/UserInfo',{uid:__UID__}).then(function(res){

                if (typeof res.data == 'string') {
                    res.data = json_parse(res.data);
                }
                that.loaded=false;
                if(res.data.code==0){
                   that.userinfo=res.data.info;
                }else{
                   msg(res.data.msg);
                }
            },function(res){
                that.loaded=false;
                that.bug=true;
            });
        },
        watch : {
            // type:function(val) {
            //     console.log(val)
            //     if(val==2){
            //         this.get_list2();
            //     }else if(val==3){
            //         if(this.index_people.length==0){
            //             this.get_list3();
            //         }
            //     }
            // }
        },
        methods:{
            get_skin:function () {
                var ta=this;
                this.$http.get(url+"People/get_skins",{mch_id:MCHID}).then(function (res) {
                    $(document).ready(function () {
                        $("#nnn").append('<link rel="stylesheet" href="./vue/css/'+res.data+'/my.css?v=1.15">');
                        $(".choosed_tab").parent().children(0).attr("src","vue/img/"+res.data+"/my2.png");
                    });
                })
            },
            url:function(e){
                console.log(e);
                if(e=="get_user_dynamic"){
                    window.location.href="message.html?uid="+__UID__+"&mch_id="+MCHID+"&USER_DYNAMIC=1";
                }else if(e=="poster2") {
                    window.location.href = e + ".html?uid=" + __UID__+"&mch_id="+MCHID+'&ranparam='+randomNum(10000,99999);
                }else{
                    window.location.href = e + ".html?uid=" + __UID__+"&mch_id="+MCHID;
                }
            },//未读消息
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
            userinfo:function(){
            	 var that=this;
                that.loaded=true;
                this.$http.get(url+'Webindex/UserInfo',{uid:__UID__}).then(function(res){
                 	console.log(res);
                     if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
	                that.loaded=false;
	                if(res.data.code==0){
	                   that.userinfo=res.data.info;
	                }else{
	                   msg(res.data.msg);
	                }
	            },function(res){
	                that.loaded=false;
	                that.bug=true;
	            });
            },
            poster_jx:function(){
                window.location.href = "jixie.html?uid=" + __UID__+"&hb=1&mch_id="+MCHID+'&ranparam='+randomNum(10000,99999);
            },poster_hb:function (e) {
                //msg(e + ".html?uid=" + __UID__+"&hb=1");
                window.location.href = e + ".html?uid=" + __UID__+"&hb=1&mch_id="+MCHID;
            },get_goods:function (e) {
                window.location.href = e + ".html?uid=" + __UID__+"&goods=1&mch_id="+MCHID;
            }
      
        }
    });
};
//生成从minNum到maxNum的随机数
function randomNum(minNum,maxNum){
    switch(arguments.length){
        case 1:
            return parseInt(Math.random()*minNum+1,10);
            break;
        case 2:
            return parseInt(Math.random()*(maxNum-minNum+1)+minNum,10);
            break;
        default:
            return 0;
            break;
    }
}