var snap = location.href;
var cuff = snap.split("addons");
var host = cuff[0];
/**
 * 上传文件
 */
function uploadFile(fileid,data,callBack, source){
	var dom = document.getElementById(fileid);
	var file =  dom.files[0];//File对象;
	if(validationFile(file, source)){
		$.ajaxFileUpload({
			url : host+"addons/yb_mingpian/core/index.php?s=/admin/upload_attr/uploadfile", //用于文件上传的服务器端请求地址
			secureuri : false, //一般设置为false
			fileElementId : fileid, //文件上传空间的id属性  <input type="file" id="file" name="file" />
			dataType : 'text', //返回值类型 一般设置为json
			data : data,
			async : false,
			contentType : "text/json;charset=utf-8",
			success : function(res){ //服务器成功响应处理函数
                var arr=res.split('<');
                //console.log();
                callBack.call(this,JSON.parse(arr[0]));
			}
		});
	}
}
/**
 * 验证文件是否可以上传
 * @param file JS DOM文件对象
 * @source admin pc sourcel
 */
function validationFile(file, source) {
	var fileTypeArr = ['application/php','text/html','application/javascript','application/msword','application/x-msdownload','text/plain'];
	if(null == file) return false;
	
	if(!file.type){
		if(source == 1) layer.msg("文件类型不合法");
		
		else if(source == "pc" ) $.msg("文件类型不合法");
			
		else showTip("文件类型不合法","warning");
		
		return false;
	}
	
	var flag = false;
	for(var i=0;i<fileTypeArr.length;i++){
		if(file.type == fileTypeArr[i]){
			flag = true;
			break;
		}
	}
	
	if(flag){
		if(source == 1) layer.msg("文件类型不合法");
		else if(source == "pc" ) $.msg("文件类型不合法");
		
		else showTip("文件类型不合法","warning");
		
		return false;
	}
	return true;
}
/**
 * 删除文件，支付批量删除，逗号隔开
 * @param filename
 */
function removeFile(filename){
	$.ajax({
		url : "/admin/upload/removefile",
		type : "post",
		data : { "filename" : filename },
		success : function(res){
			showTip("本次操作共删除"+res.success_count+"个文件,"+res.error_count+"个文件失败","success");
		}
	})
}
/**
 * 商品规格图片上传
 * @param fileid
 * @param callBack
 */
function specImgUpload(fileid, data, callBack){
	var dom = document.getElementById(fileid);
	var file =  dom.files[0];//File对象;
	if(validationFile(file)){
		$.ajaxFileUpload({
			url : '/admin/upload/icon_images', //用于文件上传的服务器端请求地址
			secureuri : false, //一般设置为false
			fileElementId : fileid, //文件上传空间的id属性  <input type="file" id="file" name="file" />
			dataType : 'text', //返回值类型 一般设置为json
			data : data,
			async : false,
			contentType : "text/json;charset=utf-8",
			success : function(res){ //服务器成功响应处理函数
                var arr=res.split('<');
                //console.log();
                callBack.call(this,JSON.parse(arr[0]));
			}
		});
	}
}