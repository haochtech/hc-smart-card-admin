var snap = location.href;
var cuff = snap.split('addons');
var host = cuff[0];

//删除项
function vm_del_item(index) {
    bannerVM.now_index = -1;
    bannerVM.now_type = '';
    Vue.nextTick(function () {
        bannerVM.now_right_data = {};
        bannerVM.all_data.splice(index, 1);
    });
}

//选中项
function vm_choose_item(index, type, need_h) {
    bannerVM.now_type = '';
    Vue.nextTick(function () {
        bannerVM.now_type = type;
        bannerVM.now_index = index;
        bannerVM.now_right_data = bannerVM.all_data[index];
        if (need_h) {
            bannerVM.add_h = bannerVM.now_right_data.list.length * 135 + 135;
            bannerVM.add_top = bannerVM.now_right_data.list.length * 135 + 15;
        }
    });
}

//xx集自动获取
function list_auto(newval, post_data) {
    if (newval == 1) {
        bannerVM.add_h = bannerVM.now_right_data.list.length * 135 + 135;
        bannerVM.add_top = bannerVM.now_right_data.list.length * 135 + 15;
    }
    else {
        bannerVM.add_h = 245;
        bannerVM.add_top = 145;
        bannerVM.now_right_data.list.splice(0, bannerVM.now_right_data.list.length);
        $.ajax({
            type: "post",
            url: host + "addons/yb_mingpian/core/index.php?s=/admin/custom/xx_list",
            data: post_data,
            success: function (data) {
                var arr = [];
                for (var k in data) {
                    var item = {};
                    item['name'] = data[k]['name'];
                    item['alias'] = data[k]['name'];
                    item['description'] = data[k]['description'];
                    item['imgurl'] = data[k]['imgurl'];
                    if (post_data.type === "goods") {
                        item['price'] = data[k]['price'];
                        item['url'] = '/yb_mingpian/pages/goods/detail/index?id=' + data[k]['id'];
                    }
                    else if (post_data.type === "article") {
                        if (data[k]['link'] != '') {
                            item['url'] = "/yb_mingpian/pages/web/index?url=" + escape(data[k]['link']) + "&name=" + data[k]['name'];
                        } else {
                            item['url'] = '/yb_mingpian/pages/find_info/index?id=' + data[k]['id'];
                        }
                    }
                    else if (post_data.type === "product") {
                        item['url'] = '/yb_mingpian/pages/product/info/index?id=' + data[k]['id'];
                        delete(item['description']);
                    }
                    item['top'] = 0;
                    arr.push(item);
                }
                bannerVM.now_right_data.list = arr;
            }
        });
    }
}

//悬浮图标
Vue.component('floaticon', {
    props: ['index', 'list', 'form_transparent'],
    template: '<li id="floaticon_button_this" style="width:100%;height:auto;" class="ui-draggable"  title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div  v-on:click="onc_banner(index,$event)" class="floaticon pr"><div class="icon_right"><img :style="\'opacity:\'+(form_transparent / 100.0)+\';width:100%px;height:auto;\'" v-for="item in list" :src="item.imgurl" alt=""></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            vm_choose_item(index, 'floaticon', false);
        },
    },
});
//会员中心列
Vue.component('ucenter_cell', {
    props: ['index', 'type', 'name', 'img'],
    template: '<li class="ui-draggable" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index,type)" class="deleteButton"></div><div v-on:click="onc_banner(index,type,$event)" class="uc_li"><div class="uc_icon"><img style="" :src="img" alt=""></div><div class="uc_menu_name">{{name}}</div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, type, e) {
            vm_choose_item(index, 'ucenter_cell', false);
        },
    },
});
//会员中心列
Vue.component('order_status', {
    props: ['index'],
    template: '<li class="ui-draggable" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div v-on:click="onc_banner(index,$event)" >' +
        '<div class="order_status">' +
        '<div class="cell"><img src="' + host + 'addons/yb_mingpian/core/public/menu/images/paying-48.png"><span>待付款</span></div>' +
        '<div class="cell"><img src="' + host + 'addons/yb_mingpian/core/public/menu/images/box-48.png"><span>待发货</span></div>' +
        '<div class="cell"><img src="' + host + 'addons/yb_mingpian/core/public/menu/images/car-48.png"><span>待收货</span></div>' +
        '<div class="cell"><img src="' + host + 'addons/yb_mingpian/core/public/menu/images/refund-48.png"><span>退换货</span></div>' +
        '</div>' +
        '</div></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            vm_choose_item(index, 'order_status', false);
        },
    },
});
//会员信息
Vue.component('member', {
    props: ['index', 'bg_color', 'bg_img', 'text_color'],
    template: '<li class="ui-draggable" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div>' +
        '<div v-on:click="onc_banner(index,$event)" class="uc_head_div" :style="' + "'background-color:'" + '+bg_color+' + "';background-image:url('" + '+bg_img+' + "');'" + '" ><img src="public/images/member/noface.png"><span :style="' + "'color:'" + '+text_color+' + "';'" + '">会员昵称</span></div><span class="bottom_space"></span></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            vm_choose_item(index, 'member', false);
        },
    },
});
//轮播图
Vue.component('banner', {
    props: ['topimg', 'jiaodian_color', 'index', 'list', 'ly_height', 'jiaodian_dots', 'bg_color'],
    template: '<li :style="\'height:\'+juedui_height+\'px;background:\'+bg_color+\';\'" class="ui-draggable pr" title="点击进行修改,拖动交换位置.">' +
        '<div v-on:click="del_left(index)" class="deleteButton"></div>' +
        '<div class="swiperImg"><div v-on:click="onc_banner(index,$event)" class="imgList">' +
        '<ul><li><img :src="topimg"></li></ul></div>' +
        '<div class="buttle" :style="' + "'display:'" + '+jiaodian_dots+' + "';'" + '">' +
        '<i  v-on:click="topimg = list[index].imgurl" v-for="(right,index) in list" :style="' + "'background:'" + '+jiaodian_color+' + "';'" + '" class="on"></i>' +
        '</div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {

            vm_choose_item(index, 'banner', true);
        }
    },
    computed: {
        juedui_height: function () {
            return this.ly_height / 10 * 310;
        }
    },
});
//秒杀
Vue.component('miaosha', {
    props: ['list', 'index', 'bg_color', 'font_size', 'text_color', 'style_type', 'add_type', 'add_sort', 'add_num', 'add_cate'],
    template: '<li :style="' + "'background-color:'" + '+bg_color+' + "';'" + '" class="ui-draggable" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div  v-on:click="onc_banner(index,$event)" :class="\'cubeNavigationArea column-\'+style_num+\' clearfix\'"><div class="imgList" v-for="d in list"><div :style="style_height" class="cubeLink cubeLink1"><div :style="style_width" class="_img"><img :src="d.imgurl"></div><div :style="' + "'width:'" + '+text_width+' + "';'" + '" class="_text"><p :style="' + "'font-size:'" + '+font_size+' + "'px;color:'" + '+text_color+' + "';'" + '" class="title">{{d.name}}</p><p style="font-size: 14px;">距开始</p><p style="font-size: 14px;">8天8时8分8秒</p></div></div></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            vm_choose_item(index, 'miaosha', true);
        },
    },
    computed: {
        style_num: function () {
            return this.style_type < 3 ? 1 : 2;
        },
        style_width: function () {
            return this.style_type == 1 ? '' : 'width:100%;height:130px;';
        },
        style_height: function () {
            return this.style_type == 1 ? '' : 'border-bottom:0px;height:220px;';
        },
        text_width: function () {
            var ss = {'1': '180px', '2': '94%', '3': '140px'};
            return ss[this.style_type + ''];
        },
    },
    watch: {
        add_type: function (newval) {
            list_auto(this.add_type, {
                'type': 'article',
                'num': this.add_num,
                'sort': this.add_sort,
                'cate': this.add_cate
            });
        },
        add_num: function (newval) {
            //article_list_auto(this.add_type,this.add_num,this.add_sort,this.add_cate);
            list_auto(this.add_type, {
                'type': 'article',
                'num': this.add_num,
                'sort': this.add_sort,
                'cate': this.add_cate
            });
        },
        add_sort: function (newval) {
            list_auto(this.add_type, {
                'type': 'article',
                'num': this.add_num,
                'sort': this.add_sort,
                'cate': this.add_cate
            });
        },
        add_cate: function (newval) {
            list_auto(this.add_type, {
                'type': 'article',
                'num': this.add_num,
                'sort': this.add_sort,
                'cate': this.add_cate
            });
        },
    }
});
//广告位
Vue.component('advert', {
    props: ['imgurl', 'index', 'list', 'ly_width', 'ly_height', 'bg_color'],
    template: '<li :style="\'width: 100%;height:\'+juedui_height+\'px;background:\'+bg_color+\';\'" class="ui-draggable" data-title="点击拖动到左侧" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div class="swiperImg pr"><div v-on:click="onc_banner(index,$event)" class="imgList"><ul><li style="float:left;" v-for="right in list" :style="' + "'width:'" + '+right.width+' + "'%;'" + "" + '"><img style="height: 100%" :src="right.imgurl"></li></ul></div><div class="buttle" style="display: block;"></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            vm_choose_item(index, 'advert', true);
        },
    },
    computed: {
        juedui_height: function () {
            return this.ly_height / 10 * 310;
        }
    },
});
//宫格导航
Vue.component('navigation', {
    props: ['index', 'list', 'font_size', 'text_color', 'bg_color', 'radian', 'style_type'],
    template: '<li class="ui-draggable" title="点击进行修改,拖动交换位置.">' +
        '<div v-on:click="del_left(index)" class="deleteButton"></div><div v-on:click="onc_banner(index,$event)" :class="layout" :style="' + "'padding-bottom:8px;background:'" + '+bg_color+' + "';'" + '"><div v-for="c in list" class="cubeLink cubeLink1 Ggdh" style="height:100px"> ' +
        '<a class="cubeLink_a" href="javascript:;"> <div class="cubeLink_bg"></div> <div class="cubeLink_curtain"></div>' +
        '<div class="cubeLink_text g_link"> <div class="cubeLink_ico icon-cube" :style="'+"'background-image:url('" + '+c.imgurl+' + "');border-radius:'" + '+radian+' + "'%;'" + '"></div><div  :style="' + "'font-size:'" + '+font_size+' + "'px;color:'" + '+text_color+' + "';'" + '"  class="cubeLink_text_p "><em>{{c.name}}</em> <p class="cubeLink_subText_p"></p></div> </div> </a> </div>    </div></li>',
    methods: {

        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            vm_choose_item(index, 'navigation', true);
        },
    },
    computed: {
        layout: function () {
            return "cubeNavigationArea column-" + this.style_type + " clearfix";
        }
    },
});
//标题
Vue.component('headline', {
    props: ['name', 'index', 'font_size', 'text_color', 'bg_color', 'style_type'],
    template: '<li :class="\'ui-draggable headline_\'+style_type" title="点击进行修改,拖动交换位置." name="id_title"><div v-on:click="del_left(index)" class="deleteButton"></div><div v-on:click="onc_banner(index,$event)" :style="' + "'background-color:'" + '+bg_color+' + "';'" + '" class="pureText"><div class="wrap"><a  class="aframe"></a><span class="middle_titleO"></span><span class="seven_titleO"></span><span :style="\'font-size:\'+ font_size+\'px;color:\'+text_color" class="Bt_title">{{name}}</span><span class="seven_titleS"></span><span class="middle_titleS"></span></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            vm_choose_item(index, 'headline', false);
        },
    },
});
//辅助空白
Vue.component('blank', {
    props: ['ly_height', 'index', 'bg_color'],
    template: '<li class="ui-draggable" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)"  class="deleteButton"></div><div v-on:click="onc_banner(index,$event)" class="Parting" :style="' + "'height:'" + '+ly_height+' + "'px;background-color:'" + '+bg_color+' + "';'" + '"></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            vm_choose_item(index, 'blank', false);
        },
    },
});
//搜索框
Vue.component('search', {
    props: ['index', 'style_type', 'bg_color', 'bd_color', 'text_color', 'hot_word'],
    template: '<li  class="ui-draggable" :style="' + "'background-color:'" + '+bg_color+' + "';'" + '"  title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><section :style="' + "'background-color:'" + '+bd_color+' + "';'" + '" v-on:click="onc_banner(index,$event)" class="members_search"><input type="text" :style="\'color:\'+text_color+\';\'" v-model="hot_word" readonly placeholder="搜索：请输入关键字"><div style="float: right;"><button :style="\'margin: 8px 5px 0 0;right: -30px;position: absolute;filter: drop-shadow(\'+text_color+\' -40px 0);\'" type="submit"></button></div></section></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            vm_choose_item(index, 'search', false);
        },
    },
});
//分割线
Vue.component('blankline', {
    props: ['index', 'line_color', 'bg_color', 'ly_height', 'margin', 'line_type'],
    template: '<li class="ui-draggable" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div v-on:click="onc_banner(index,$event)"  class="Parting" :style="' + "'background-color:'" + '+bg_color+' + "';height:'" + '+margin+' + "'px;'" + '" ><section class="custom-line-wrap"><hr :style="' + "'border-top-color:'" + '+line_color+' + "';border-width:'" + '+ly_height+' + "'px;border-top-style:'" + '+line_type+' + "''" + '"" class="custom-line"></section></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            vm_choose_item(index, 'blankline', false);
        },
    },
});
//图文集
Vue.component('article_list', {
    props: ['list', 'index', 'bg_color', 'font_size', 'text_color', 'style_type', 'add_type', 'add_sort', 'add_num', 'add_cate'],
    template: '<li :style="' + "'background-color:'" + '+bg_color+' + "';'" + '" class="ui-draggable" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div  v-on:click="onc_banner(index,$event)" :class="\'cubeNavigationArea column-\'+style_num+\' clearfix\'"><div class="imgList" v-for="d in list"><div :style="style_height" class="cubeLink cubeLink1"><div :style="style_width" class="_img"><img :src="d.imgurl"></div><div :style="' + "'width:'" + '+text_width+' + "';'" + '" class="_text"><p :style="' + "'font-size:'" + '+font_size+' + "'px;color:'" + '+text_color+' + "';'" + '" class="title">{{d.name}}</p><p style="font-size: 14px;">{{d.description}}</p></div></div></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            vm_choose_item(index, 'article_list', true);
        },
    },
    computed: {
        style_num: function () {
            return this.style_type < 3 ? 1 : 2;
        },
        style_width: function () {
            return this.style_type == 1 ? '' : 'width:100%;height:130px;';
        },
        style_height: function () {
            return this.style_type == 1 ? '' : 'border-bottom:0px;height:220px;';
        },
        text_width: function () {
            var ss = {'1': '180px', '2': '94%', '3': '140px'};
            return ss[this.style_type + ''];
        },
    },
    watch: {
        add_type: function (newval) {
            list_auto(this.add_type, {
                'type': 'article',
                'num': this.add_num,
                'sort': this.add_sort,
                'cate': this.add_cate
            });
        },
        add_num: function (newval) {
            //article_list_auto(this.add_type,this.add_num,this.add_sort,this.add_cate);
            list_auto(this.add_type, {
                'type': 'article',
                'num': this.add_num,
                'sort': this.add_sort,
                'cate': this.add_cate
            });
        },
        add_sort: function (newval) {
            list_auto(this.add_type, {
                'type': 'article',
                'num': this.add_num,
                'sort': this.add_sort,
                'cate': this.add_cate
            });
        },
        add_cate: function (newval) {
            list_auto(this.add_type, {
                'type': 'article',
                'num': this.add_num,
                'sort': this.add_sort,
                'cate': this.add_cate
            });
        },
    }
});
//产品集
Vue.component('product_list', {
    props: ['list', 'index', 'bg_color', 'font_size', 'text_color', 'style_type', 'add_type', 'add_num', 'add_cate'],
    template: '<li :style="' + "'background-color:'" + '+bg_color+' + "';'" + '" class="ui-draggable" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div>' +
        '<div  v-on:click="onc_banner(index,$event)" :class="\'product_list product_type\'+style_type">' +
        '<div :class="\'product_item item_\'+style_type" v-for="d in list">' +
        '<div class="product_img"><img :src="d.imgurl"></div>' +
        '<p :style="' + "'font-size:'" + '+font_size+' + "'px;color:'" + '+text_color+' + "';'" + '" class="title">{{d.name}}</p>' +
        '</div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            vm_choose_item(index, 'product_list', true);
        },
    },
    computed: {
        style_num: function () {
            return this.style_type < 3 ? 1 : 2;
        },
        style_width: function () {
            return this.style_type == 1 ? '' : 'width:100%;height:130px;';
        },
        style_height: function () {
            return this.style_type == 1 ? '' : 'border-bottom:0px;height:220px;';
        },
        text_width: function () {
            var ss = {'1': '180px', '2': '94%', '3': '140px'};
            return ss[this.style_type + ''];
        },
    },
    watch: {
        add_type: function () {
            list_auto(this.add_type, {'type': 'product', 'num': this.add_num, 'cate': this.add_cate});
        },
        add_num: function () {
            list_auto(this.add_type, {'type': 'product', 'num': this.add_num, 'cate': this.add_cate});
        },
        add_cate: function () {
            list_auto(this.add_type, {'type': 'product', 'num': this.add_num, 'cate': this.add_cate});
        },
    }
});
//商品集
Vue.component('goods', {
    props: ['list', 'index', 'bg_color', 'font_size', 'text_color', 'style_type', 'add_type', 'add_sort', 'add_num', 'add_cate'],
    template: '<li title="点击进行修改,拖动交换位置." :style="\'background-color:\'+bg_color" class="ui-draggable"><div v-on:click="del_left(index)" class="deleteButton"></div><div  v-on:click="onc_banner(index,$event)" :class="\'cubeNavigationArea goods_column-\'+style_type+\' clearfix\'"><div v-for="g in list" class="cubeLink cubeLink1 Ggdh" style="border-bottom: 1px solid rgb(229, 229, 229); border-right: 1px solid rgb(229, 229, 229);">' +
        '<div class="goods_img"><img style="width: 100%;overflow: hidden" :src="g.imgurl"></img></div>' +
        '<div class="cubeLink_text g_link" style="position: relative !important;"><div style="display:flex;flex-direction:column;">' +
        '<em :style="' + "'color:'" + '+text_color+' + "';font-size:'" + '+font_size+' + "'px;font-weight: bold; padding-left: 5px;'" + '">{{g.name}}</em><em style="padding-left: 5px;font-size: 10px;color: #68838B">{{g.description}}</em></div><p class="cubeLink_subText_p">￥{{g.price}}</p></div></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            vm_choose_item(index, 'goods', true);
        },
    },
    watch: {
        add_type: function (newval) {
            list_auto(this.add_type, {
                'type': 'goods',
                'num': this.add_num,
                'sort': this.add_sort,
                'cate': this.add_cate
            });
        },
        add_num: function (newval) {
            // goods_auto(this.add_type,this.add_num,this.add_sort,this.add_cate);
            list_auto(this.add_type, {
                'type': 'goods',
                'num': this.add_num,
                'sort': this.add_sort,
                'cate': this.add_cate
            });
        },
        add_sort: function (newval) {
            list_auto(this.add_type, {
                'type': 'goods',
                'num': this.add_num,
                'sort': this.add_sort,
                'cate': this.add_cate
            });
        },
        add_cate: function (newval) {
            list_auto(this.add_type, {
                'type': 'goods',
                'num': this.add_num,
                'sort': this.add_sort,
                'cate': this.add_cate
            });
        },
    }
});
//按钮
Vue.component('edit_button', {
    props: ['list', 'index', 'name', 'radius', 'show_border', 'show_icon', 'bg_color', 'text_color', 'border_color'],
    template: '<li class="ui-draggable" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div v-on:click="onc_banner(index)" class="button_Text" style="text-align:center;"> <div class="wrap" :style="' + "'border-radius:'" + '+radius+' + "'px;border:'" + '+show_border+' + "'px solid;border-color:'" + '+border_color+' + "';background-color:'" + '+bg_color+' + "';padding: 0 20px;'" + '"><img v-if="show_icon == 1" v-for="item in list" :src="item.imgurl" style="width:20px;margin: 0px 15px 0px 0px;vertical-align: middle;"><span :style="' + "'color:'" + '+text_color+' + "';'" + '">{{name}}</span></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            vm_choose_item(index, 'edit_button', false);
        },
    },
});
//地理位置
Vue.component('position', {
    props: ['index', 'name', 'show_type', 'bg_color', 'text_color'],
    template: '<li class="ui-draggable" :style="\'background:\'+bg_color+\';\'" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div v-on:click="onc_banner(index)" class="position"><img v-if="show_type==2" src="' + host + 'addons/yb_mingpian/core/public/menu/images/pos.png"> <div v-if="show_type==1" class="wrap" :style="\'color:\'+text_color+\';\'"> <em class="fr iconfont icon-arrow-right"></em> <span class="title"><i class="Hui-iconfont Hui-iconfont-weizhi" :style="\'color:\'+text_color+\';\'"></i>{{name}}</span> </div> </div></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            vm_choose_item(index, 'position', false);
        },
    },
});
//富文本
Vue.component('rich_text', {
    props: ['index', 'content', 'bg_color'],
    template: '<li class="ui-draggable" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div v-on:click="onc_banner(index)" style="" :style="' + "'background-color:'" + '+bg_color+' + "';'" + '" class="control"><div :id="' + "'um_text-'" + '+index+' + "';'" + '" class="custom-richtext" v-html="content"></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            var old = bannerVM.now_type;
            vm_choose_item(index, 'rich_text', false);
            if (old == 'rich_text') {
                Vue.nextTick(function () {
                    ue.setContent(bannerVM.now_right_data['content']);
                });
            }
        },
    },
});
//图片列表
Vue.component('edit_piclist', {
    props: ['index', 'list', 'style_type', 'column_num', 'radian', 'bg_color', 'text_color'],
    template: '<li class="ui-draggable" title="点击进行修改,拖动交换位置." :style="\'background:\'+bg_color+\';\'"><div v-on:click="del_left(index)" class="deleteButton"></div>' +
        '<div v-on:click="onc_banner(index,$event)" :class="\'cubeNavigationArea column-\'+column_num+\' clearfix\'"><div v-for="item in list" class="cubeLink cubeLink1" style="padding: 0 4px;">' +
        '<a class="cubeLink_a" href="javascript:;" style="height: auto;"><div class="cubeLink_curtain"></div><div :style="\'border-radius:\'+radian+\'%;width:100%\'" class="cubeLink_ico1 icon-cube">' +
        '<img :src="item.imgurl" width="100%" height="auto"></div> <div  v-if="style_type < 3"  class="cubeLink_text1 g_link">' +
        '<div class="cubeLink_text_p"><em :style="html_style">{{item.name}}</em> <p class="cubeLink_subText_p" style="margin:0px;"></p></div></div></a></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            vm_choose_item(index, 'edit_piclist', true);
        },
    },
    computed: {
        html_style: function () {
            return this.style_type == 2 ? 'text-align:center;color:#fff;position:absolute;left:0;bottom:-3px;width:100%;line-height:29px;background-color:rgba(0,0,0,.5);text-decoration:inherit;' : '';
        }
    },
});
//悬浮客服
Vue.component('customer', {
    props: ['index', 'form_transparent', 'imgurl'],
    template: '<li id="customer_button_this" style="width:100%;height:auto;" class="ui-draggable" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div  v-on:click="onc_banner(index,$event)" class="floaticon pr"><div class="icon_right" ><img :style="\'opacity:\'+(form_transparent / 100.0)+\';width:100%;height:auto;\'" :src="imgurl" alt=""></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            vm_choose_item(index, 'customer', false);
        },
    },
});
//公告
Vue.component('announcement', {
    props: ['index', 'text_color', 'bg_color', 'content', 'imgurl'],
    template: '<li id="set_announcement" :style="' + "'background-color:'" + '+bg_color+' + "''" + '" class="ui-draggable" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div v-on:click="onc_banner(index,$event)" class="floaticon pr"><div style="min-height: 28px;display:flex;align-items: center;overflow: hidden;text-overflow: ellipsis;-webkit-line-clamp:1;-webkit-box-orient: vertical;"><img style="width: 30px;height: 30px;" :src="imgurl" alt=""><span :style="\'font-size: 12px;color:\'+text_color+\';\'">{{content}}</span></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            vm_choose_item(index, 'announcement', false);
        },
    },
});
//流量主
Vue.component('ad', {
    props: ['index', 'ly_height', 'imgurl'],
    template: '<li class="ui-draggable" :style="' + "'height:'" + '+ly_height+' + "'px;'" + '" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><img v-on:click="onc_banner(index,$event)" style="width: 100%;height: 100%;" :src="imgurl" alt=""></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            vm_choose_item(index, 'ad', false);
        },
    },
});
//音频
Vue.component('edit_music', {
    props: ['index', 'url', 'name', 'author', 'imgurl'],
    template: '<li id="edit_music" class="ui-draggable" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div v-on:click="onc_banner(index,$event)" style="width:310px;height:100px"> <div style="padding:12px;">  <div style="    border: 1px solid #a89e9e;"><img id="audio_img" :src="imgurl" style="width:70px;cursor:pointer;height:70px;"><img id="audio_stare" src="' + host + 'addons/yb_mingpian/core/public/menu/images/audio_stare.png" style="width:80px;cursor:pointer;height:70px;position:absolute;top:10px;left:10px;"><div style="float: right;width: 50px;margin:4px 4px;">00:00</div> <div style="float:right;width: 120px;text-align: center;height: 70px;"><p style="height: 35px;margin: 0;font-size: 15px;line-height: 35px;" class="audio_title">{{name}}</p><p class="audio_desc">{{author}}</p>   </div>  </div> </div> <audio id="my_audio" controls="controls" :src="url"></audio></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            vm_choose_item(index, 'edit_music', false);
        },
    },
});
//三方图
Vue.component('tripartite', {
    props: ['list', 'index'],
    template: '<li><div v-on:click="del_left(index)" class="deleteButton"></div><div  v-on:click="onc_banner(index,$event)" class="weui-grids threeimage" style="display: flex;"><div href="javascript:;" style="flex: 1 1 0%;"><div class="weui-grid__icon" style="width: 159px; height: 159px;"><img :src="list[0].imgurl" alt="" draggable="false" style="width: 159px; height: 159px;"></div></div><div href="javascript:;" style="flex: 1 1 0%;"><div class="weui-grid__icon" style="width: 159px; height: 79.5px;"><img :src="list[1].imgurl" alt="" draggable="false" style="width: 159px; height: 79.5px;"></div> <div class="weui-grid__icon" style="width: 159px; height: 79.5px;"><img :src="list[2].imgurl" alt="" draggable="false" style="width: 159px; height: 79.5px;;"></div></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            bannerVM.add_h = 245;
            bannerVM.add_top = 145;
            vm_choose_item(index, 'tripartite', false);
        },
    },
});
//四方图
Vue.component('quartet', {
    props: ['list', 'index'],
    template: '<li><div v-on:click="del_left(index)" class="deleteButton"></div><div  v-on:click="onc_banner(index,$event)" class="weui-grids threeimage" style="display: flex;"><div href="javascript:;" style="flex: 1 1 0%;"><div class="weui-grid__icon" style="width: 212px; height: 318px;"><img :src="list[0].imgurl" alt="" draggable="false" style="width: 212px; height: 318px;"></div></div><div href="javascript:;" style="flex: 1 1 0%;"><div class="weui-grid__icon" style="width: 106px; height: 106px;"><img :src="list[1].imgurl" alt="" draggable="false" style="width: 106px; height: 106px;"></div> <div class="weui-grid__icon" style="width: 106px; height: 106px;"><img :src="list[2].imgurl" alt="" draggable="false" style="width: 106px; height: 106px;"></div> <div class="weui-grid__icon" style="width: 106px; height: 106px;"><img :src="list[3].imgurl" alt="" draggable="false" style="width: 106px; height: 106px;"></div></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            bannerVM.add_h = 245;
            bannerVM.add_top = 145;
            vm_choose_item(index, 'quartet', false);
        },
    },
});
//留言板
Vue.component('message_board', {
    props: ['index', 'bg_color', 'btn_color', 'text_color'],
    template: '<li title="点击进行修改,拖动交换位置" :style="' + "'background-color:'" + '+bg_color+' + "''" + '" class="ui-draggable"><div v-on:click="del_left(index)" class="deleteButton"></div><div v-on:click="onc_banner(index,$event)"  class="widget_wrap" style="padding:5px;"><div style="background:#fff"><textarea class="tests" placeholder="感谢提出建议"></textarea></div><div class="input" style="border-bottom:1px solid #EEEEEE;margin-top:15px">姓名<input type="text" name="" placeholder="请输入姓名（可选）" style=""></div><div class="input">手机<input type="text" name="" placeholder="请输入手机号（可选）" style=""></div><div class="sub" :style="\'background:\'+btn_color+\';color:\'+text_color+\';\'">提交</div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            vm_choose_item(index, 'message_board', false);
        },
    },
});
//视频
Vue.component('edit_video', {
    props: ['index', 'ly_height', 'url', 'video_type', 'imgurl'],
    template: '<li title="点击进行修改,拖动交换位置" class="ui-draggable"><div v-on:click="del_left(index)" class="deleteButton"></div><div v-on:click="onc_banner(index,$event)" class="video_sub" :style="\'margin-left: 0px; margin-right: auto; margin-top: 0px; position: relative;height:\'+ly_height+\'px;\'"><video id="my_video" :src="url" :poster="imgurl" controls>您的浏览器不支持 video 标签</video> <div class="video_unapplet" style="display: none;"></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            vm_choose_item(index, 'edit_video', false);
        },
        video_play: function () {
            $("#my_video").play();
        }
    },
});
//门店
Vue.component('store_door', {
    props: ['index', 'imgurl', 'bg_color', 'text_color', 'name', 'time', 'phone'],
    template: '<li :style="' + "'background-color:'" + '+bg_color+' + "';'" + '" title="点击进行修改,拖动交换位置" class="ui-draggable"><div v-on:click="del_left(index)" class="deleteButton"></div><div  v-on:click="onc_banner(index,$event)"  class="widget_wrap" style="padding:5px"><div style="display:flex;"><img style="width:50px;height:50px" :src="imgurl"  class="top-img"><div :style="\'flex:1;font-size:12px;color:\'+text_color+\';\'"><div class="title-name">{{name}}</div><div>工作时间:<span class="time">{{time}}</span>' +
        '<div class="shop_phone_icon" :style="\'filter: drop-shadow(\'+text_color+\' -40px 0);\'"></div></div></div></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            vm_choose_item(index, 'store_door', false);
        },
    },
});

//招聘
Vue.component('join_us', {
    props: ['index', 'list'],
    template: '<li title="点击进行修改,拖动交换位置" class="ui-draggable"><div v-on:click="del_left(index)" class="deleteButton"></div><div v-on:click="onc_banner(index)" class="control"><div v-for="g in list"><a class="cubeLink_a" href="javascript:;"><p>职位：{{g.name}}</p><p>待遇：{{g.pay}}</p><p>经验：{{g.exp}}&nbsp;&nbsp;学历：{{g.major}}</p></a></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            vm_choose_item(index, 'join_us', false);
        },
    },
});
//表单
Vue.component('edit_form', {
    props: ['index', 'imgurl'],
    template: '<li id="edit_form_this" class="ui-draggable" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div v-on:click="onc_banner(index,$event)" style="width:310px;"><img :src="imgurl"></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            vm_del_item(index);
        },
        //选中
        onc_banner: function (index, e) {
            vm_choose_item(index, 'edit_form', false);
        },
    },
});
//颜色选择
Vue.component('color', {
    props: ['name', 'flag', 'default'],
    template: '<div class="item-inner mt10"> <span class="labeltext" style="width:66px;" v-text="name"></span> <div class="chosecolor-container"> <input v-model="color" class="chosecolor data-bind" type="color"> <input type="text" v-model="color" class="color_input"/></div> </div>',
    data: function () {
        return {color: bannerVM.now_right_data[this.flag]}
    },
    watch: {
        color: function (newval) {
            bannerVM.now_right_data[this.flag] = newval;
        }
    }
});
Vue.component('color_n', {
    props: ['name', 'flag', 'default'],
    template: '<div class="item-inner mt10"> <span class="labeltext" style="width:66px;" v-text="name"></span> <div class="chosecolor-container"> <input v-model="color" class="chosecolor data-bind" type="color"> <input type="text" v-model="color" class="color_input"/></div> </div>',
    data: function () {
        return {color: bannerVM.page[this.flag]}
    },
    watch: {
        color: function (newval) {
            bannerVM.page[this.flag] = newval;
        }
    },
    created: function () {
        console.log(this.flag);
        console.log(bannerVM.page);
    }
});
Vue.component('color_t', {
    props: ['name', 'flag', 'default'],
    template: '<div class="item-inner mt10"> <span class="labeltext" style="width:66px;" v-text="name"></span> <div class="chosecolor-container"> <input v-model="color" class="chosecolor data-bind" type="color"> <input type="text" v-model="color" class="color_input"/></div> </div>',
    data: function () {
        return {color: bannerVM.tabbar[this.flag]}
    },
    watch: {
        color: function (newval) {
            bannerVM.tabbar[this.flag] = newval;
        }
    }
});
var ue = null;
var bannerVM = new Vue({
    el: '#b_menu',
    data: {
        select_type: '',
        now_index: -1,//当前下标
        right_list_index: 0, //右侧列表操作元素下标
        now_type: '', //当前操作组件类型
        //当前操作组件数据
        now_right_data: {
            bg_color: '#009193',
            bg_img: '',
        },
        tabbar: {},
        //当前页面配置
        page: {
            name: '页面标题',
            nv_color: '#ffffff',
            bg_color: '#f2f2f2',
            text_color: '#000000',
            bg_img: '',
            open_img: {
                imgurl: '',
                name: '',
                url: '',
                key: '1',
            },
            show_tabbar: 'true',
        },
        all_data: [],//所有数据
        joinlist: [],//招聘集
        add_h_di: 360,
        add_top_di: 250,
        add_h: 245,//DIV高度
        add_top: 145,//添加按钮高度
    },
    computed: {
        nv_bg_url: function () {
            if (this.page.text_color == "#000000") {
                return "public/menu/images/black.png";
            }
            else {
                return "public/menu/images/white.png";
            }
        }
    },
    watch: {
        now_type: function (newval, oldval) {
            if (newval == 'rich_text') {
                if (ue == null) {
                    Vue.nextTick(function () {
                        ue = UM.getEditor('editor', {
                            imageUrl: "__CONF_SITE__app/Umupload/uploadFile", //处理图片上传的接口
                            imageFieldName: "upfile", //上传图片的表单的name
                            imagePath: ""
                        });
                        ue.setContent(bannerVM.now_right_data['content']);
                        ue.addListener("blur", function () {
                            bannerVM.now_right_data['content'] = ue.getContent();
                        });
                    });
                }
            }
            else {
                if (ue != null) {
                    ue.destroy();
                    ue = null;
                }
                if (newval == 'head') {
                    bannerVM.now_right_data = bannerVM.page;
                }
                if (newval == 'tabbar') {
                    bannerVM.now_right_data = bannerVM.tabbar;
                }
            }
        }
    },
    methods: {
        //选中图片
        select_img: function (index, select_type) {
            bannerVM.right_list_index = index;
            bannerVM.select_type = select_type;
            console.log(111);
            layer_open("我的图片", host + '/addons/yb_mingpian/core/index.php?s=/admin/images/dialogalbumlist', 900, 620);
            // art.dialog.open((host+'/addons/yb_mingpian/core/index.php?s=/admin/images/dialogalbumlist'), {
            //     lock : true,
            //     title : "我的图片",
            //     width : 900,
            //     height : 620,
            //     drag : false,
            //     background : "#000000",
            //     scrollbar:false
            // }, true);
        },
        //选中功能呢
        select_menu: function (index, type) {
            bannerVM.right_list_index = index;
            bannerVM.select_type = type;
            lay_open('选择功能', host + 'addons/yb_mingpian/core/index.php?s=/admin/menu/menu_select&type=' + type + '&index=' + index, '50%', '70%');
        },
        //选中功能呢
        select_menu_mp: function (index, type) {
            bannerVM.right_list_index = index;
            bannerVM.select_type = type;
            lay_open('选择功能', host + 'addons/yb_mingpian/core/index.php?s=/admin/bottomnav/menu_select&type=' + type + '&index=' + index, '1000px', '800px');
        },
        //商品
        select_goods: function (index, type) {
            bannerVM.right_list_index = index;
            lay_open('选择商品', host + 'addons/yb_mingpian/core/index.php?s=/admin/menu/menu_select_2&type=goods&path=/yb_mingpian/pages/goods/detail/index', '800px', '600px');
        },
        click_position_wz: function () {
            lay_open('坐标', host + 'addons/yb_mingpian/core/index.php?s=/admin/menu/index_select_position', '800px', '610px');
        },
        select_join: function (index) {
            $("#join_select_id").val(index);
            lay_open('选择职位', host + 'addons/yb_mingpian/core/index.php?s=/admin/menu/select_join&select_id=' + index, '800px', '600px');
        },
        //底部导航
        select_di_menu: function (index) {
            lay_open('选择功能', host + '/addons/yb_mingpian/core/index.php?s=/admin/menu/select_menu&select_id=' + index, '800px', '600px');
        },
        //表单选择
        select_form_all: function (index) {
            lay_open('选择表单', host + 'addons/yb_mingpian/core/index.php?s=/admin/menu/menu_select_2&type=edit_form&path=/yb_mingpian/pages/form/index', '800px', '600px');
        },
        clip_menu: function (index) {
            if (bannerVM.now_right_data.list.length == 2) {
                layer.msg('不能少于2个菜单！', {icon: 2, time: 1000});
                return false;
            }
            bannerVM.now_right_data.list.splice(index, 1);
        },
        //删除图片
        remove_img: function (index) {
            if (bannerVM.now_right_data.list.length == 1) {
                layer.msg('不能小于1！', {icon: 2, time: 1000});
                return false;
            }
            for (var i = 1; i <= 100; i++) {
                if (typeof (bannerVM.now_right_data.list[index + i]) != 'undefined') {
                    bannerVM.now_right_data.list[index + i]['top'] = bannerVM.now_right_data.list[index + i]['top'] - 135;
                } else {
                    break;
                }
            }
            bannerVM.now_right_data.list.splice(index, 1);
            bannerVM.add_top -= 135;
            bannerVM.add_h -= 135;
            bannerVM.now_right_data['topimg'] = bannerVM.now_right_data.list[0]['imgurl'];
        }
    }
});