var vue_obb='';
var theRequest = GetRequest();
var __UID__='',MCHID='',histor='-1';
histor=theRequest["histor"];
if(typeof(histor)=="undefined"){
    histor='-1';
}
window.onload=function(){
    __UID__ = theRequest["uid"];
    var UID=theRequest["UID"];
    MCHID= theRequest["mch_id"];
    var domain = window.location.host;
    var path_name=window.location.pathname;
    var child_name=path_name.split("/addons");
    var url="https://"+domain+child_name[0]+"/addons/yb_mingpian/core/index.php?mch_id="+MCHID+"&s=/web/";
    vue_obb=new Vue({
        el:'#app',
        data:{
            //下拉刷新
            top: 0,
            histor: histor,
            all_num:10,
            startY: 0,
            is_follow: 2,
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
            hidden:-1,//选项卡人，信息展开折叠
            user_id:0,
            iiiid:'',
            detail_id:'',
            user_detail:[],
            select1:"4",
            select_item:[
                {text:"最后跟进时间",value:"3"},
                {text:"最后活动时间",value:"4"},
                {text:"工作交接",value:"5"},
                {text:"扫码",value:"6"},
                {text:"收藏",value:"7"}
            ],
            follow:[],
            follow_time:"近期",
            follow_lang:[],
            nidaye:[],
            now_select:"最后活动时间",
            lang_check:"",
            lang_class:"",
            cache_img:'',
            search_text:'',
            follow_select:false,
            follow_send:false,
            follow_msg:false,
            show_search:false,
            xiuhudong:false,
            xiubiaodan:false,
            xiufenxi:false,
            xiugenjin:true,
            wadaxiwahudong:false,
            wadaxiwagenjin:true,
            wadaxiwafenxi:false,
            wadaxiwabiaodan:false,
            now:{
                success50:0,
                success80:0,
                success99:0,
                success100:0,
                kfxqzb:{me:15,product:5,company:20},
                khhyd:{},
                khhd:[100,50,30,70,160,60],
                khhycs:{}
            },
            list:{},
            skin_a:"blue",
            deal_page:0,
            deal_data:[],
            deal_all:0,
            deal_show:false,
            deal_searh:'',
            is_new_deal:false
        },
        created:function(){
            var that=this;
            if (UID){
                that.statis(UID);
            }else {
                that.get_list(that.is_follow);
            }
            // that.get_skin();
        },
        watch : {
            type:function(val) {
                if(val==2){
                }else if(val==3){
                }
            }
        },
        methods:{
            url:function(e){
                if (e=='news_one'){
                    window.location.href =  "news_ai.html?uid="+__UID__+"&mch_id="+MCHID+"&user_id="+this.user_detail['user_id'];
                }else {
                    window.location.href = e + ".html?uid=" + __UID__+"&mch_id="+MCHID;
                }
            },
            //统计
            deal_list:function () {
                var ta=this;
                ta.deal_show=true;
                ta.page=1;
                ta.index_data=[];
                ta.search_text='';
                ta.type=1;
                ta.show_search=false;
                if (ta.deal_searh!=''){
                    ta.deal_page=1;
                    ta.deal_data=[];
                }
                this.$http.get(url+"People/deal_list",{mch_id:MCHID,page:ta.deal_page,uid:__UID__,sear:ta.deal_searh}).then(function (res) {
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                        ta.deal_data=ta.deal_data.concat(res.data.info);
                        ta.deal_page= res.data.num == 0 ? ta.deal_page : (ta.deal_page + 1);
                        ta.down=res.data.num< 10 ? false : true;
                        ta.deal_all=res.data.deal_all;
                    }
                })
            },
            statis:function (id) {
                var ta=this;
                ta.loaded=true;
                if (id==''){
                    id=ta.iiiid;
                }else {
                    ta.user_id=id;
                }
                ta.type=3;
                ta.wadaxiwahudong=false;
                ta.wadaxiwagenjin=true;
                ta.wadaxiwafenxi=false;
                ta.wadaxiwabiaodan=false;
                ta.xiufenxi=false;
                ta.xiugenjin=true;
                ta.xiuhudong=false;
                ta.xiubiaodan=false;
                this.$http.get(url+'People/get_user_statis',{user_id:id,staff_user_id:__UID__,mch_id:MCHID}).then(function (res) {
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    ta.loaded=false;
                    if(res.data.code==0){
                        ta.follow=res.data.info;
                        ta.user_detail=res.data.eazy;
                        ta.follow_lang=res.data.follow_lang;
                    }else{
                        layer.open({content:res.data.msg,skin: 'msg',time: 2});
                    }
                },function (res) {
                    ta.type=1;
                    ta.loaded=false;
                    ta.bug=true;
                });
            },
            del_follow:function(id){
                var ta=this;
                // ta.loaded=true;
                var data={id:id};
                this.$http.get(url+'People/del_follow',data).then(function (res) {
                    $("#dddd"+id).hide();
                    msg('删除成功');
                })
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
                // ta.get_list();
            },
            //liebiao
            get_list:function(f) {
                var that=this;
                that.loaded=true;
                that.show_search=false;
                that.deal_show=false;
                that.type=1;
                that.deal_page=1;
                that.deal_data=[];
                if(f==3){
                    f=that.is_follow;
                }
                if(f!=that.is_follow){
                    that.page=1;
                    that.index_data=[];
                    that.is_follow=f;
                }
                if (that.search_text!=''){
                    that.page=1;
                    that.index_data=[];
                }
                var data={uid: __UID__,page:that.page,type:that.select1,search_text:that.search_text,is_follow:f,mch_id:MCHID};
                this.$http.get(url+'People/get_user_list',data).then(function(res){
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    that.loaded=false;
                    if(res.data.code==0){
                        that.index_data=that.index_data.concat(res.data.info);
                        that.page= res.data.info.length == 0 ? that.page : (that.page + 1);
                        that.down=res.data.info.length < 100 ? false : true;
                        that.follow_lang=res.data.follow_lang;
                    }
                },function(res){
                    that.loaded=false;
                    that.bug=true;
                });
            },
            //用户详情
            get_user_detail:function(id){
                var ta=this;
                ta.loaded=true;
                ta.type=2;
                ta.detail_id=id;
                ta.cache_img=ta.user_detail['user_headimg'];
                this.$http.get(url+'People/get_user_detail',{id:id}).then(function(res){
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    ta.loaded=false;
                    if(res.data.code==0){
                        ta.user_detail=res.data.info;
                        ta.user_detail['user_headimg']=ta.cache_img;
                    }else{
                        layer.open({content: res.data.msg,skin: 'msg',time: 2});
                    }
                },function (res) {
                    ta.type=3;
                    ta.loaded=false;
                    ta.bug=true;
                });
            },
            //提交修改
            save_info:function () {
                var ta=this;
                ta.loaded=true;
                var fo=ta.user_detail.from;
                var sa=ta.user_detail.sax;
                var num=ta.user_detail.num;
                ta.cache_img=ta.user_detail['user_headimg'];
                this.$http.post(url+'People/save_user',ta.user_detail,{emulateJSON:true}).then(function (res) {
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    ta.loaded=false;
                    if(res.data.code==0){
                        ta.user_detail=res.data.info;
                        ta.user_detail.from=fo;
                        ta.user_detail.sax=sa;
                        ta.user_detail.num=num;
                        ta.user_detail['user_headimg']=ta.cache_img;
                        layer.open({content: '修改成功',skin: 'msg',time: 2});
                    }else{
                        layer.open({content: res.data.msg,skin: 'msg',time: 2});
                    }
                },function (res) {
                    ta.type=1;
                    ta.loaded=false;
                    ta.bug=true;
                })
            },
            is_deal:function (f) {
                var ta=this;
                this.$http.post(url+'People/do_deal',{uid: __UID__,user_id:f}).then(function (res) {
                    res.data = json_parse(res.data);
                    if(res.data.code==1){
                        ta.user_detail.is_new_deal=true;
                        layer.open({content: '已成交',skin: 'msg',time: 2});
                    }else {
                        // alert(res.data.code);
                        // alert(ta.user_detail.is_new_deal);
                        ta.user_detail.is_new_deal=false;
                        layer.open({content: '已修改',skin: 'msg',time: 2});
                    }
                },function (res) {
                    ta.type=1;
                    ta.loaded=false;
                    ta.bug=true;
                })
            },
            change_gender:function (x) {
                var ta=this;
                ta.loaded=true;
                ta.user_detail.gender=x==1?2:1;
                ta.user_detail.sax=x==1?"女":"男";
                ta.loaded=false;
            },
            //跟进操作
            change_lang:function (x,y) {
                var ta=this;
                ta.loaded=true;
                ta.follow_select=false;
                ta.lang_class=x;
                ta.lang_check=y;
                ta.loaded=false;
            },
            genjin_send:function () {
                var ta=this;
                ta.loaded=true;
                ta.follow_send=true;
                ta.type=5;
                ta.loaded=false;
            },
            genjin_select:function () {
                var ta=this;
                ta.loaded=true;
                ta.follow_select=!ta.follow_select;
                ta.type=5;
                ta.loaded=false;
            },
            sub_follow:function (id) {
                var ta=this;
                ta.loaded=true;
                if(ta.lang_check=='') {
                    ta.follow_msg = true;
                }else {
                    ta.user_detail.lang_check=ta.lang_check;
                    this.$http.post(url+'People/save_follow',ta.user_detail,{emulateJSON:true}).then(function(res){
                        layer.open({content: '跟进成功',skin: 'msg',time: 2});
                    },function(){});
                    ta.follow_select=false;
                    ta.follow_send=false;
                    ta.follow_msg=false;
                    ta.lang_check='';
                    ta.type=3;
                    this.statis(ta.user_detail.user_id);
                }
                ta.loaded=false;
            },
            is_done:function () {
                var ta=this;
                ta.loaded=true;
                ta.follow_msg=false;
                ta.loaded=false;
            },
            search_show:function () {
                var ta=this;
                ta.loaded=true;
                ta.type=5;
                ta.show_search=true;
                ta.loaded=false;
            },
            go_back_:function () {
                var ta=this;
                ta.loaded=true;
                ta.type=3;
                if(ta.follow_send){
                    ta.follow_send=false;
                }
                ta.loaded=false;
            },
            send_message:function (id,staff_id) {
                var ta=this;
                ta.loaded=true;
                ta.type=3;
                ta.loaded=false;
            },
            get_liulan:function () {
                var ta=this;
                ta.loaded=true;
                ta.wadaxiwahudong=true;
                ta.wadaxiwagenjin=false;
                ta.wadaxiwafenxi=false;
                ta.wadaxiwabiaodan=false;
                ta.user_detail.lang_check=ta.lang_check;
                this.$http.get(url+'People/get_liulan',{user_id:ta.user_detail['user_id'],staff_id:ta.user_detail['staff_id']}).then(function(res){
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    if(res.data.code==0) {
                        ta.page = res.data.info.length == 0 ? ta.page : (ta.page + 1);
                        ta.down = res.data.info.length < 10 ? false : true;
                        ta.nidaye = res.data.info;
                    }else {
                        layer.open({content: res.data.msg,skin: 'msg',time: 2});
                    }
                },function(){});
                ta.xiufenxi=false;
                ta.xiugenjin=false;
                ta.xiuhudong=true;
                ta.xiubiaodan=false;
                ta.type=3;
                ta.loaded=false;
            },
            get_genjina:function () {
                var ta=this;
                ta.loaded=true;
                ta.wadaxiwahudong=false;
                ta.wadaxiwagenjin=false;
                ta.wadaxiwafenxi=true;
                ta.wadaxiwabiaodan=false;
                ta.xiufenxi=true;
                ta.xiubiaodan=false;
                ta.xiugenjin=false;
                ta.xiuhudong=false;
                ta.wadaxiwa="fenxi";
                this.$http.get(url+'Webindex/user_chart',{uid:__UID__,type:0,user_id:ta.user_id}).then(function(res){
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    ta.loaded=false;
                    if (res.data.code == 0) {
                        ta.now.khhd = res.data.info.khhd;
                        ta.now.kfxqzb = res.data.info.kfxqzb;
                        ta.now.khhycs=res.data.info.khhycs;
                        setTimeout(function () {
                            ta.khhycs();
                            ta.kfxqzb();
                            ta.khhd();
                        },1000);
                    } else {
                        ta.loaded = false;
                    }
                },function(res){
                    ta.loaded=false;
                });
                ta.loaded=false;
            },
            kfxqzb:function() {
                //数据
                var data = this.now.kfxqzb;
                // 基于准备好的dom，初始化echarts实例
                var myChart = echarts.init(document.getElementById('khxqzb'));
                // 指定图表的配置项和数据
                var option = {
                    tooltip: {},
                    legend: {
                        y:'bottom',
                        itemWidth:16,
                        itemHeight:14,
                        icon:'circle',
                        data:['对我感兴趣','对产品感兴趣','对公司感兴趣']
                    },
                    color:['#ec6a34','#f2af4a','#68c1d0'],
                    series: [{
                        label : {
                            normal: {
                                formatter: '{d}%',
                                textStyle: {
                                    fontWeight: 'normal',
                                    fontSize: 15
                                }
                            }
                        },
                        radius : '50%',
                        center: ['45%','35%'],
                        name: '销量',
                        type: 'pie',
                        data: [
                            {name: '对我感兴趣', value: data.me},
                            {name: '对产品感兴趣', value: data.product},
                            {name: '对公司感兴趣', value: data.company}
                        ]
                    }]
                };
                // 使用刚指定的配置项和数据显示图表。
                myChart.setOption(option);
            },
            khhycs:function() {
                var data=this.now.khhycs;
                var date_arr=[];
                var value_arr=[];
                for (var key in data) {
                    date_arr.push(key);
                    value_arr.push(data[key]);
                }
                // 基于准备好的dom，初始化echarts实例
                var myChart = echarts.init(document.getElementById('khhyd'));
                // 指定图表的配置项和数据
                var option = {
                    tooltip: {},
                    grid:{
                        top:"10%",
                        right:"10%",
                        bottom:"10%",
                        left:"10%"
                    },
                    xAxis: {
                        gridIndex:0,
                        data: date_arr,
                        axisTick: {
                            interval: 0,
                        },
                        boundaryGap:false,
                        splitArea:{
                            show:true,
                        },
                        axisLabel:{
                            textStyle:{
                                fontSize:11
                            }
                        }
                    },
                    yAxis: {
                        type:"value"
                    },
                    series: [{
                        type: 'line',
                        smooth: !0,
                        symbol: "circle",
                        symbolSize: 8,
                        data: value_arr,
                        itemStyle: {
                            normal: {
                                label: {
                                    show: !0,
                                    fontSize: 15,
                                    color: "#0d1e39",
                                    fontWeight: "540",
                                    textBorderColor: "#fff",
                                    textBorderWidth: 5
                                },
                                borderColor: "rgb(255,255,255)",
                                borderWidth: 2,
                                color: "#1c3d71",
                                lineStyle: {color: "#4978C3", width: 2}
                            }
                        },
                        textStyle: {color: "#54657E"}
                    }]
                };
                // 使用刚指定的配置项和数据显示图表。
                myChart.setOption(option);
            },
            khhd:function() {
                //["保存电话","拨打电话","添加印象","咨询产品","评论","点赞"]
                var data = this.now.khhd;
                // 基于准备好的dom，初始化echarts实例
                var myChart = echarts.init(document.getElementById('khhd'));
                // 指定图表的配置项和数据
                var option = {
                    tooltip: {},
                    grid:{
                        top:"10%",
                        right:"20%",
                        bottom:"10%",
                        left:"20%"
                    },
                    xAxis : [
                        {
                            show:true,
                            type : 'value',
                            boundaryGap : [0, 0],
                            axisTick: {
                                show: false
                            },
                            axisLabel:{
                                show: false
                            },
                            axisLine: {lineStyle: {color: "#d3d3d3"}},
                        }
                    ],
                    yAxis: {
                        axisLine: {lineStyle: {color: "#d3d3d3"}},
                        data:["保存电话","拨打电话","添加印象","咨询产品","评论","点赞"],
                        axisTick: {
                            show: false
                        },
                        axisLabel:{
                            color: "#54657E"
                        },
                    },
                    color:['#ec6a34','#f2af4a','#68c1d0'],
                    series: [{
                        type: 'bar',
                        data: data,
                        barWidth: 20,  //柱宽度
                        itemStyle : {
                            normal : {
                                // 随机显示颜色
                                color:function(d){return "#"+Math.floor(Math.random()*(256*256*256-1)).toString(16);},
                                // 定制显示（按顺序）
        //                        color: function(params) {
        //                            var colorList = ["#B2B2B2", "#B2B2B2", "#B2B2B2", "#B2B2B2", "#fec846", "#FEAB2B"];
        //                            return colorList[params.dataIndex]
        //                        },
                                label: {
                                    show: true,
                                    position: 'right',
                                }
                            },
                        },
                        textStyle: {color: "#54657E"}
                    }]
                };
                // 使用刚指定的配置项和数据显示图表。
                myChart.setOption(option);
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
                    console.log('下拉刷新中')
                }else if(this.startY-e.changedTouches[0].pageY>60){
                    console.log('上拉加载更多');
                    if(this.type==1){
                        if(this.down){
                            if(this.deal_show){
                                this.deal_list();
                            }else {
                                this.get_list(this.is_follow);
                            }
                        }
                    }
                }
            },
        },
    });
};
$(document).ready(function (e) {
    var counter = 0;
    if (window.history && window.history.pushState) {
        $(window).on('popstate', function () {
            window.history.pushState('forward', null, '#');
            counter++;
            if(counter>0){
                window.history.forward(1);
                console.log(histor);
                if(histor=="-1") {
                    if (vue_obb.type == 3) {
                        vue_obb.type = 1;
                    } else {
                        window.location.href = "people.html?uid=" + __UID__ + "&mch_id=" + MCHID;
                    }
                }else {
                    window.location.href = histor+".html?uid=" + __UID__ + "&mch_id=" + MCHID;
                }
            }
        })
    }
    window.history.pushState('forward', null, '#');
    window.history.forward(1);
})
