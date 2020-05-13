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
            type:1,//1；列表，2添加
            bug:false,
            loaded:false,
            show:true,
            
            huashu_list:[],
            index_huashu:0,
            talk_value:'',
            inputCnt: 0
        },
        created:function(){
            var that=this;
            //话术库
            that.huashu();
        },
        watch : {
            type:function(val) {
                var that=this;
                if(val==1){
                	document.title="话术管理";
                    //话术库
                    that.huashu();
                }else if(val==2){
				document.title="新增话术";
                }           
            }
        },
        methods:{
        
            //话术库分类+信息
            huashu:function(){
                 var that=this;
                 that.loaded=true;
                this.$http.get(url+'Webindex/wordpool',{uid:__UID__}).then(function(res){
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    that.loaded=false;
                    if(res.data.code==0){
                        that.huashu_list=res.data.info;
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
            //添加话术
            add_talk:function(){
            	var that=this;
            	if(that.loaded){
            		return;
            	}
            	var n=that.talk_value.replace(/\s*/g, "");
            	if(n==''){
            		msg('内容不能为空！');return;
            	}	
            	var class_id=that.huashu_list[that.index_huashu]['id']?that.huashu_list[that.index_huashu]['id']:0;
            	console.log(class_id);
            	console.log(n);
            	 var that=this;
                 that.loaded=true;
                this.$http.get(url+'Webindex/add_wordpool',{
                	uid:__UID__,
                	value:n,
                	class_id:class_id
                }).then(function(res){
                    if (typeof res.data == 'string') {
                        res.data = json_parse(res.data);
                    }
                    that.loaded=false;
                    if(res.data.code==0){
                    	 msg('添加成功');
                       that.talk_value='';
                       setTimeout(function(){
							that.type=1;
                       },1e3);
                       
                    }else{
                          layer.open({
                                    content: res.data.msg,skin: 'msg',time: 2 //2秒后自动关闭
                                  });
                    }
                },function(res){
                    msg('接口异常');
                });
            },
            // 显示已输入话术字数
            descInput:function() {
                var textVal = this.talk_value.length;
                this.inputCnt = textVal;
            },
            //删除话术
            del_talk:function(id){
            		var that=this;
            	layer.open({
				    content: '您确定要删除该条话术吗？'
				    ,btn: [ '确定','取消']
				    ,yes: function(index){
				    	that.$http.get(url+'Webindex/del_wordpool',{
		                	uid:__UID__,
		                	id:id,
		                }).then(function(res){
		                    if (typeof res.data == 'string') {
		                        res.data = json_parse(res.data);
		                    }
		                    that.loaded=false;
		                    if(res.data.code==0){
		                    	 msg('删除成功');
		                      	that.huashu();
		                    }else{
		                          layer.open({
		                          content: res.data.msg,skin: 'msg',time: 2 //2秒后自动关闭
		                      });
		                    }
		                },function(res){
		                    msg('接口异常');
		                });
				      layer.close(index);
				    }
				  });
            }
              
           
        }    
    });
};
