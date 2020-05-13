function is_weixn(){
    var ua = navigator.userAgent.toLowerCase();
    if(ua.match(/MicroMessenger/i)=="micromessenger") {
        return true;
    } else {
        return false;
    }
}
//var is_weixin=is_weixn();
//if(!is_weixin){
//    var domain = window.location.host;
//    window.location.href="https://"+domain+"/addons/yb_mingpian/core/web.php";
//
//}
//document.addEventListener('touchmove', function(e) {
//    e.preventDefault();
//}, false);
/**
 * 获取地址参数
 * @returns {Object}
 * @constructor
 */
function GetRequest() {
    var url = location.search; //获取url中"?"符后的字串
    var theRequest = new Object();
    if (url.indexOf("?") != -1) {
        var str = url.substr(1);
        strs = str.split("&");
        for (var i = 0; i < strs.length; i++) {
            theRequest[strs[i].split("=")[0]] = unescape(strs[i].split("=")[1]);
        }
    }
    return theRequest;
}

/**
 *上传图片
 * num=1时(单图上传)：存入data两个字符串 pic_path:图片的base64数据,可以用img标签显示;serverId:返回图片的服务器端ID
 * num》1时(多图上传)：存入data一个数组pic_arr=[['path'=>'','serverId'=>'']]
 * @param that  实例化vue
 * @param num  1时为单图上传 >1多图上传
 * @constructor
 */
function ChooseImage(that, num=1) { //num 1单图；>1多图
    wx.chooseImage({
        count: num,
        sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
        sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
        defaultCameraMode: "normal", //表示进入拍照界面的默认模式，目前有normal与batch两种选择，normal表示普通单拍模式，batch表示连拍模式，不传该参数则为normal模式。（注：用户进入拍照界面仍然可自由切换两种模式）
        success: function (res) {
          console.log(res)
            var localIds = res.localIds; // 返回选定照片的本地ID列表，
            var phone_system = CheckPhone();
            console.log(phone_system)
            if (num == 1) {
                if (phone_system == 1) {
                    that.pic_path = localIds[0];
                } else {
                    wx.getLocalImgData({
                        localId: localIds[0], // 图片的localID
                        success: function (res) {
                          console.log(res)
                            that.pic_path = res.localData; // localData是图片的base64数据，可以用img标签显示
                        }
                    });
                }
                //图片上传
                wx.uploadImage({
                    localId: localIds[0], // 需要上传的图片的本地ID，由chooseImage接口获得
                    isShowProgressTips: 1, // 默认为1，显示进度提示
                    success: function (res) {
                        console.log(res)
                        that.serverId = res.serverId; // 返回图片的服务器端ID
                    }
                });
            } else {
                var pic_arr = that.pic_arr ? that.pic_arr : [];
                var pic_arr2 = [];
                localIds.forEach(function (t, k) {
                    if (phone_system == 1) {
                        pic_arr[k]['path'] = t;
                    } else {
                        wx.getLocalImgData({
                            localId: t, // 图片的localID
                            success: function (res) {
                                pic_arr2[k]['path'] = res.localData; // localData是图片的base64数据，可以用img标签显示
                            }
                        });
                    }
                    //图片上传
                    wx.uploadImage({
                        localId: t, // 需要上传的图片的本地ID，由chooseImage接口获得
                        isShowProgressTips: 1, // 默认为1，显示进度提示
                        success: function (res) {
                            pic_arr2[k]['serverId'] = res.serverId; // 返回图片的服务器端ID
                        }
                    });
                })
                that.pic_arr = pic_arr.concat(pic_arr2);
            }
        },
        fail:function(t){
        msg(t.errMsg);
    }
    });
}
/**
 * 判断手机型号
 * @returns {number}
 * @constructor
 */
function CheckPhone() {
    var u = navigator.userAgent;
    if (u.indexOf('Android') > -1 || u.indexOf('Linux') > -1) {//安卓手机
        return 1;
    } else if (u.indexOf('iPhone') > -1) {//苹果手机
        return 2;
    } else if (u.indexOf('Windows Phone') > -1) {//winphone手机
        return 3;
    }
}
/**
 * 字符串转json对象
 * @param str
 * @returns {any}
 */
function json_parse(str) {
    var jsonStr = str;
    jsonStr = jsonStr.replace(" ", "");
    if (typeof jsonStr != 'object') {
        jsonStr = jsonStr.replace(/\ufeff/g, "");//重点
        var obj = JSON.parse(jsonStr);
        return obj
    }
};
//提示
function msg(e) {
    layer.open({
        content: e, skin: 'msg', time: 2 //2秒后自动关闭
    });
}
//loading带文字
function loading(e) {
    layer.open({
        type: 2
        , content: e
    });
}
//汉字转化为unicode方法
function toUnicodeFun(data) {
    if (data == '' || typeof data == 'undefined') return '请输入汉字';
    var str = '';
    for (var i = 0; i < data.length; i++) {
        str += "\\u" + data.charCodeAt(i).toString(16);
    }
    return str;
}
//unicode转化为汉字的方法
function toChineseWords(data) {
    if (data == '' || typeof data == 'undefined') return '请输入十六进制unicode';
    data = data.split("\\u");
    var str = '';
    for (var i = 0; i < data.length; i++) {
        str += String.fromCharCode(parseInt(data[i], 16).toString(10));
    }
    return str;
}
