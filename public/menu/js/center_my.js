var snap = location.href;
var cuff = snap.split('addons');
var host = cuff[0];
var that=this;
var member_cell = {
    'member':{name:'会员信息',icon:'about'},
    'order':{name:'我的订单',icon:'about'},
    'fenxiao':{name:'分销中心',icon:'fx_icon'},
    'follow':{name:'我的关注',icon:'like'},
    'coupon':{name:'我的优惠券',icon:'coupon'},
    'book':{name:'我的预约',icon:'appointment'},
    'kjm':{name:'我的砍价',icon:'kj_icon'},
    'kjo':{name:'砍价订单',icon:'kj_order_icon'},
    'ptm':{name:'我的拼团',icon:'group_icon'},
    'pto':{name:'拼团订单',icon:'groupj_order_icon'},
    'kefu':{name:'在线客服',icon:'service'},
    'address':{name:'收货地址',icon:'location'},
    'about':{name:'关于我们',icon:'about'},
    'paycontent':{name:'我的订阅',icon:'dingyue'},
};
//选择颜色
function select_color(color,type) {
    bannerVM.now_right_data[type] = color;
}
//点击左侧组件
$(".j-diy-addModule").click(function () {
    var type = $(this).attr('data-type');
    bannerVM.add_h=245;
    bannerVM.add_top=145;
    var new_item ={};
    new_item['type']=type;
    new_item['time_key'] = Date.parse(new Date())/1000+""+Math.round(Math.random()*100000);
    if(member_cell.hasOwnProperty(type))
    {
        new_item['img']=host+ "addons/yb_mingpian/core/public/images/member/"+member_cell[type]['icon']+".png";
        new_item['name']=member_cell[type]['name'];
    }
    //右边向当前数组中增加数据
    var item = {};
    item['imgurl'] = host+ "addons/yb_mingpian/core/public/menu/images/Lb21.png";
    item['top'] = 0;
    item['this_type']=type;
    item['width']="100";
    item['name'] = '名称';
    item['alias'] = '链接';
    var arr=[];
    if(type == "banner"){
        delete(item['width']);delete(item['name']);delete(item['alias']);
        arr.push(item);
        //左边幻灯片增加数据
        new_item['jiaodian_color']="#be0000";
        new_item['jiaodian_dots']="block";
        new_item['indicator_dots']="2";
        new_item['ly_width']="10";
        new_item['ly_height']="3";
        new_item['bg_color'] = '#ffffff';
        new_item['topimg']=host + "addons/yb_mingpian/core/public/menu/images/Lb.jpg";
        new_item['list'] = arr;
    }
    else if(type == "advert")
    {
        delete(item['name']);delete(item['alias']);
        arr.push(item);
        //左边幻灯片增加数据
        new_item['ly_width']="10";
        new_item['ly_height']="3";
        new_item['bg_color']="#ffffff";
        new_item['topimg']=host+ "addons/yb_mingpian/core/public/menu/images/Lb2.png";
        new_item['list'] = arr;
    }
    else if(type == "member")
    {
        new_item['bg_color']="#f2f2f2";
        new_item['text_color']="#333333";
    }
    else if(type == "navigation")
    {
        arr.push(item);
        //左边幻灯片增加数据
        new_item['radian']="0";
        new_item['style_type']=4;
        new_item['layout']="cubeNavigationArea column-4 clearfix";
        new_item['text_color']="#333333";
        new_item['bg_color'] = "#ffffff";
        new_item['font_size']="12";
        new_item['topimg']=host+ "addons/yb_mingpian/core/public/menu/images/Ggdh1.png";
        new_item['list'] = arr;
    }
    else if(type == "headline")
    {
        //左边幻灯片增加数据
        new_item['name'] = '标题名称';
        new_item['style_type'] = 1;
        new_item['bg_color']="#000000";
        new_item['text_color']="#333333";
        new_item['font_size']="18";
        new_item['bg_color']="#ffffff";
    }
    else if(type == "blank")
    {
        //左边幻灯片增加数据
        new_item['bg_color']="#ffffff";
        new_item['ly_height']="20";
    }
    else if(type == 'search')
    {
        new_item['hot_word']="";
        new_item['style_type']=0;
        new_item['bg_color']="#f2f2f2";
        new_item['bd_color']="#ffffff";
        new_item['text_color']="#333333";
    }
    else if(type == 'search')
    {
        new_item['hot_word']="";
        new_item['style_type']=0;
        new_item['bg_color']="#f2f2f2";
        new_item['bd_color']="#ffffff";
        new_item['text_color']="#333333";
    }
    else if(type == 'floaticon')
    {
        item = {};
        item['imgurl'] = host+ "addons/yb_mingpian/core/public/menu/images/kefu.png";
        arr.push(item);
        new_item['form_bottom']="45";
        new_item['form_margin']="0";
        new_item['form_transparent']="100";
        new_item['list'] = arr;
    }
    else if(type == 'blankline')
    {
        new_item['line_type']="solid";
        new_item['line_color']="#000000";
        new_item['bg_color']="#ffffff";
        new_item['ly_height']="1";
        new_item['margin']="10";
    }
    else if(type == 'article_list')
    {
        item['name']="标题";
        item['description']="内容描述";
        item['imgurl']=host+ "addons/yb_mingpian/core/public/menu/images/Tpj_3.png";
        arr.push(item);
        new_item['font_size']="15";
        new_item['bg_color']="#ffffff";
        new_item['text_color']="#000000";
        new_item['style_type']="1";
        new_item['add_type']="1";
        new_item['add_num']="1";
        new_item['add_sort']="time";
        new_item['add_cate']="0";
        new_item['list'] = arr;
    }
    else if(type == 'product_list')
    {
        item['name']="标题";
        item['imgurl']=host+ "addons/yb_mingpian/core/public/menu/images/Tpj_3.png";
        arr.push(item);
        new_item['font_size']="15";
        new_item['bg_color']="#ffffff";
        new_item['text_color']="#000000";
        new_item['style_type']="1";
        new_item['add_type']="1";
        new_item['add_num']="1";
        new_item['add_cate']="0";
        new_item['list'] = arr;
    }
    else if(type == 'goods')
    {
        item['name']="商品标题";
        item['description']="商品简介";
        item['price']="0.0";
        item['imgurl']=host+ "addons/yb_mingpian/core/public/menu/images/Tpj_3.png";
        arr.push(item);
        new_item['font_size']="12";
        new_item['bg_color']="#ffffff";
        new_item['text_color']="#000000";
        new_item['style_type']="1";
        new_item['add_type']="1";
        new_item['add_num']="1";
        new_item['add_sort']="time";
        new_item['add_cate']="0";
        new_item['list'] = arr;
    }
    else if(type == 'edit_button')
    {
        item['imgurl'] =  host+ "addons/yb_mingpian/core/public/menu/images/kefu.png";
        item['name']='点击选择链接';
        arr.push(item);
        new_item['name']="按钮";//按钮名称
        new_item['radius']="0";//按钮样式
        new_item['show_border']="0";//边框显示
        new_item['bg_color']="#0DA3F9";//背景颜色
        new_item['text_color']="#ffffff";//字体颜色
        new_item['border_color']="#797979";//边框颜色
        new_item['show_icon']="1";//显示图片
        new_item['list'] = arr;
    }
    else if(type == 'position')
    {
        new_item['name']="请输入地址描述(限制20字内)";//按钮名称
        new_item['lon']="";
        new_item['lat']="";
        new_item['bg_color']="#ffffff";
        new_item['text_color']="#333333";
        new_item['show_type']="1";
    }
    else if(type == 'rich_text')
    {
        new_item['content']="请输入";
        new_item['bg_color']="#ffffff";
    }
    else if(type == 'edit_piclist')
    {
        item['imgurl'] = host+ "addons/yb_mingpian/core/public/menu/images/Tplb_3.png";
        item['name'] = '链接至';
        arr.push(item);
        new_item['style_type']="1";  //排版选择
        new_item['column_num']="2";  //样式选择
        new_item['radian']="0";  //样式选择
        new_item['bg_color']="#ffffff";  //圆角弧度
        new_item['text_color']="#333333";  //圆角弧度
        new_item['list'] = arr;
    }
    else if(type == 'customer')
    {
        if ($("#customer_button_this").length>0){
            layer.msg('客服按钮只能添加一个',{icon:5,time:1000});
            return false;
        }
        new_item['form_bottom']="30";
        new_item['form_margin']="0";
        new_item['form_transparent']="100";
        new_item['imgurl'] = host+ 'addons/yb_mingpian/core/public/menu/images/service_icon.png';
    }
    else if(type == 'announcement')
    {
        new_item['imgurl'] = host+ "addons/yb_mingpian/core/public/menu/images/hotdot.png";
        new_item['bg_color']="#ffffff";
        new_item['text_color']="#333333";
        new_item['content']="请填写公告内容";
    }
    else if(type == 'ad')
    {
        new_item['imgurl']=host+ "addons/yb_mingpian/core/public/menu/images/tencent_app.jpg";
        new_item['ad_id']="0";
        new_item['ly_height']="140";
    }
    else if(type == 'edit_music')
    {
        new_item['url']="";
        new_item['imgurl']=host+"addons/yb_mingpian/core/public/menu/images/Tplb_3.png";
        new_item['name']="";
        new_item['author']="";
    }
    else if(type == 'tripartite')
    {
        var item = {};
        item['imgurl']=host+ "addons/yb_mingpian/core/public/menu/images/11red.PNG";
        item['top'] =0;
        item['alias'] = '';
        var item1 = {};
        item1['imgurl']=host+ "addons/yb_mingpian/core/public/menu/images/21blue.PNG";
        item1['top'] = 135;
        item1['alias'] = '';
        var item2 = {};
        item2['imgurl']=host+ "addons/yb_mingpian/core/public/menu/images/21yellow.PNG";
        item2['top'] = 270;
        item2['alias'] = '';
        var arr=[];
        arr.push(item);
        arr.push(item1);
        arr.push(item2);
        new_item['list'] = arr;
    }
    else if(type == 'quartet')
    {
        var item = {};
        item['imgurl']=host+ "addons/yb_mingpian/core/public/menu/images/23blue.PNG";
        item['top'] =0;
        var item1 = {};
        item1['imgurl']=host+ "addons/yb_mingpian/core/public/menu/images/11blue.PNG";
        item1['top'] = 135;
        var item2 = {};
        item2['imgurl']=host+ "addons/yb_mingpian/core/public/menu/images/11red.PNG";
        item2['top'] = 270;
        var item3 = {};
        item3['imgurl']=host+ "addons/yb_mingpian/core/public/menu/images/11yellow.PNG";
        item3['top'] = 405;
        var arr=[];
        arr.push(item);
        arr.push(item1);
        arr.push(item2);
        arr.push(item3);
        new_item['list'] = arr;
    }
    else if(type == 'message_board')
    {
        new_item['bg_color']="#f2f2f2";
    }
    else if(type == 'edit_video')
    {
        new_item['ly_height']="150";
        new_item['url']="";
        new_item['video_type']="0";
        new_item['imgurl']="";
    }
    else if(type == 'store_door')
    {
        new_item['bg_color']="#ffffff";
        new_item['text_color']="#333333";
        new_item['name']="门店名称";
        new_item['time']="8:00-18:00";
        new_item['phone']="";
        new_item['imgurl']=host+ "addons/yb_mingpian/core/public/menu/images/business.png";
    }
    else if(type == 'edit_form')
    {
        new_item['imgurl']=host+ "addons/yb_mingpian/core/public/menu/images/choose_form.jpg";
    }
    else if(type == 'join_us')
    {
        var item = {};
        item['this_type']="join_us";
        item['name'] = "职位名";
        item['pay'] = "3k-5k";
        item['major']="本科";
        item['exp']="无";
        item['key_id']="";
        item['top']=0;
        var arr=[];
        arr.push(item);
        new_item['list'] = arr;
    }
    else if(type == 'miaosha')
    {
        item['name']="标题";
        item['description']="内容描述";
        item['imgurl']=host+ "addons/yb_mingpian/core/public/menu/images/Tpj_3.png";
        arr.push(item);
        new_item['font_size']="15";
        new_item['bg_color']="#ffffff";
        new_item['text_color']="#000000";
        new_item['style_type']="1";
        new_item['add_type']="1";
        new_item['add_num']="1";
        new_item['add_sort']="time";
        new_item['add_cate']="0";
        new_item['list'] = arr;
    }
    bannerVM.now_type = '';
    Vue.nextTick(function () {
        var index = -1;
        if(bannerVM.now_index > -1)
        {
            index = bannerVM.now_index+1;
            bannerVM.all_data.splice(index,0,new_item);
        }
        else
        {
            bannerVM.all_data.push(new_item);
            //获取当前增加的幻灯片下标
            index = bannerVM.all_data.length-1;
        }
        if(type=='join_us'){
            bannerVM.joinlist=bannerVM.all_data[index]["list"];
            console.log(bannerVM.joinlist);
        }
        // if(bannerVM.now_index<2) {
            // console.log(bannerVM.all_data);
            // console.log(bannerVM.now_right_data);
        // }
        bannerVM.now_index = index;
        bannerVM.now_right_data = bannerVM.all_data[bannerVM.now_index];

        bannerVM.now_type = type;
    });
})
//轮播图添加一条空数据
function list_add_item(type){
    if(type == 'tabbar')
    {
        var item = {
            name:'名称',
            alias:'链接',
            url:'',
            tabbar_icon:host+'/addons/yb_mingpian/core/public/upload/navigat/white_2.png',
            tabbar_select_icon:host+'/addons/yb_mingpian/core/public/upload/navigat/green_2.png',
            key:'1'
        };
        bannerVM.now_right_data.list.push(item);
        return;
    }
    var item = {};
    item['imgurl'] =  host+ "addons/yb_mingpian/core/public/menu/images/Lb21.png";
    item['top'] = bannerVM.now_right_data.list.length * 135;
    item['this_type'] = type;
    item['alias'] = '链接';
    if(type == 'advert')
    {
        item['width']="100";
    }
    else if(type == 'navigation')
    {
        item['name'] = '名称';
        item['alias'] = '链接';
    }
    else if(type == 'article_list')
    {
        item['imgurl'] =  host+ "addons/yb_mingpian/core/public/menu/images/Tpj_3.png";
        item['name'] = '标题';
        item['alias'] = '链接';
        item['description']="内容描述";
    }
    else if(type == 'miaosha')
    {
        item['imgurl'] =  host+ "addons/yb_mingpian/core/public/menu/images/Tpj_3.png";
        item['name'] = '标题';
        item['alias'] = '链接';
        item['description']="内容描述";
    }
    else if(type == 'goods')
    {
        item['imgurl'] =  host+ "addons/yb_mingpian/core/public/menu/images/Tpj_3.png";
        item['name'] = '商品标题';
        item['alias'] = '链接';
        item['price']="0.0";
        item['description']="商品简介";
    }
    else if(type == 'edit_piclist')
    {
        item['imgurl'] = host + "addons/yb_mingpian/core/public/menu/images/Tplb_3.png";
        item['name'] = '链接至';
        item['alias'] = '链接';
    }
    bannerVM.add_h += 135;
    bannerVM.add_top += 135;
    bannerVM.now_right_data.list.push(item);
}
/**
 * 上移组件
 */
function tool_up_vue(){
    //获取组件index
    //当前index
    var index=bannerVM.now_index;
    if (index==0){
        return false;
    }
    //当前的元素
    var Option = bannerVM.all_data[bannerVM.now_index];
    //上面那个元素
    var tempOption = bannerVM.all_data[index - 1];
    Vue.set(bannerVM.all_data, index - 1, Option);
    Vue.set(bannerVM.all_data, index, tempOption);
    bannerVM.now_index= bannerVM.now_index-1;
}
function tool_down_vue() {
    var index=bannerVM.now_index;
    if (index==bannerVM.all_data.length-1){
        return false;
    }
    //当前的元素
    var Option = bannerVM.all_data[bannerVM.now_index];
    //下面那个元素
    var tempOption = bannerVM.all_data[index + 1];
    //  console.log(index);
    //console.log(tempOption);
    Vue.set(bannerVM.all_data, index + 1, Option);
    Vue.set(bannerVM.all_data, index, tempOption);
    bannerVM.now_index= bannerVM.now_index+1;
}
$(".j-diy-addModulesss").click(function () {
    if($(this).attr('data-type')==99){
        $("#offweb_join").css("display",'block').siblings().css("display",'none');
        var item = {};
        item['this_type']="join_us";
        item['name'] = "职位名";
        item['pay'] = "3k-5k";
        item['major']="本科";
        item['exp']="无";
        item['key_id']="";
        item['top']=0;
        var arr=[];
        arr.push(item);
        //左边幻灯片增加数据
        var new_item ={};
        new_item['type']="join_us";
        new_item['list']=arr;
        new_item['time_key'] = Date.parse(new Date())/1000+""+Math.round(Math.random()*100000);
        bannerVM.all_data.push(new_item);
        bannerVM.now_index = bannerVM.all_data.length-1;
        bannerVM.joinlist=bannerVM.all_data[bannerVM.now_index]['list'];
    }
})
function add_join() {
    var item = {};
    item['this_type']="join_us";
    item['name'] = "职位名";
    item['pay'] = "3k-5k";
    item['major']="本科";
    item['key_id']="";
    item['exp']="无";
    item['top'] = bannerVM.joinlist.length * 135;
    bannerVM.add_h += 135;
    bannerVM.add_top += 135;
    bannerVM.joinlist.push(item);
}
//*********************************************底部导航*************************************************//
//图片选择回调
function image_select(img_src) {
    var arr = ['bg_img','imgurl','open_img'];
    var tab_arr = ['tabbar_icon','tabbar_select_icon'];
    if(bannerVM.select_type == 'open_img')
    {
        bannerVM.now_right_data.open_img.imgurl = img_src;
        return;
    }
    if(arr.indexOf(bannerVM.select_type) >= 0)
    {
        bannerVM.now_right_data[bannerVM.select_type] = img_src;
    }
    else if(tab_arr.indexOf(bannerVM.select_type) >= 0)
    {
        bannerVM.now_right_data.list[bannerVM.right_list_index][bannerVM.select_type] = img_src;
    }
    else
    {
        bannerVM.now_right_data.list[bannerVM.right_list_index]['imgurl'] = img_src;
        if(bannerVM.now_right_data['topimg'])
        {
            bannerVM.now_right_data['topimg']= bannerVM.now_right_data.list[0]['imgurl'];
        }
    }
}
function get_position(lat,lng) {
    console.log("center_my get_position !!!!!!!!!");
    bannerVM.now_right_data['lat']=lat;
    bannerVM.now_right_data['lon']=lng;
}
var alias_arr = ['tripartite','edit_button','quartet','edit_piclist','navigation','floaticon','tabbar','banner','advert'];
//菜单选择
function menu_select(item)
{
    console.log("menu_select --------------------");
    console.log(item);
    if(bannerVM.select_type == 'open_img')
    {
        for(var k in item)
        {
            bannerVM.now_right_data.open_img[k] = item[k];
        }
        return;
    }
    if(bannerVM.now_type == "edit_form")
    {
        for(var k in item)
        {
            bannerVM.now_right_data[k] = item[k];
        }
        return;
    }
    var index = bannerVM.right_list_index;
    var new_item=bannerVM.now_right_data['list'][index];
    delete(new_item["lat"]);
    delete(new_item["lng"]);
    delete(new_item["phone"]);
    new_item['key'] = 1;
    console.log(bannerVM.now_type);
    if(alias_arr.indexOf(bannerVM.now_type) >= 0)
    {
        new_item['alias'] = item['name'];
        delete(item["imgurl"]);
        delete(item["name"]);
    }
    for(var k in item)
    {
        new_item[k] = item[k];
    }
    if (new_item["type"]=="web_page"){
        new_item["name"]=new_item["alias"];
    }
    console.log(new_item);
    //bannerVM.now_right_data['topimg']= bannerVM.now_right_data.list[0]['imgurl'];
    Vue.set(bannerVM.now_right_data.list,index,new_item);
}
//打开一个子窗口
function lay_open(title, url, w, h) {
    layer.open({
        type: 2,
        area: [w, h],
        fix: false, //不固定
        maxmin: true,
        shade: 0.4,
        title: title,
        content: url,
        scrollbar: false
    });
}
function get_images(index,item,a,b,c,this_index) {
    // var new_item =bannerVM.all_data[bannerVM.now_index]['list'][this_index];
    var new_item={};
    if (item['type']=="join"){
        new_item['name'] = item.name;
        new_item['pay'] = item.pay;
        new_item['exp'] =item['exp'];
        new_item['major'] =item['major'];
        new_item['key_id'] = item["key_id"];
        Vue.set(bannerVM.joinlist,this_index,new_item);
    }
}
