//模块输入信息验证
function verify(group_name){
	if(group_name == ''){
		$("#group_name").parent().next().show();
		$("#group_name").focus();
		return false;
	}else{
		$(".error").hide();
	}
	return true;
}
var flag = false;//防止重复提交
//图片上传
function imgUpload(){
	OpenPricureDialog("PopPicure", ADMINMAIN,1);
}
function PopUpCallBack(img_id,img_src){
	$("#imgLogo").attr("src",__IMG(img_src));
	$("#group_pic").val(img_id);
}