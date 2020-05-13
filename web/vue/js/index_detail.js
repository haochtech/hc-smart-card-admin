window.onload=function(){
   
    var theRequest = GetRequest();
    var __UID__ = theRequest["uid"];
 	var op_type= theRequest["op_type"];
 	var op_name= unescape(theRequest["op_name"]);
 	var status={
        		'1':'查看',
		        '2':'转发',
		        '3':'复制',
		        '4':'保存',
		        '5':'拨打',
                '6':'浏览',
                '7':'觉得'
        	};
 	document.title = status[op_type]+op_name;
   var domain = window.location.host;
    var MCHID= theRequest["mch_id"];
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
            bug:false,
            loaded:false,
            show:true,
            op_type:op_type,
            index_data:[],
            count:0,
            page:1,
            down:true,
           	str_time:'',
            end_time:'',
            show_date:false,//日期是否显示
            
        },
        created:function(){
            var that=this;
            if(op_type<7){
                that.get_list();
            }else{
                that.get_list_like();
            }

        },
        methods:{
            //时间
            get_list:function() {
                var that=this;
                that.loaded=true;
                this.$http.post(url+'Webindex/index_detail',{
                	uid:__UID__,
                	str_time:that.str_time,
                    end_time:that.end_time,
                    op_type:op_type,
                    op_name:op_name,
                	page:that.page},{emulateJSON:true}).then(function(res){
                    console.log(res);
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    that.loaded=false;
                    if(res.data.code==0){
                        that.index_data=that.index_data.concat(res.data.info.info);
                            that.page= res.data.info.info.length == 0 ? that.page : (that.page + 1);
                            that.down=res.data.info.info.length < 10 ? false : true;
                            that.count=res.data.info.count;
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
            //觉得靠谱
            get_list_like:function() {
                var that=this;
                that.loaded=true;
                this.$http.post(url+'Webindex/index_detail_like',{
                    uid:__UID__,
                    str_time:that.str_time,
                    end_time:that.end_time,
                    page:that.page},{emulateJSON:true}).then(function(res){
                    console.log(res);
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    that.loaded=false;
                    if(res.data.code==0){
                        that.index_data=that.index_data.concat(res.data.info.info),
                            that.page= res.data.info.info.length == 0 ? that.page : (that.page + 1),
                            that.down=res.data.info.info.length < 10 ? false : true;
                        that.count=res.data.info.count;
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
                   that.str_time=str;
                    that.end_time=end;
                    that.page=1;
                    that.down=true;
                    that.index_data=[];
                    that.get_list();
               
            },
            //清除时间选择
            clear_time:function(){
                var that=this;
                    that.str_time='';
                    that.end_time='';
                    that.page=1;
                    that.down=true;
                    that.index_data=[];
                    that.get_list();
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
                if(e.changedTouches[0].pageY-this.startY>60){
                    console.log('下拉刷新中')
                        this.index_data=[],
                            this.page= 1,
                            this.down=true;
                        this.get_list();
                   
                }else if(this.startY-e.changedTouches[0].pageY>60){
                    console.log('上拉加载更多');
                        if(this.down){
                            this.get_list();
                        }
                    
                }
            },
        },
    });
};