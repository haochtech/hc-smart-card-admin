var snap = location.href;
var cuff = snap.split('addons');
var host = cuff[0];
//弹出图片管理界面
var OpenPricureDialog = function(type, ADMIN_MAIN, number ,upload_type, spec_id, spec_value_id) {
	// wall_p=wall_p||"goods_add"; +wall_p
    var dds=host+'addons/yb_mingpian/core/index.php?s=/admin' + '/images/dialogalbumlist&number='+ number +'&spec_id='+spec_id+'&spec_value_id='+spec_value_id+'&upload_type='+upload_type+"&type=goods_add";
	//uoload_type 上传来源 1 为商品主图  2 位商品sku图片 3 位规格spec
	if (number  == null || number  == '' ) {
		number  = 0;
	}
	if (type == "PopPicure") {
		// layer_open("我的图片",dds,900,620,'',100);
		art.dialog.open((dds), {
			lock : true,
			title : "我的图片",
			width : 900,
			height:620,
			drag : false,
			background : "#000000",
            scrollbar:false,
		}, true);
	}
}
//弹出sku管理界面
var OpenSkuDialog = function(url,attr_id,cate_id) {
	art.dialog.open((host+'addons/yb_mingpian/core/index.php?s=/'+url + '/goods/controlDialogSku&attr_id='+attr_id), {
		lock : true,
		title : "商品规格",
		width : 860,
		height : 350,
		drag : false,
		background : "#000000",
        scrollbar:false
	}, true);
	
}