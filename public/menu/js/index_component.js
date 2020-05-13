var snap = location.href;
var cuff = snap.split('addons');
var host = cuff[0];
//轮播图
Vue.component('banner', {
    props: ['topimg','jiaodian_color','index','list','ly_height','juedui_height','jiaodian_dots'],
    template: '<li :style="'+"'height:'"+'+juedui_height+'+"'px;'"+'" class="ui-draggable pr" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div class="swiperImg"><div v-on:click="onc_banner(index,$event)" class="imgList"><ul><li><img :src="topimg"></li></ul></div><div class="buttle" :style="'+"'display:'"+'+jiaodian_dots+'+"';'"+'"><i  v-on:click="select_jiaodian(index)" v-for="(right,index) in list" :style="'+"'background:'"+'+jiaodian_color+'+"';'"+'" class="on"></i></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#form_edit_banner").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            console.log(index);
            $(".activeMod").children().children().removeClass('yb_select');
            //$(e.target).parents().find('.ui-draggable').eq(index).addClass('select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            //$(e.target).parent().parent().parent().parent().parent().addClass("clicked").siblings().removeClass('clicked');
            $("#form_edit_banner").css("display",'block').siblings().css("display",'none');
            bannerVM.banner=bannerVM.all_data[index]['list'];
            $('#banner_color_r').val(bannerVM.all_data[index]['jiaodian_color']);
            $('#banner_height').val(bannerVM.all_data[index]['ly_height']);
            $('#this_banner_height').html(bannerVM.all_data[index]['ly_height']*10);
            $("#banner_color_text").html(bannerVM.all_data[index]['jiaodian_color']);
            $('#banner_width').val(bannerVM.all_data[index]['ly_width']);
            bannerVM.add_h = bannerVM.banner.length *135+135;
            bannerVM.add_top = bannerVM.banner.length *135+15;
            bannerVM.now_index = index;
            // Vue.set(example1.items, 0, bannerVM.all_data[index]['list'];)
        },
        select_jiaodian:function (index) {
            this.onc_banner(this.index);
            //console.log();
            //console.log(index);
            for (var i=0;i<bannerVM.banner.length;i++){
                if (i==index){
                    bannerVM.all_data[bannerVM.now_index]['topimg']= bannerVM.banner[index]['imgurl'];
                    bannerVM.banner[index]['jiaodian_color']=bannerVM.all_data[bannerVM.now_index]['jiaodian_color'];
                }else {
                    bannerVM.banner[i]['jiaodian_color']='#898989';
                }
            }
        }
    },
})
//广告位
Vue.component('advert', {
    props: ['imgurl','index','list','ly_width','ly_height','juedui_height'],
    template: '<li :style="'+"'width:'"+'+100+'+"'%;height:'"+'+juedui_height+'+"'px;'"+'" class="ui-draggable" data-title="点击拖动到左侧" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div class="swiperImg pr"><div v-on:click="onc_banner(index,$event)" class="imgList"><ul><li style="float:left;" v-for="right in list" :style="'+"'width:'"+'+right.width+'+"'%;'"+""+'"><img style="height: 100%" :src="right.imgurl"></li></ul></div><div class="buttle" style="display: block;"></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#form_edit_advert").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            //$(e.target).parent().parent().parent().parent().parent().parent().addClass("clicked").siblings().removeClass('clicked');
            $("#form_edit_advert").css("display",'block').siblings().css("display",'none');
            bannerVM.advert=bannerVM.all_data[index]['list'];
            $('#advert_width').val(bannerVM.all_data[index]['ly_width']);
            $('#advert_height').val(bannerVM.all_data[index]['ly_height']);
            $('#what_advert_height').html(bannerVM.all_data[index]['ly_height']*10);
            bannerVM.add_h = bannerVM.advert.length *135+135;
            bannerVM.add_top = bannerVM.advert.length *135+15;
            bannerVM.now_index = index;
        },
    },
})
//宫格导航
Vue.component('navigation', {
    props: ['index','list','font_size','color','radian','layout'],
    template: '<li class="ui-draggable" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div v-on:click="onc_banner(index,$event)" :class="\'cubeNavigationArea column-\'+layout+\' clearfix\'"><div v-for="c in list" class="cubeLink cubeLink1 Ggdh" style="height:100px"> <a class="cubeLink_a" href="javascript:;"> <div class="cubeLink_bg"></div> <div class="cubeLink_curtain"></div> <div class="cubeLink_ico icon-cube" :style="'+"'background-image:url('"+'+c.imgurl+'+"');border-radius:'"+'+radian+'+"'px;'"+'"></div> <div class="cubeLink_text g_link"> <div  :style="'+"'font-size:'"+'+font_size+'+"'px;color:'"+'+color+'+"';'"+'"  class="cubeLink_text_p "><em>{{c.name}}</em> <p class="cubeLink_subText_p"></p> </div> </div> </a> </div>    </div></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#from_edit_iconnav").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            $("#from_edit_iconnav").css("display",'block').siblings().css("display",'none');
            bannerVM.catenav=bannerVM.all_data[index]['list'];
            $('#catenav_corners').val(bannerVM.all_data[index]['radian']);
            $('#catenav_corners_height').val(bannerVM.all_data[index]['radian']);
            $('#catenav_color').val(bannerVM.all_data[index]['color']);
            $("#catenav_color_text").html(bannerVM.all_data[index]['color']);
            if (bannerVM.all_data[index]['font_size']==12){
                $("#Ggdh_fontsize_s").prop("checked", "checked");
            }
            else if (bannerVM.all_data[index]['font_size']==14){
                $("#Ggdh_fontsize_m").prop("checked", "checked");
            }
            else{
                $("#Ggdh_fontsize_l").prop("checked", "checked");
            }
            if (bannerVM.all_data[index]['layout'].replace(/[^0-9]/ig,"")==2) {
                $("#editlayout_2").prop("checked", "checked");
            }
            else if(bannerVM.all_data[index]['layout'].replace(/[^0-9]/ig,"")==3){
                $("#editlayout_3").prop("checked", "checked");
            }
            else if(bannerVM.all_data[index]['layout'].replace(/[^0-9]/ig,"")==4){
                $("#editlayout_4").prop("checked", "checked");
            }
            else {
                $("#editlayout_5").prop("checked", "checked");
            }
            bannerVM.add_h = bannerVM.catenav.length *135+135;
            bannerVM.add_top = bannerVM.catenav.length *135+15;
            bannerVM.now_index = index;
        },
    },
})
//标题
Vue.component('headline', {
    props: ['name','index','list','font_size','color','bg_color','layout','time_key'],
    // template: '<li class="ui-draggable" title="点击进行修改,拖动交换位置." name="id_title"><div v-on:click="del_left(index)" class="deleteButton"></div><div v-on:click="onc_banner(index,$event)" style="" :style="'+"'background-color:'"+'+bg_color+'+"';'"+'" class="pureText"><div class="wrap"><a  class="aframe"></a><span class="middle_titleO"></span><span class="seven_titleO"></span><span :style="'+"'font-size:'"+'+font_size+'+"'px;color:'"+'+color+'+"';'"+'" class="Bt_title">{{name}}</span><span class="seven_titleS"></span><span class="middle_titleS"></span></div></div></li>',
    template: '<li :id="\'title_\'+time_key" class="ui-draggable" title="点击进行修改,拖动交换位置." name="id_title"><div v-on:click="del_left(index)" class="deleteButton"></div><div v-on:click="onc_banner(index,$event)" style="" :style="'+"'background-color:'"+'+bg_color+'+"';'"+'" class="pureText"><div class="wrap"><a  class="aframe"></a><span class="middle_titleO"></span><span class="seven_titleO"></span><span :style="\'font-size:\'+ font_size+\'px;color:\'+color" class="Bt_title">{{name}}</span><span class="seven_titleS"></span><span class="middle_titleS"></span></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#form_edit_title").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            $("#form_edit_title").css("display",'block').siblings().css("display",'none');
            bannerVM.columntitle=bannerVM.all_data[index]['list'];
            $('#titlebgcolorSet').val(bannerVM.all_data[index]['bg_color']);
            $('#Bt_TitlecolorSet').val(bannerVM.all_data[index]['color']);
            $('#Bt_textarea').val(bannerVM.all_data[index]['name']);
            $("#Bt_TitlecolorSet_text").html(bannerVM.all_data[index]['color']);
            $("#titlebgcolorSet_text").html(bannerVM.all_data[index]['bg_color']);
            if (bannerVM.all_data[index]['font_size']==14){
                $("#Bt_fontsize_s").prop("checked", "checked");
            }
            else if (bannerVM.all_data[index]['font_size']==18){
                $("#Bt_fontsize_m").prop("checked", "checked");
            }
            else{
                $("#Bt_fontsize_l").prop("checked", "checked");
            }
            if(bannerVM.all_data[index]['style']==1){
                $("#pureTitle1").prop("checked", "checked");
            }
            if(bannerVM.all_data[index]['style']==2){
                $("#pureTitle2").prop("checked", "checked");
            }
            if(bannerVM.all_data[index]['style']==3){
                $("#pureTitle3").prop("checked", "checked");
            }
            if(bannerVM.all_data[index]['style']==4){
                $("#pureTitle4").prop("checked", "checked");
            }
            if(bannerVM.all_data[index]['style']==5){
                $("#pureTitle5").prop("checked", "checked");
            }
            if(bannerVM.all_data[index]['style']==6){
                $("#pureTitle6").prop("checked", "checked");
            }
            if(bannerVM.all_data[index]['style']==7){
                $("#pureTitle7").prop("checked", "checked");
            }
            if(bannerVM.all_data[index]['style']==8){
                $("#pureTitle8").prop("checked", "checked");
            }
            // bannerVM.add_h = bannerVM.columntitle.length *245;
            //bannerVM.add_top = bannerVM.columntitle.length *135;
            bannerVM.now_index = index;
        },
    },
})
//辅助空白
Vue.component('blank', {
    props: ['ly_height','index','list','bg_color'],
    template: '<li class="ui-draggable" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)"  class="deleteButton"></div><div v-on:click="onc_banner(index,$event)" class="Parting" :style="'+"'height:'"+'+ly_height+'+"'px;background-color:'"+'+bg_color+'+"';'"+'"></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#form_edit_blank").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            $("#form_edit_blank").css("display",'block').siblings().css("display",'none');
            $('#blank_color').val(bannerVM.all_data[index]['bg_color']);
            $('#blank_height').val(bannerVM.all_data[index]['ly_height']);
            $('#blank_height_number').html(bannerVM.all_data[index]['ly_height']);
            $("#blank_color_text").html(bannerVM.all_data[index]['bg_color']);
            bannerVM.now_index = index;
        },
    },
})
//搜索框
Vue.component('search', {
    props: ['index','bg_color','li_bg_color'],
    template: '<li  class="ui-draggable" :style="'+"'background-color:'"+'+li_bg_color+'+"';'"+'"  title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><section :style="'+"'background-color:'"+'+bg_color+'+"';'"+'" v-on:click="onc_banner(index,$event)" class="members_search"><input type="text" id="search" value="" readonly placeholder="搜索：请输入关键字"><button type="submit"></button></section></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#form_edit_search").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            $("#form_edit_search").css("display",'block').siblings().css("display",'none');
            $('#search_textarea').val(bannerVM.all_data[index]['default']);
            $('#search_color').val(bannerVM.all_data[index]['bg_color']);
            $('#search_bj_color').val(bannerVM.all_data[index]['li_bg_color']);
            $('#search_text_color').val(bannerVM.all_data[index]['text_color']);
            $("#search_color_text").html(bannerVM.all_data[index]['bg_color']);
            $("#search_bj_color_text").html(bannerVM.all_data[index]['li_bg_color']);
            $("#search_text_color_text").html(bannerVM.all_data[index]['text_color']);
            if (bannerVM.all_data[index]['search_style']=='0'){
                $("#search_style_1").prop("checked", "checked");
            }
            if (bannerVM.all_data[index]['search_style']=='1'){
                $("#search_style_2").prop("checked", "checked");
            }
            bannerVM.now_index = index;
        },
    },
})
//分割线
Vue.component('blankline', {
    props: ['index','color','bg_color','ly_height','margin','line'],
    template: '<li class="ui-draggable" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div v-on:click="onc_banner(index,$event)"  class="Parting" :style="'+"'background-color:'"+'+bg_color+'+"';height:'"+'+margin+'+"'px;'"+'" ><section class="custom-line-wrap"><hr :style="'+"'border-top-color:'"+'+color+'+"';border-width:'"+'+ly_height+'+"'px;border-top-style:'"+'+line+'+"''"+'"" class="custom-line"></section></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#form_edit_search").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            $("#form_edit_Parting").css("display",'block').siblings().css("display",'none');
            $("#Fgx_Color").val(bannerVM.all_data[index]['color']);
            $("#Fgx_bgColor").val(bannerVM.all_data[index]['bg_color']);
            $("#line_height").val(bannerVM.all_data[index]['ly_height']);
            $("#line_margin").val(bannerVM.all_data[index]['margin']);
            $("#Fgx_Color_text").html(bannerVM.all_data[index]['color']);
            $("#Fgx_bgColor_text").html(bannerVM.all_data[index]['bg_color']);
            $("#line_height_height").html(bannerVM.all_data[index]['ly_height']);
            $("#line_margin_height").html(bannerVM.all_data[index]['margin']);
            if (bannerVM.all_data[index]['line']=='solid'){
                $("#Fgx_style_1").prop("checked", "checked");
            }else if(bannerVM.all_data[index]['line']=='dashed'){
                $("#Fgx_style_2").prop("checked", "checked");
            }else {
                $("#Fgx_style_3").prop("checked", "checked");
            }
            //$('#search_textarea').val(bannerVM.all_data[index]['hot_search']);
            bannerVM.now_index = index;
        },
    },
})
//图文集
Vue.component('article_list', {
    props: ['list','index','bg_color','title_size','title_color','style_width','style_height','style_num','text_width'],
    template: '<li :style="'+"'background-color:'"+'+bg_color+'+"';'"+'" class="ui-draggable" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div  v-on:click="onc_banner(index,$event)" :class="\'cubeNavigationArea column-\'+style_num+\' clearfix\'"><div class="imgList" v-for="d in list"><div :style="style_height" class="cubeLink cubeLink1"><div :style="style_width" class="_img"><img :src="d.imgurl"></div><div :style="'+"'width:'"+'+text_width+'+"';'"+'" class="_text"><p :style="'+"'font-size:'"+'+title_size+'+"'px;color:'"+'+title_color+'+"';'"+'" class="title">{{d.name}}</p><p style="font-size: 14px;">{{d.description}}</p></div></div></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#form_edit_imgtextlist").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            $("#form_edit_imgtextlist").css("display",'block').siblings().css("display",'none');
            $("#imgtextColorSet").val(bannerVM.all_data[index]['bg_color']);
            $("#pureTitlecolorSet").val(bannerVM.all_data[index]['title_color']);
            $("#add_img_list_val").val(bannerVM.all_data[index]['add_num']);
            $("#imgtextColorSet_text").html(bannerVM.all_data[index]['bg_color']);
            $("#pureTitlecolorSet_text").html(bannerVM.all_data[index]['title_color']);
            $("#select_img_id").find("option[value = '"+bannerVM.all_data[index]['add_cate']+"']").attr("selected","selected");
            bannerVM.imgtextlist=bannerVM.all_data[index]['list'];
            if (bannerVM.all_data[index]['layout']==1){
                $("#cateLists1").prop("checked", "checked");
            }
            else if(bannerVM.all_data[index]['layout']==2) {
                $("#cateLists2").prop("checked", "checked");
            }
            else if(bannerVM.all_data[index]['layout']==3) {
                $("#cateLists3").prop("checked", "checked");
            }
            if (bannerVM.all_data[index]['title_size']=='15'){
                $("#Twj_fontsize_s").prop("checked", "checked");
            }else if(bannerVM.all_data[index]['title_size']=='17'){
                $("#Twj_fontsize_m").prop("checked", "checked");
            }else {
                $("#Twj_fontsize_l").prop("checked", "checked");
            }
            if(bannerVM.all_data[index]['add_type']=='1'){
                $("#img_add_type_zid").css('display','none');
                $("#img_type_anually").prop("checked", "checked");
            }
            console.log(bannerVM.all_data[index]['add_type']);
            if(bannerVM.all_data[index]['add_type']=='2'){
                $("#img_add_type_zid").css('display','block');
                $("#img_type_automatic").prop("checked", "checked");
            }
            if (bannerVM.all_data[index]['add_sort']=='time'){
                $("#img_type_time").prop("checked", "checked");
            }
            else if(bannerVM.all_data[index]['add_sort']=='pop'){
                $("#img_type_pop").prop("checked", "checked");
            }else {
                $("#img_type_sales").prop("checked", "checked");
            }
            bannerVM.add_h = bannerVM.imgtextlist.length *135+135;
            bannerVM.add_top = bannerVM.imgtextlist.length *135+15;
            bannerVM.now_index = index;
        },
    },
})
//商品集
Vue.component('goods', {
    props: ['list','index','bg_color','layout','title_size','title_color'],
    template: '<li title="点击进行修改,拖动交换位置." class="ui-draggable"><div v-on:click="del_left(index)" class="deleteButton"></div><div  v-on:click="onc_banner(index,$event)" :class="\'cubeNavigationArea column-\'+layout+\' clearfix\'"><div v-for="g in list" class="cubeLink cubeLink1 Ggdh" style="height: 200px; border-bottom: 1px solid rgb(229, 229, 229); border-right: 1px solid rgb(229, 229, 229);"><a href="javascript:;" class="cubeLink_a"><div class="cubeLink_bg"></div> <div class="cubeLink_curtain"></div> <div class="cubeLink_ico icon-cube" :style="'+"'background-image:url('"+'+g.imgurl+'+"');border-radius: 0px; width: 100px; height: 100px; background-size: 100px 100px;'"+'"></div> <div class="cubeLink_text g_link" style="position: relative;"><div class="cubeLink_text_p " style="font-size: 12px; color: rgb(0, 0, 0);"><em :style="'+"'color:'"+'+title_color+'+"';font-size:'"+'+title_size+'+"'px;font-weight: bold; padding-left: 5px;'"+'">{{g.name}}</em><em style="padding-left: 5px;font-size: 10px;color: #68838B">{{g.description}}</em> <p class="cubeLink_subText_p" style="margin-bottom: 1rem; color: rgb(255, 0, 0); padding-left: 5px;">￥{{g.price}}</p><p public="" static="" images="" style="display: block; width: 25px; height: 25px; border-radius: 50%; position: absolute; right: 10px; bottom: 5px; margin-bottom: 0px;"></p></div></div></a></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#from_edit_goodlist").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            $("#from_edit_goodlist").css("display",'block').siblings().css("display",'none');
            $("#goods_color").val(bannerVM.all_data[index]['bg_color']);
            $("#add_goods_list_val").val(bannerVM.all_data[index]['add_num']);
            $("#goods_color_text").html(bannerVM.all_data[index]['bg_color']);
            $("#select_cate_id").find("option[value = '"+bannerVM.all_data[index]['add_cate']+"']").attr("selected","selected");
            if (bannerVM.all_data[index]['title_size']=='12'){
                $("#Goods_fontsize_s").prop("checked", "checked");
            }else if(bannerVM.all_data[index]['title_size']=='14'){
                $("#Goods_fontsize_m").prop("checked", "checked");
            }else {
                $("#Goods_fontsize_l").prop("checked", "checked");
            }
            if(bannerVM.all_data[index]['layout']==1){
                $("#editgoods_1").prop("checked", "checked");
            }
            else if(bannerVM.all_data[index]['layout']==2){
                $("#editgoods_2").prop("checked", "checked");
            }
            else if(bannerVM.all_data[index]['layout']==3){
                $("#editgoods_3").prop("checked", "checked");
            }
            else if(bannerVM.all_data[index]['layout']==4){
                $("#editgoods_4").prop("checked", "checked");
            }
            if(typeof bannerVM.all_data[index]['add_type']=="undefined"){
                $("#goods_add_type_zid").css('display','none');
                $("#Goods_type_anually").prop("checked", "checked");
            }
            if(bannerVM.all_data[index]['add_type']=='1'){
                $("#goods_add_type_zid").css('display','none');
                $("#Goods_type_anually").prop("checked", "checked");
            }
            if(bannerVM.all_data[index]['add_type']=='2'){
                $("#goods_add_type_zid").css('display','block');
                $("#Goods_type_automatic").prop("checked", "checked");
            }
            if (bannerVM.all_data[index]['add_sort']=='time'){
                $("#Goods_type_time").prop("checked", "checked");
            }
            else if(bannerVM.all_data[index]['add_sort']=='pop'){
                $("#Goods_type_pop").prop("checked", "checked");
            }else {
                $("#Goods_type_sales").prop("checked", "checked");
            }
            bannerVM.goodlist=bannerVM.all_data[index]['list'];
            bannerVM.add_h = bannerVM.goodlist.length *135+135;
            bannerVM.add_top = bannerVM.goodlist.length *135+15;
            bannerVM.now_index = index;
        },
    },
})
//按钮
Vue.component('edit_button', {
    props: ['list','index','button_name','button_radius','img_style','button_border','button_bg_color','button_title_color','button_border_color'],
    template: '<li class="ui-draggable" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div v-on:click="onc_banner(index)" class="button_Text" style="text-align:center;"> <div class="wrap" :style="'+"'border-radius:'"+'+button_radius+'+"'px;border:'"+'+button_border+'+"'px solid;border-color:'"+'+button_border_color+'+"';background-color:'"+'+button_bg_color+'+"';padding: 0 20px;'"+'"><img v-if="img_style == 1" v-for="item in list" :src="item.imgurl" style="width:20px;margin: 0px 15px 0px 0px;vertical-align: middle;"><span :style="'+"'color:'"+'+button_title_color+'+"';'"+'">{{button_name}}</span></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#form_edit_button").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            $("#form_edit_button").css("display",'block').siblings().css("display",'none');
            $("#button_name").val(bannerVM.all_data[index]['button_name']);
            $("#button_radius").val(bannerVM.all_data[index]['button_radius']);
            $("#AnbgcolorSet").val(bannerVM.all_data[index]['button_bg_color']);
            $("#Title_ancolor").val(bannerVM.all_data[index]['button_title_color']);
            $("#Title_bkcolor").val(bannerVM.all_data[index]['button_border_color']);
            $("#AnbgcolorSet_text").html(bannerVM.all_data[index]['button_bg_color']);
            $("#Title_ancolor_text").html(bannerVM.all_data[index]['button_title_color']);
            $("#Title_bkcolor_text").html(bannerVM.all_data[index]['button_border_color']);
            $("#button_radius_height").html(bannerVM.all_data[index]['button_radius']);
            if (bannerVM.all_data[index]['button_border']=='1'){
                $("#An_bg_show").prop("checked", "checked");
            }else{
                $("#An_bg_hide").prop("checked", "checked");
            }
            if (bannerVM.all_data[index]['img_style']=='1'){
                $("#An_pic_show").prop("checked", "checked");
            }else {
                $("#An_pic_hide").prop("checked", "checked");
            }
            bannerVM.edit_button=bannerVM.all_data[index]['list'];
            // bannerVM.add_h = bannerVM.goodlist.length *135+135;
            // bannerVM.add_top = bannerVM.goodlist.length *135+15;
            bannerVM.now_index = index;
        },
    },
})
//地图
Vue.component('position', {
    props: ['index','position_name','is_display'],
    template: '<li class="ui-draggable" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div v-on:click="onc_banner(index)" class="position"><img v-if="is_display==2" src="'+ host +'addons/yb_mingpian/core/public/menu/images/pos.png"> <div v-if="is_display==1" class="wrap"> <em class="fr iconfont icon-arrow-right"></em> <span class="title"><i class="Hui-iconfont Hui-iconfont-weizhi"></i>{{position_name}}</span> </div> </div></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#form_edit_position").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            $("#form_edit_position").css("display",'block').siblings().css("display",'none');
            $("#Dlwz_textarea").val(bannerVM.all_data[index]['position_name']);
            $("#longitude").val(bannerVM.all_data[index]['longitude']);
            $("#latitude").val(bannerVM.all_data[index]['latitude']);
            if (bannerVM.all_data[index]['is_display']==1){
                $("#position_editlayout_1").prop("checked", "checked");
            }else {
                $("#position_editlayout_2").prop("checked", "checked");
            }
            bannerVM.now_index = index;
        },
    },
})
//富文本
Vue.component('rich_text', {
    props: ['index','content','bg_color'],
    template: '<li class="ui-draggable"title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div v-on:click="onc_banner(index)" style="" :style="'+"'background-color:'"+'+bg_color+'+"';'"+'" class="control"><div :id="'+"'um_text-'"+'+index+'+"';'"+'" class="custom-richtext" v-html="content"></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#form_rich_text").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            $("#form_rich_text").css("display",'block').siblings().css("display",'none');
            $("#rich_bg_color_text").html(bannerVM.all_data[index]['bg_color']);
            $('#um_text-'+index).html(bannerVM.all_data[index]['content']);
            //document.getElementById("ueditor_0").contentDocument.body.innerHTML=bannerVM.all_data[index]['content'];
            ue.setContent(bannerVM.all_data[index]['content']);
            $("#rich_bg_color").val(bannerVM.all_data[index]['bg_color']);
            bannerVM.now_index = index;
        },
    },
})
//图片列表
Vue.component('edit_piclist', {
    props: ['index','list','layout','pic_style','html_style','pic_radius'],
    template: '<li class="ui-draggable" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div v-on:click="onc_banner(index,$event)" :class="\'cubeNavigationArea column-\'+layout+\' clearfix\'">' +
        '<div v-for="item in list" class="cubeLink cubeLink1">' +
        '<a class="cubeLink_a" style="height: auto;" href="javascript:;"><div class="cubeLink_curtain"></div>' +
        '<div :style="\'border-radius:\'+pic_radius+\'%;width:100%\'" class="cubeLink_ico1 icon-cube">' +
        '<img :src="item.imgurl" width="100%" height="auto"></div> ' +
        '<div  v-if="pic_style < 3"  class="cubeLink_text1 g_link"><div class="cubeLink_text_p">' +
        '<em :style="html_style">{{item.title}}</em> ' +
        '<p class="cubeLink_subText_p" style="margin:0px;"></p></div></div></a></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#form_edit_piclist").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            $("#form_edit_piclist").css("display",'block').siblings().css("display",'none');
            if(bannerVM.all_data[index]['layout']==1){
                $("#editpiclayout_1").prop("checked", "checked");
            }
            else if(bannerVM.all_data[index]['layout']==2){
                $("#editpiclayout_2").prop("checked", "checked");
            }
            else if(bannerVM.all_data[index]['layout']==3){
                $("#editpiclayout_3").prop("checked", "checked");
            }
            else if(bannerVM.all_data[index]['layout']==4){
                $("#editpiclayout_4").prop("checked", "checked");
            }
            if (bannerVM.all_data[index]['pic_style']==1){
                $("#purePiclist1").prop("checked", "checked");
            }
            else if (bannerVM.all_data[index]['pic_style']==2){
                $("#purePiclist2").prop("checked", "checked");
            }
            else if (bannerVM.all_data[index]['pic_style']==3){
                $("#purePiclist3").prop("checked", "checked");
            }
            $("#piclist_radius").val(bannerVM.all_data[index]['pic_radius']);
            $("#piclist_radius_span").html(bannerVM.all_data[index]['pic_radius']);
            bannerVM.edit_piclist=bannerVM.all_data[index]['list'];
            bannerVM.add_h = bannerVM.edit_piclist.length *135+135;
            bannerVM.add_top = bannerVM.edit_piclist.length *135+15;
            bannerVM.now_index = index;
        },
    },
})
//悬浮按钮
Vue.component('floaticon', {
    props: ['index','form_bottom','form_margin','form_transparent','list'],
    template: '<li id="floaticon_button_this" :style="\'bottom:\'+form_bottom+\'px;left:\'+form_margin+\'px\'" class="ui-draggable"  title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div  v-on:click="onc_banner(index,$event)" class="floaticon pr"><div class="icon_right"><img :style="\'opacity:\'+form_transparent+\';width:35px;height:35px;\'" v-for="item in list" :src="item.imgurl" alt=""></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#form_edit_floaticon").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            $("#form_edit_floaticon").css("display",'block').siblings().css("display",'none');
            $("#floaticon_button").val(bannerVM.all_data[index]['b_form_bottom']);
            $("#floaticon_margin").val(bannerVM.all_data[index]['b_form_margin']);
            $("#floaticon_transparent").val(bannerVM.all_data[index]['b_form_transparent']);
            $("#floaticon_button_text").html(bannerVM.all_data[index]['b_form_bottom']);
            $("#floaticon_margin_text").html(bannerVM.all_data[index]['b_form_margin']);
            bannerVM.floaticon=bannerVM.all_data[index]['list'];
            bannerVM.now_index = index;
        },
    },
})
//悬浮客服
Vue.component('customer', {
    props: ['index','form_bottom','form_margin','form_transparent','imgurl'],
    template: '<li id="customer_button_this" :style="\'bottom:\'+form_bottom+\'px;left:\'+form_margin+\'px\'" class="ui-draggable"  title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div  v-on:click="onc_banner(index,$event)" class="floaticon pr"><div class="icon_right" ><img :style="\'opacity:\'+form_transparent+\';width:35px;height:35px;\'" :src="imgurl" alt=""></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#form_edit_Customer").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            $("#form_edit_Customer").css("display",'block').siblings().css("display",'none');
            $("#Customer_button").val(bannerVM.all_data[index]['b_form_bottom']);
            $("#Customer_margin").val(bannerVM.all_data[index]['b_form_margin']);
            $("#Customer_transparent").val(bannerVM.all_data[index]['b_form_transparent']);
            $("#Customer_img").attr('src',bannerVM.all_data[index]['imgurl']);
            $("#Customer_button_text").html(bannerVM.all_data[index]['b_form_bottom']);
            $("#Customer_margin_text").html(bannerVM.all_data[index]['b_form_margin']);
            bannerVM.now_index = index;
        },
    },
})
//悬浮电话
Vue.component('edit_phone', {
    props: ['index','form_bottom','form_margin','form_transparent','imgurl'],
    template: '<li id="phone_button_this" :style="\'bottom:\'+form_bottom+\'px;left:\'+form_margin+\'px\'" class="ui-draggable"  title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div  v-on:click="onc_banner(index,$event)" class="floaticon pr"><div class="icon_right"><img :style="\'opacity:\'+form_transparent+\';width:35px;height:35px;\'" :src="imgurl" alt=""></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#form_edit_phone").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            $("#form_edit_phone").css("display",'block').siblings().css("display",'none');
            $("#phone_button").val(bannerVM.all_data[index]['b_form_bottom']);
            $("#phone_margin").val(bannerVM.all_data[index]['b_form_margin']);
            $("#phone_transparent").val(bannerVM.all_data[index]['b_form_transparent']);
            $("#phone_img").attr('src',bannerVM.all_data[index]['imgurl']);
            $("#this_phone_val").val(bannerVM.all_data[index]['phone']);
            $("#phone_button_text").html(bannerVM.all_data[index]['b_form_bottom']);
            $("#phone_margin_text").html(bannerVM.all_data[index]['b_form_margin']);
            bannerVM.now_index = index;
        },
    },
})
//公告
Vue.component('announcement', {
    props: ['index','color','bg_color','title','imgurl'],
    template: '<li id="set_announcement" :style="'+"'background-color:'"+'+bg_color+'+"''"+'" class="ui-draggable" v-on:click="onc_banner(index,$event)" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div class="floaticon pr"><div style="float: left;overflow: hidden;text-overflow: ellipsis;display: -webkit-box;-webkit-line-clamp:1;-webkit-box-orient: vertical;"><img style="width: 40px;height: 30px;" :src="imgurl" alt=""><span :style="'+"'color:'"+'+color+'+"';font-size:12px;'"+'">{{title}}</span></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#form_edit_announcement").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            $("#form_edit_announcement").css("display",'block').siblings().css("display",'none');
            $("#announcement_bgColor_text").html(bannerVM.all_data[index]['bg_color']);
            $("#announcement_Color").val(bannerVM.all_data[index]['color']);
            $("#announcement_bgColor").val(bannerVM.all_data[index]['bg_color']);
            $("#announcement_Color_text").html(bannerVM.all_data[index]['color']);
            $("#announcement_textarea").val(bannerVM.all_data[index]['title']);
            $("#announcement_img").attr('src',bannerVM.all_data[index]['imgurl']);
            bannerVM.now_index = index;
        },
    },
})
//流量主
Vue.component('ad', {
    props: ['index','height','img'],
    template: '<li class="ui-draggable" :style="'+"'height:'"+'+height+'+"'px;'"+'" v-on:click="onc_banner(index,$event)" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><img style="width: 100%;" :src="img" alt=""></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#form_edit_ad").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            $("#form_edit_ad").css("display",'block').siblings().css("display",'none');
            $("#what_ad_height").html(bannerVM.all_data[index]['height']);
            $("#ad_height").val(bannerVM.all_data[index]['height']);
            $("#ad_ad_id").val(bannerVM.all_data[index]['ad_id']);
            bannerVM.now_index = index;
        },
    },
})
//视频
Vue.component('edit_video', {
    props: ['index','video_height','video_url','imgurl'],
    template: '<li title="点击进行修改,拖动交换位置" class="ui-draggable"><div v-on:click="del_left(index)" class="deleteButton"></div><div v-on:click="onc_banner(index,$event)" class="video_sub" :style="\'margin-left: 0px; margin-right: auto; margin-top: 0px; position: relative;height:\'+video_height+\'px;\'"><video id="my_video" :src="video_url" :poster="imgurl" controls>您的浏览器不支持 video 标签</video> <div class="video_unapplet" style="display: none;"></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#form_edit_video").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            $("#form_edit_video").css("display",'block').siblings().css("display",'none');
            $("#vidoe_height").val(bannerVM.all_data[index]['vidoe_height']);
            $("#Sp_textarea").val(bannerVM.all_data[index]['video_url']);
            if (bannerVM.all_data[index]['video_type']==0){
                $("#vidoe_url1").prop("checked", "checked");
            }else {
                $("#vidoe_url2").prop("checked", "checked");
            }
            $("#this_video_img").attr('src',bannerVM.all_data[index]['imgurl']);
            bannerVM.now_index = index;
        },
        video_play:function () {
            $("#my_video").play();
        }
    },
})
//音频
Vue.component('edit_music', {
    props: ['index','music_url','title','author','imgurl'],
    template: '<li id="edit_music" class="ui-draggable" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div v-on:click="onc_banner(index,$event)" style="width:310px;height:100px"> <div style="padding:12px;">  <div style="    border: 1px solid #a89e9e;"><img id="audio_img" :src="imgurl" style="width:70px;cursor:pointer;height:70px;"><img id="audio_stare" src="'+host+'addons/yb_mingpian/core/public/menu/images/audio_stare.png" style="width:80px;cursor:pointer;height:70px;position:absolute;top:10px;left:10px;"><div style="float: right;width: 50px;margin:4px 4px;">00:00</div> <div style="float:right;width: 120px;text-align: center;height: 70px;"><p style="height: 35px;margin: 0;font-size: 15px;line-height: 35px;" class="audio_title">{{title}}</p><p class="audio_desc">{{author}}</p>   </div>  </div> </div> <audio id="my_audio" controls="controls" src=""></audio></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#form_edit_audio").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            $("#form_edit_audio").css("display",'block').siblings().css("display",'none');
            $("#edit_music_url").val(bannerVM.all_data[index]['music_url']);
            $("#this_music_title").val(bannerVM.all_data[index]['title']);
            $("#this_music_author").val(bannerVM.all_data[index]['author']);
            $("#this_music_img").val(bannerVM.all_data[index]['imgurl']);
            bannerVM.now_index = index;
        },
    },
})
//评论
Vue.component('comment_s', {
    props: ['index'],
    template: '<li title="点击进行修改,拖动交换位置" class="ui-draggable"><div v-on:click="del_left(index)" class="deleteButton"></div><img v-on:click="onc_banner(index)" src="'+ host +'addons/yb_mingpian/core/public/menu/images/comment.jpg"></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#form_edit_comment").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            $("#form_edit_comment").css("display",'block').siblings().css("display",'none');
            $("#comment_count").val(bannerVM.all_data[index]['is_display']);
            $('#is_comment_count_text').html(bannerVM.all_data[index]['is_display']);
            bannerVM.now_index = index;
        },
    },
})
//拼团
Vue.component('fight_group', {
    props: ['list','index','bg_color','title_size','title_color','style_width','style_height'],
    template: '<li :style="'+"'background-color:'"+'+bg_color+'+"';'"+'" class="ui-draggable" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div  v-on:click="onc_banner(index,$event)" class="swiper"><div class="imgList" v-for="d in list"><div :style="style_height" class="imgtext"><div :style="style_width" class="_img"><img :src="d.imgurl"></div><div class="_text"><p :style="'+"'font-size:'"+'+title_size+'+"'px;color:'"+'+title_color+'+"';'"+'" class="title">{{d.name}}</p><p style="font-size: 14px;">{{d.description}}</p></div></div></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#form_edit_imgtextlist").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            $("#form_fight_group").css("display",'block').siblings().css("display",'none');
            $("#groupbjtextColorSet").val(bannerVM.all_data[index]['bg_color']);
            $("#groupTitlecolorSet").val(bannerVM.all_data[index]['title_color']);
            $("#add_img_list_val").val(bannerVM.all_data[index]['add_num']);
            $("#select_img_id").find("option[value = '"+bannerVM.all_data[index]['add_cate']+"']").attr("selected","selected");
            bannerVM.fight_group_list=bannerVM.all_data[index]['list'];
            if (bannerVM.all_data[index]['layout']==1){
                $("#cateLists1").prop("checked", "checked");
            }else if(bannerVM.all_data[index]['layout']==2) {
                $("#cateLists2").prop("checked", "checked");
            }
            if (bannerVM.all_data[index]['title_size']=='15'){
                $("#Twj_fontsize_s").prop("checked", "checked");
            }else if(bannerVM.all_data[index]['title_size']=='17'){
                $("#Twj_fontsize_m").prop("checked", "checked");
            }else {
                $("#Twj_fontsize_l").prop("checked", "checked");
            }
            if(typeof bannerVM.all_data[index]['add_type']=="undefined"){
                $("#img_add_type_zid").css('display','none');
                $("#img_type_anually").prop("checked", "checked");
            }
            if(bannerVM.all_data[index]['add_type']=='1'){
                $("#img_add_type_zid").css('display','none');
                $("#img_type_anually").prop("checked", "checked");
            }
            if(bannerVM.all_data[index]['add_type']=='2'){
                $("#img_add_type_zid").css('display','block');
                $("#img_type_automatic").prop("checked", "checked");
            }
            if (bannerVM.all_data[index]['add_sort']=='time'){
                $("#img_type_time").prop("checked", "checked");
            }
            else if(bannerVM.all_data[index]['add_sort']=='pop'){
                $("#img_type_pop").prop("checked", "checked");
            }else {
                $("#img_type_sales").prop("checked", "checked");
            }
            bannerVM.add_h = bannerVM.fight_group_list.length *135+135;
            bannerVM.add_top = bannerVM.fight_group_list.length *135+15;
            bannerVM.now_index = index;
        },
    },
})
//三方图
Vue.component('tripartite', {
    props: ['list','index'],
    template: '<li><div v-on:click="del_left(index)" class="deleteButton"></div><div  v-on:click="onc_banner(index,$event)" class="weui-grids threeimage" style="display: flex;"><div href="javascript:;" style="flex: 1 1 0%;"><div class="weui-grid__icon" style="width: 159px; height: 159px;"><img :src="list[0].img" alt="" draggable="false" style="width: 159px; height: 159px;"></div></div><div href="javascript:;" style="flex: 1 1 0%;"><div class="weui-grid__icon" style="width: 159px; height: 79.5px;"><img :src="list[1].img" alt="" draggable="false" style="width: 159px; height: 79.5px;"></div> <div class="weui-grid__icon" style="width: 159px; height: 79.5px;"><img :src="list[2].img" alt="" draggable="false" style="width: 159px; height: 79.5px;;"></div></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#form_edit_tripartite").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            $("#form_edit_tripartite").css("display",'block').siblings().css("display",'none');
            bannerVM.tripartite_list=bannerVM.all_data[index]['list'];
            bannerVM.now_index = index;
        },
    },
})
//四方图
Vue.component('quartet', {
    props: ['list','index'],
    template: '<li><div v-on:click="del_left(index)" class="deleteButton"></div><div  v-on:click="onc_banner(index,$event)" class="weui-grids threeimage" style="display: flex;"><div href="javascript:;" style="flex: 1 1 0%;"><div class="weui-grid__icon" style="width: 212px; height: 318px;"><img :src="list[0].img" alt="" draggable="false" style="width: 212px; height: 318px;"></div></div><div href="javascript:;" style="flex: 1 1 0%;"><div class="weui-grid__icon" style="width: 106px; height: 106px;"><img :src="list[1].img" alt="" draggable="false" style="width: 106px; height: 106px;"></div> <div class="weui-grid__icon" style="width: 106px; height: 106px;"><img :src="list[2].img" alt="" draggable="false" style="width: 106px; height: 106px;"></div> <div class="weui-grid__icon" style="width: 106px; height: 106px;"><img :src="list[3].img" alt="" draggable="false" style="width: 106px; height: 106px;"></div></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#form_edit_quartet").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            $("#form_edit_quartet").css("display",'block').siblings().css("display",'none');
            bannerVM.quartet_list=bannerVM.all_data[index]['list'];
            bannerVM.now_index = index;
        },
    },
})
//留言板
Vue.component('message_board', {
    props: ['index'],
    template: '<li title="点击进行修改,拖动交换位置" class="ui-draggable"><div v-on:click="del_left(index)" class="deleteButton"></div><div v-on:click="onc_banner(index,$event)"  class="widget_wrap" style="padding:5px;background:#eee"><div style="background:#fff"><textarea class="tests" placeholder="感谢提出建议"></textarea></div><div class="input" style="border-bottom:1px solid #EEEEEE;margin-top:15px">姓名<input type="text" name="" placeholder="请输入姓名（可选）" style=""></div><div class="input">手机<input type="text" name="" placeholder="请输入手机号（可选）" style=""></div><div class="sub">提交</div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#form_message_board").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            $("#form_message_board").css("display",'block').siblings().css("display",'none');
            bannerVM.now_index = index;
        },
    },
})
//招聘
Vue.component('join_us', {
    props: ['index','list'],
    template: '<li title="点击进行修改,拖动交换位置" class="ui-draggable"><div v-on:click="del_left(index)" class="deleteButton"></div><div v-on:click="onc_banner(index)" class="control"><div v-for="g in list"><a class="cubeLink_a" href="javascript:;"><p>职位：{{g.name}}</p><p>待遇：{{g.pay}}</p><p>经验：{{g.exp}}&nbsp;&nbsp;学历：{{g.major}}</p></a></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#offweb_join").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            $("#offweb_join").css("display",'block').siblings().css("display",'none');
            bannerVM.joinlist=bannerVM.all_data[index]['list'];
            bannerVM.add_h = bannerVM.edit_piclist.length *135+135;
            bannerVM.add_top = bannerVM.edit_piclist.length *135+15;
            bannerVM.now_index = index;
        },
    },
})

//产品集
Vue.component('product', {
    props: ['list','index','bg_color','layout','title_size','title_color'],
    template: '<li title="点击进行修改,拖动交换位置." class="ui-draggable"><div v-on:click="del_left(index)" class="deleteButton"></div><div  v-on:click="onc_banner(index,$event)" :class="\'cubeNavigationArea column-\'+layout+\' clearfix\'"><div v-for="g in list" class="cubeLink cubeLink1 Ggdh" style="height: 200px; border-bottom: 1px solid rgb(229, 229, 229); border-right: 1px solid rgb(229, 229, 229);"><a href="javascript:;" class="cubeLink_a"><div class="cubeLink_bg"></div> <div class="cubeLink_curtain"></div> <div class="cubeLink_ico icon-cube" :style="'+"'background-image:url('"+'+g.pic+'+"');border-radius: 0px; width: 100px; height: 100px; background-size: 100px 100px;'"+'"></div> <div class="cubeLink_text g_link" style="position: relative;"><div class="cubeLink_text_p " style="font-size: 12px; color: rgb(0, 0, 0);"><em :style="'+"'color:'"+'+title_color+'+"';font-size:'"+'+title_size+'+"'px;font-weight: bold; padding-left: 5px;'"+'">{{g.name}}</em><em style="padding-left: 5px;font-size: 10px;color: #68838B">{{g.content}}</em> <p class="cubeLink_subText_p" style="margin-bottom: 1rem; color: rgb(255, 0, 0); padding-left: 5px;">￥{{g.price}}</p><p public="" static="" images="" style="display: block; width: 25px; height: 25px; border-radius: 50%; position: absolute; right: 10px; bottom: 5px; margin-bottom: 0px;"></p></div></div></a></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#product_list").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            $("#product_list").css("display",'block').siblings().css("display",'none');
            $("#product_color").val(bannerVM.all_data[index]['bg_color']);
            $("#add_product_list_val").val(bannerVM.all_data[index]['add_num']);
            $("#product_color_text").html(bannerVM.all_data[index]['bg_color']);
            if (bannerVM.all_data[index]['title_size']=='12'){
                $("#product_fontsize_s").prop("checked", "checked");
            }else if(bannerVM.all_data[index]['title_size']=='14'){
                $("#product_fontsize_m").prop("checked", "checked");
            }else {
                $("#product_fontsize_l").prop("checked", "checked");
            }
            if(bannerVM.all_data[index]['layout']==1){
                $("#editproduct_1").prop("checked", "checked");
            }
            else if(bannerVM.all_data[index]['layout']==2){
                $("#editproduct_2").prop("checked", "checked");
            }
            else if(bannerVM.all_data[index]['layout']==3){
                $("#editproduct_3").prop("checked", "checked");
            }
            bannerVM.products=bannerVM.all_data[index]['list'];
            bannerVM.add_h = bannerVM.products.length *135+135;
            bannerVM.add_top = bannerVM.products.length *135+15;
            bannerVM.now_index = index;
        },
    },
})
//门店
Vue.component('store_door', {
    props: ['index','imgurl','door_name','door_job'],
    template: '<li title="点击进行修改,拖动交换位置" class="ui-draggable"><div v-on:click="del_left(index)" class="deleteButton"></div><div  v-on:click="onc_banner(index,$event)"  class="widget_wrap" style="padding:5px"><div style="display:flex;"><img style="width:50px;height:50px" :src="imgurl"  class="top-img"><div style="flex:1;font-size:12px;"><div class="title-name">{{door_name}}</div><div>工作时间:<span class="time">{{door_job}}</span><div class="shop_phone_icon"><img src="'+host+'addons/yb_mingpian/core/public/menu/images/shop_phone_icon.png"></div></div></div></div></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#form_edit_store_door").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            $("#form_edit_store_door").css("display",'block').siblings().css("display",'none');
            $("#door_textarea").val(bannerVM.all_data[index]['door_name']);
            $("#door_job").val(bannerVM.all_data[index]['door_job']);
            $("#this_door_img").attr(bannerVM.all_data[index]['imgurl']);
            $("#door_phone").val(bannerVM.all_data[index]['door_phone']);
            bannerVM.now_index = index;
        },
    },
})
//公告
/*
 Vue.component('form_edit_bulletin', {
 props: ['index','imgurl','door_name','door_job'],
 template: '<li title="点击进行修改,拖动交换位置" class="ui-draggable"><div v-on:click="del_left(index)" class="deleteButton"></div><div  v-on:click="onc_banner(index,$event)"  class="widget_wrap" style="padding:5px"><div style="display:flex;"><img style="width:50px;height:50px" :src="imgurl"  class="top-img"><div style="flex:1;font-size:12px;"><div class="title-name">{{door_name}}</div><div>工作时间:<span class="time">{{door_job}}</span><div class="shop_phone_icon"><img src="'+host+'addons/yb_mingpian/core/public/menu/images/shop_phone_icon.png"></div></div></div></div></div></li>',
 methods: {
 //删除
 del_left: function (index) {
 bannerVM.all_data.splice(index,1);
 $("#form_edit_store_door").css("display",'none');
 },
 //选中
 onc_banner:function (index,e) {
 $(".activeMod").children().children().removeClass('yb_select');
 $(".activeMod").children().children().eq(index).addClass('yb_select');
 $("#form_edit_store_door").css("display",'block').siblings().css("display",'none');
 $("#door_textarea").val(bannerVM.all_data[index]['door_name']);
 $("#door_job").val(bannerVM.all_data[index]['door_job']);
 $("#this_door_img").attr(bannerVM.all_data[index]['imgurl']);
 bannerVM.now_index = index;
 },
 },
 })
 */
//表单
Vue.component('edit_form', {
    props: ['index','imgurl'],
    template: '<li id="edit_form_this" class="ui-draggable" title="点击进行修改,拖动交换位置."><div v-on:click="del_left(index)" class="deleteButton"></div><div v-on:click="onc_banner(index,$event)" style="width:310px;"><img :src="imgurl"></div></li>',
    methods: {
        //删除
        del_left: function (index) {
            bannerVM.all_data.splice(index,1);
            $("#form_edit_form").css("display",'none');
        },
        //选中
        onc_banner:function (index,e) {
            $(".activeMod").children().children().removeClass('yb_select');
            $(".activeMod").children().children().eq(index).addClass('yb_select');
            $("#form_edit_form").css("display",'block').siblings().css("display",'none');
            bannerVM.now_index = index;
        },
    },
})
var bannerVM = new Vue({
    el: '#b_menu',
    data: {
        all_data:[],//所有数据
        menu_list:[],
        add_h_di: 360,
        add_top_di: 250,
        display: "block",
        nab_name: '模板名称',
        nab_color: '#000000',
        font_color: '#8b8b8b',
        db_color: '#ffffff',
        di_dh_color: '#ffffff',
        dh_color: '#ffffff',
        dbj_color: '#ffffff',
        bag_url: 'public/menu/images/black.png',
        win_color:'#ffffff',
        win_img:'',
        openImg:'',
        openImgUrl:'',
        openImgUrlName:'',
        color:'',
        is_di_dis:true, //是否显示底部导航
        banner:[],//轮播图当前数据
        advert:[],//广告位当前数据
        catenav:[],//宫格导航
        columntitle:[],//标题
        imgtextlist:[],//图文集
        fight_group_list:[],//拼团
        goodlist:[],//商品集
        products:[],//产品集
        joinlist:[],//招聘集
        edit_piclist:[],//图片列表
        edit_button:[],//按钮
        floaticon:[],//悬浮
        customer:[],
        add_h:245,//DIV高度
        add_top:145,//添加按钮高度
        now_index : 0,//当前下标
        this_type:'',
        tripartite_list:[],//三方图
        quartet_list:[],//四方图
    },
    mounted: function() {
        this.$dragging.$on('dragged', function(data) {
            // $("#from_edit_goodlist").css("display",'none').siblings().css("display",'none');
            var list=Object.keys(data['value']['list']);
            var index='';
            for(var i=0;i<list.length;i++){
                if (data['draged']['time_key']==data['value']['list'][i]['time_key']){
                    index=i;
                }
            }
            bannerVM.now_index = index;
        })
        this.$dragging.$on('dragend', function(data) {
        })
    },
    methods: {
        //选中图片
        select_img: function (index,type) {
            art.dialog.open((host+'/addons/yb_mingpian/core/index.php?s=/admin/menu/dialogalbumlist&this_id='+index+"&type="+type), {
                lock : true,
                title : "我的图片",
                width : 900,
                height:620,
                drag : false,
                background : "#000000",
                scrollbar:false
            }, true);
        },
        //选中功能呢
        select_menu:function (index,type) {
            $("#goods_select_id").val(index);
            bannerVM.this_type=type;
            lay_open('选择功能', host+'addons/yb_mingpian/core/index.php?s=/admin/menu/index_select_menu&this_type='+type+'&select_id=' + index, '1000px', '900px');
        },
        //选中功能呢
        select_article:function (index,type) {
            $("#goods_select_id").val(index);
            bannerVM.this_type=type;
            lay_open('选择功能', host+'addons/yb_mingpian/core/index.php?s=/admin/menu/index_select_article&this_type='+type+'&select_id=' + index, '800px', '600px');
        },
        //砍价
        select_fight_group:function (index,type) {
            $("#goods_select_id").val(index);
            bannerVM.this_type=type;
            lay_open('选择功能',host+'/addons/yb_mingpian/core/index.php?s=/admin/menu/select_fight_group&this_type='+type+'&select_id=' + index, '800px', '600px');
        },
        //商品
        select_goods:function (index,type) {
            $("#goods_select_id").val(index);
            bannerVM.this_type=type;
            lay_open('选择商品', host+'addons/yb_mingpian/core/index.php?s=/admin/menu/index_select_goods&this_type='+type+'&select_id=' + index, '800px', '600px');
        },
        //产品
        select_product:function (index,type) {
            $("#products_select_id").val(index);
            bannerVM.this_type=type;
            lay_open('选择产品', host+'addons/yb_mingpian/core/index.php?s=/admin/menu/index_select_product&this_type='+type+'&select_id=' + index, '800px', '600px');
        },
        //招聘
        select_join:function (index) {
            $("#join_select_id").val(index);
            lay_open('选择职位', host+'addons/yb_mingpian/core/index.php?s=/admin/menu/select_join&select_id=' + index, '800px', '600px');
        },
        click_position_wz:function () {
            lay_open('坐标', host+'addons/yb_mingpian/core/index.php?s=/admin/menu/index_select_position','800px', '600px');
        },
        add_menu: function (index, type) {
            art.dialog.open((host+'addons/yb_mingpian/core/index.php?s=/admin/menu/dialogalbumlist&this_id=' + index + "&type=" + type), {
                lock : true,
                title : "我的图片",
                width : 900,
                height:620,
                drag : false,
                background : "#000000",
                scrollbar:false
            }, true);
        },
        //底部导航
        select_di_menu: function (index) {
            lay_open('选择功能', host+'/addons/yb_mingpian/core/index.php?s=/admin/menu/select_menu&select_id=' + index, '800px', '600px');
        },
        //表单选择
        select_form_all:function (index) {
            lay_open('选择表单', host+'/addons/yb_mingpian/core/index.php?s=/admin/menu/select_form_all', '800px', '600px');
        },
        //删除图片
        remove_img:function (index,type) {
            if (type=="goods"){
                if(bannerVM.goodlist.length == 1)
                {
                    layer.msg('不能小于1件商品！',{icon:2,time:1000});
                    return false;
                }
                for (var i=1;i<=10;i++){
                    if(typeof (bannerVM.goodlist[index+i])!='undefined'){
                        bannerVM.goodlist[index+i]['top']=bannerVM.goodlist[index+i]['top']-135;
                    }else {
                        continue;
                    }
                }
                bannerVM.goodlist.splice(index,1);
                bannerVM.add_top -= 135;
                bannerVM.add_h -= 135;
            }
            if (type=="product"){
                if(bannerVM.products.length == 1)
                {
                    layer.msg('不能小于1件产品！',{icon:2,time:1000});
                    return false;
                }
                for (var i=1;i<=10;i++){
                    if(typeof (bannerVM.products[index+i])!='undefined'){
                        bannerVM.products[index+i]['top']=bannerVM.products[index+i]['top']-135;
                    }else {
                        continue;
                    }
                }
                bannerVM.products.splice(index,1);
                bannerVM.add_top -= 135;
                bannerVM.add_h -= 135;
            }
            if (type=="article"){
                if(bannerVM.imgtextlist.length == 1)
                {
                    layer.msg('不能小于1张图片！',{icon:2,time:1000});
                    return false;
                }
                for (var i=1;i<=10;i++){
                    if(typeof (bannerVM.imgtextlist[index+i])!='undefined'){
                        bannerVM.imgtextlist[index+i]['top']=bannerVM.imgtextlist[index+i]['top']-135;
                    }else {
                        continue;
                    }
                }
                bannerVM.imgtextlist.splice(index,1);
                bannerVM.add_top -= 135;
                bannerVM.add_h -= 135;
            }
            if (type=="banner"){
                if(bannerVM.banner.length == 1)
                {
                    layer.msg('不能小于1张图片！',{icon:2,time:1000});
                    return false;
                }
                for (var i=1;i<=10;i++){
                    if(typeof (bannerVM.banner[index+i])!='undefined'){
                        bannerVM.banner[index+i]['top']=bannerVM.banner[index+i]['top']-135;
                    }else {
                        continue;
                    }
                }
                bannerVM.banner.splice(index,1);
                bannerVM.add_top -= 135;
                bannerVM.add_h -= 135;
            }
            if (type=="advert"){
                if(bannerVM.advert.length == 1)
                {
                    layer.msg('不能小于1张图片！',{icon:2,time:1000});
                    return false;
                }
                for (var i=1;i<=10;i++){
                    if(typeof (bannerVM.advert[index+i])!='undefined'){
                        bannerVM.advert[index+i]['top']=bannerVM.advert[index+i]['top']-135;
                    }else {
                        continue;
                    }
                }
                bannerVM.advert.splice(index,1);
                bannerVM.add_top -= 135;
                bannerVM.add_h -= 135;
            }
            if (type=="navigation"){
                if(bannerVM.catenav.length == 1)
                {
                    layer.msg('不能小于1张图片！',{icon:2,time:1000});
                    return false;
                }
                for (var i=1;i<=10;i++){
                    if(typeof (bannerVM.catenav[index+i])!='undefined'){
                        bannerVM.catenav[index+i]['top']=bannerVM.catenav[index+i]['top']-135;
                    }else {
                        continue;
                    }
                }
                bannerVM.catenav.splice(index,1);
                bannerVM.add_top -= 135;
                bannerVM.add_h -= 135;
            }
            if (type=="edit_piclist"){
                if(bannerVM.edit_piclist.length == 1)
                {
                    layer.msg('不能小于1张图片！',{icon:2,time:1000});
                    return false;
                }
                for (var i=1;i<=10;i++){
                    if(typeof (bannerVM.edit_piclist[index+i])!='undefined'){
                        bannerVM.edit_piclist[index+i]['top']=bannerVM.edit_piclist[index+i]['top']-135;
                    }else {
                        continue;
                    }
                }
                bannerVM.edit_piclist.splice(index,1);
                bannerVM.add_top -= 135;
                bannerVM.add_h -= 135;
            }
            if (type=="join_us"){
                if(bannerVM.joinlist.length == 1)
                {
                    layer.msg('不能小于1条！',{icon:2,time:1000});
                    return false;
                }
                for (var i=1;i<=10;i++){
                    if(typeof (bannerVM.joinlist[index+i])!='undefined'){
                        bannerVM.joinlist[index+i]['top']=bannerVM.joinlist[index+i]['top']-135;
                    }else {
                        continue;
                    }
                }
                bannerVM.joinlist.splice(index,1);
                bannerVM.add_top -= 135;
                bannerVM.add_h -= 135;
            }
        },
        clip_menu: function (index) {
            if (bannerVM.menu_list.length == 2) {
                layer.msg('不能小于2个菜单！', {icon: 2, time: 1000});
                return;
            }
            //bannerVM.menu_list[index]['top'] - 135;
            if (typeof (bannerVM.menu_list[index + 1]) != 'undefined') {
                bannerVM.menu_list[index + 1]['top'] = bannerVM.menu_list[index + 1]['top'] - 112;
                if (typeof (bannerVM.menu_list[index + 2]) != 'undefined') {
                    bannerVM.menu_list[index + 2]['top'] = bannerVM.menu_list[index + 2]['top'] - 112;
                    if (typeof (bannerVM.menu_list[index + 3]) != 'undefined') {
                        bannerVM.menu_list[index + 3]['top'] = bannerVM.menu_list[index + 3]['top'] - 112;
                        if (typeof (bannerVM.menu_list[index + 4]) != 'undefined') {
                            bannerVM.menu_list[index + 4]['top'] = bannerVM.menu_list[index + 4]['top'] - 112;
                        }
                    }
                }
            }
            bannerVM.menu_list.splice(index, 1);
            bannerVM.add_top_di -= 112;
            bannerVM.add_h_di -= 112;
            if (bannerVM.menu_list.length == 4) {
                bannerVM.add_h_di += 80;
            }
            bannerVM.display = "block";
        },
    }
});