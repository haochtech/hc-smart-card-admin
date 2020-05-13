var snap = location.href;
var cuff = snap.split('addons');
var host = cuff[0];
$(function () {
    if (parseInt($("#goodsId").val()) > 0) {
        //初始化规格图片记录数组
        if ($.trim(sku_picture_array_str) != "" && $.trim(sku_picture_array_str) != undefined) {
            $sku_goods_picture = eval(sku_picture_array_str);
            $goods_attribute = eval(goods_spec_array_str);
            $goods_attribute_sku = eval(sku_lists);
        }
        if (parseInt($("#goodsType").val()) == 0) {
            //编辑商品时，有些商品没有选择商品类型
            $("#goodsType").attr("data-flag", 1);//标识 0：表示添加商品，1：表示编辑商品，商品分类为0,2：表示编辑商品，商品分类不为0
            getGoodsSpecListNotAttrId();
        } else {
            $("#goodsType").attr("data-flag", 2);//标识 0：表示添加商品，1：表示编辑商品，商品分类为0,2：表示编辑商品，商品分类不为0
            getGoodsSpecListByAttrId($("#goodsType").val(), function () {
                editSkuData(goods_spec_format, sku_list);
                //加载属性
                $(".js-goods-sku-attribute tr").each(function () {
                    var value = $(this).children("td:first").attr("data-value");//商品属性名称
                    var value_name = $(this).children("td:last");//具体的属性值
                    if (value != undefined && value != "") {
                        for (var i = 0; i < goods_attribute_list.length; i++) {
                            var curr = goods_attribute_list[i];
                            if (curr['attr_value'] == value) {
                                switch (value_name.find("input").attr("type")) {
                                    case "text":
                                        value_name.find("input").val(curr['attr_value_name']);
                                        break;
                                    case "radio":
                                        value_name.find("input").each(function () {
                                            if ($.trim($(this).val()) == $.trim(curr['attr_value_name'])) {
                                                $(this).attr("checked", "checked");
                                                return false;
                                            }
                                        })
                                        break;
                                    case "checkbox":
                                        value_name.find("input").each(function () {
                                            if ($.trim($(this).val()) == $.trim(curr['attr_value_name'])) {
                                                $(this).attr("checked", "checked");
                                            }
                                        })
                                        break;
                                }
                                if (value_name.find("input").attr("type") != "checkbox") {
                                    break;
                                }
                            }
                        }
                    }
                });
            });
        }
    } else {
        //getGoodsSpecListNotAttrId();
        $("#goodsType").attr("data-flag", 0);//标识 0：表示添加商品，1：表示编辑商品，商品分类为0,2：表示编辑商品，商品分类不为0
        getGoodsSpecListByAttrId($("#goodsType").val());
    }
    /**
     * 根据选择的商品类型，查询规格属性
     */
    $("#goodsType").change(function () {
        goodsTypeChangeData();
        getGoodsSpecListByAttrId($(this).val());
        removeSpecPictureBox();
        if (parseInt($(this).val()) == 0) {
//			//如果没有选择商品类型，则清空属性信息
            $(".js-goods-attribute-block").hide();
            $(".js-goods-sku-attribute").html("");
        }
    });
    /**
     * 添加商品规格属性
     */
    $(".js-goods-spec-add").live("click", function () {
        OpenSkuDialog('admin', parseInt($("#goodsType").val()));
        //回调函数：addGoodsSpecCallBack
    });
    /**
     * 规格值添加，生成规格值输入框，进行添加操作
     */
    $(".js-goods-spec-value-add").live("click", function () {

        if ($(this).attr("data-flag") == undefined) {
            $(".js-goods-spec-value-add").html("添加规格值").removeAttr("data-flag style");
            var spec_id = $(this).attr("data-spec-id");
            var show_type = $(this).attr("data-show-type");//显示方式
            var html = '<input type="text" placeholder="请输入规格值" style="margin-bottom:0px;">';
            var length = $(this).parent().children("article").length;//当前规格的规格值数量，用于设置图片上传的id，不冲突
            html += '<span class="goods-sku-add" style="margin:0 10px;">确定</span>';
            html += '<span class="goods-sku-cancle">取消</span>';
            $(this).css("background", "#DBDBDB");
            $(this).attr("data-flag", 1);
            $(this).html(html);
            $(this).children("input[type='text']").focus();
        }
    });
    /**
     * 规格值添加
     */
    $(".js-goods-spec-value-add>input").live("keyup", function (event) {
        var curr_obj = $(this).parent();
        var spec_value_name = curr_obj.children("input[type='text']").val();
        if (event.keyCode == 13) {
            if (spec_value_name.length != 0) {
                var show_type = curr_obj.attr("data-show-type");
                var spec_value_data = "";//附加值
                var spec_value = {
                    spec_id: curr_obj.attr("data-spec-id"), //规格id
                    spec_name: curr_obj.attr("data-spec-name"),//规格名称
                    show_type: show_type,//展示方式
                    spec_value_name: spec_value_name, //规格值
                    spec_value_data: spec_value_data  //附加值
                };
                addGoodsSpecValue(spec_value, function () {
                    curr_obj.parent().append(getCurrentSpecValueHTML(spec_value));//加载当前添加的规格值、以及最后那个添加按钮
                    curr_obj.remove();//删除当前的添加按钮
                });
            } else {
                layer.msg('请输入规格值', {icon: 2, time: 1000});
            }
        }
        return false;//防止事件冒泡
    });
    /**
     * 添加规格值：确定操作
     */
    $(".js-goods-spec-value-add>span:first").live("click", function () {

        var curr_obj = $(this).parent();
        console.log(curr_obj);

        var spec_value_name = curr_obj.children("input[type='text']").val();

        console.log(spec_value_name);

        if (spec_value_name.length != 0) {
            var show_type = curr_obj.attr("data-show-type");
            var spec_value_data = "";//附加值
            var spec_value = {
                spec_id: curr_obj.attr("data-spec-id"), //规格id
                spec_name: curr_obj.attr("data-spec-name"),//规格名称
                show_type: show_type,//展示方式
                spec_value_name: spec_value_name, //规格值
                spec_value_data: spec_value_data  //附加值
            };
            addGoodsSpecValue(spec_value, function () {
                curr_obj.parent().append(getCurrentSpecValueHTML(spec_value));//加载当前添加的规格值、以及最后那个添加按钮
                curr_obj.remove();//删除当前的添加按钮
            });
        } else {
            layer.msg('请输入规格值', {icon: 2, time: 1000});
        }
        return false;//防止事件冒泡
    })
    /**
     * 添加规格值：取消操作
     */
    $(".js-goods-spec-value-add>span:last").live("click", function () {
        $(this).parent().removeAttr("style data-flag").html("添加规格值");
        return false;//防止事件冒泡
    })
    /**
     * 修改商品规格信息
     *
     */
    $(".goods-sku-item span").live("dblclick", function () {
        var text = $(this).text();
        if (text != "") {
            $(this).empty();//清空当前规格值的文本内容
            var html = '<input type="text" value="' + text + '" data-flag="update_sku_text" data-old-html="' + text + '" />';
            $(html).appendTo($(this));//添加输入框
            $(this).css("padding", "7px 10px");//调整样式
            $(this).children("input[type='text']").focus();
        }
        if (timeoutID != null) {
            clearTimeout(timeoutID);
        }
    });
    /**
     * 更新规格值
     */
    $("input[data-flag='update_sku_text']").live("keyup", function (event) {
        var curr_obj = $(this);
        var spec_value_name = $.trim(curr_obj.val());
        if (event.keyCode == 13) {
            if (spec_value_name.length != 0) {
                var spec_value_id = curr_obj.parent().attr("data-spec-value-id");
                //输入框的内容与之间的规格值不一等，进行修改，否则关闭输入框
                if (spec_value_name != curr_obj.attr("data-old-html")) {
                    var spec = {
                        flag: curr_obj.parent().hasClass("selected"),
                        spec_id: curr_obj.parent().attr("data-spec-id"),
                        spec_name: curr_obj.parent().attr("data-spec-name"),
                        spec_value_id: spec_value_id,
                        spec_value_name: spec_value_name,
                        spec_show_type: curr_obj.parent().attr("data-spec-show-type")
                    };
                    curr_obj.parent().html(spec_value_name).css("padding", "7px 20px");//给规格值文本赋值
                    editSpecValueName(spec);
                } else {
                    curr_obj.parent().html(spec_value_name).css("padding", "7px 20px");//给规格值文本赋值
                }
            } else {
                layer.msg('请输入规格值', {icon: 2, time: 1000});
            }
        }
        return false;//防止重复提交
    }).live("click", function () {
        return false;//防止重复提交
    }).live("blur", function () {
        var curr_obj = $(this);
        var spec_value_name = $.trim(curr_obj.val());
        var spec_value_id = curr_obj.parent().attr("data-spec-value-id");
        if (spec_value_name.length == 0) {
            layer.msg('请输入规格值', {icon: 2, time: 1000});
            return false;
        }
        if (spec_value_name != curr_obj.attr("data-old-html")) {
            var spec = {
                flag: curr_obj.parent().hasClass("selected"),
                spec_id: curr_obj.parent().attr("data-spec-id"),
                spec_name: curr_obj.parent().attr("data-spec-name"),
                spec_value_id: spec_value_id,
                spec_value_name: spec_value_name,
                spec_show_type: curr_obj.parent().attr("data-spec-show-type")
            };
            curr_obj.parent().html(spec_value_name).css("padding", "7px 20px");//给规格值文本赋值
            editSpecValueName(spec);
        } else {
            curr_obj.parent().html(spec_value_name).css("padding", "7px 20px");//给规格值文本赋值
        }
    });
    //***********************************选择运费方式***********************************
    $("input[name='fare']").change(function () {
        if ($("input[name='fare']:checked").val() == 1) {
            //$("#deliveryDiv").show();
            $("#commodity-weight").show();
            $("#commodity-volume").show();
            $("#valuation-method").show();
            $("#express_Company").show();
        } else {
            //$("#deliveryDiv").show();
            $("#commodity-weight").hide();
            $("#commodity-volume").hide();
            $("#valuation-method").hide();
            $("#express_Company").hide();
        }
    });
    //***********************************选择运费方式***********************************
    //***********************************选择积分兑换***********************************
    $("input[name='integralSelect']").change(function () {
        if ($("input[name='integralSelect']:checked").val() == 1) {
            $("#integral-exchange").show();
        } else {
            $("#integral-exchange").hide();
        }
    });
    //***********************************选择积分兑换***********************************
    /**
     * 循环处理价格 不让价格为空
     */
    $('input[name="sku_price"],input[name="market_price"],input[name="cost_price"],input[name="stock_num"],input[name="code"]').live('keyup', function () {
        var $this = $(this);
        var reg = /^\d+(.{0,1})\d{0,2}$/;
        if ($this.attr("name") == "sku_price" || $this.attr("name") == "market_price" || $this.attr("name") == "cost_price" || $this.attr("name") == "stock_num") {
            if ($this.val().length > 0) {
                if (reg.test($this.val())) {
                    if ($this.val().replace(/(^\s*)|(\s*$)/g, "") == "" || $this.val().replace(/(^\s*)|(\s*$)/g, "") == "0.00") {
                        if ($this.attr("name") == "stock_num") {
                            $this.val("0");
                        } else {
                            $this.val("0.00");
                        }
                        $this.css("border-color", "#b94a48");
                        $this.parent().find(".help-inline").show();
                    } else {
                        num = parseInt($this.val());
                        $this.css("border-color", "");
                        $this.parent().find(".help-inline").hide();
                    }
                    switch ($this.attr("name")) {
                        case "sku_price":
                            eachPrice();
                            break;
                        case "market_price":
                            eachMarketPrice();
                            break;
                        case "cost_price":
                            eachCostPrice();
                            break;
                        case "stock_num":
                            eachInput();
                            break;
                    }
                } else {
                    if ($this.attr("name") == "stock_num") {
                        $this.val("0");
                    } else {
                        $this.val("0.00");
                    }
                }
            } else {
                if ($this.attr("name") == "stock_num") {
                    $this.val("0");
                } else {
                    $this.val("0.00");
                }
            }
        }
    });
    /**
     * 离开焦点事件也要进行处理
     */
    $('input[name="sku_price"],input[name="market_price"],input[name="cost_price"],input[name="stock_num"],input[name="code"]').live("blur", function () {
        $(this).keyup();
    });
    /**
     * 循环 处理库存
     */
    $('input[name="stock_num"]').live('keyup', function () {
        $stock = $(this);
        if ($stock.val().replace(/(^\s*)|(\s*$)/g, "") == "") {
            $stock.css("border-color", "#b94a48");
            $stock.parent().find(".help-inline").show();
        } else {
            $stock.css("border-color", "");
            $stock.parent().find(".help-inline").hide();
        }
        eachInput();
    });
    $(".brick.small").live('mouseover', function () {
        $(this).children().next().show();
    }).live("mouseout", function () {
        $(this).children().next().hide();
    });
    // 批量设置
    var js_batch_type = '';
    var shop_type = $("#shop_type").val();
    $('.js-batch-price').live('click', function () {
        if (shop_type == 2 || (shop_type == 1 && goodsid == 0)) {
            js_batch_type = 'price';
            $('.js-batch-form').show();
            $('.js-batch-type').hide();
            $('.js-batch-txt').attr('placeholder', '请输入价格');
            $('.js-batch-txt').focus();
        }
    });
    $(".js-batch-market_price").live("click", function () {
        if (shop_type == 2 || (shop_type == 1 && goodsid == 0)) {
            js_batch_type = 'market_price';
            $('.js-batch-form').show();
            $('.js-batch-type').hide();
            $('.js-batch-txt').attr('placeholder', '请输入市场价');
            $('.js-batch-txt').focus();
        }
    });
    $(".js-batch-cost_price").live("click", function () {
        if (shop_type == 2 || (shop_type == 1 && goodsid == 0)) {
            js_batch_type = 'cost_price';
            $('.js-batch-form').show();
            $('.js-batch-type').hide();
            $('.js-batch-txt').attr('placeholder', '请输入成本价');
            $('.js-batch-txt').focus();
        }
    });
    $('.js-batch-stock').live('click', function () {
        if (shop_type == 2 || (shop_type == 1 && goodsid == 0)) {
            js_batch_type = 'stock';
            $('.js-batch-form').show();
            $('.js-batch-type').hide();
            $('.js-batch-txt').attr('placeholder', '请输入库存');
            $('.js-batch-txt').focus();
        }
    });
    /**
     * 批量设置
     */
    $('.js-batch-save').live('click', function () {
        var batch_txt = $('.js-batch-txt');
        if (batch_txt.val() != null && batch_txt.val() != '') {
            var float_val = parseFloat(batch_txt.val());
            if (js_batch_type == 'price') {
                if (float_val > 9999999.99) {
                    showTip('价格最大为 9999999.99', 'warning');
                    batch_txt.focus();
                    return false;
                } else if (!/^\d+(\.\d+)?$/.test(batch_txt.val())) {
                    showTip('请输入合法的价格', "warning");
                    batch_txt.focus();
                    return false;
                } else {
                    batch_txt.val(float_val.toFixed(2));
                }
                $('.js-goods-stock .js-price').val(batch_txt.val());
                batch_txt.val('');
                // 商品价格
                $("input[name='price']").val(float_val.toFixed(2));
                $.each($temp_Obj, function (c, v) {
                    v["sku_price"] = float_val.toFixed(2);
                });
                $("input[name='price']").attr('readonly', true);
                eachPrice();
            } else if (js_batch_type == 'market_price') {// 市场价
                if (float_val > 9999999.99) {
                    showTip('价格最大为 9999999.99', 'warning');
                    batch_txt.focus();
                    return false;
                } else if (!/^\d+(\.\d+)?$/.test(batch_txt.val())) {
                    showTip('请输入合法的价格', 'warning');
                    batch_txt.focus();
                    return false;
                } else {
                    batch_txt.val(float_val.toFixed(2));
                }
                $('.js-goods-stock .js-market-price').val(batch_txt.val());
                $.each($temp_Obj, function (c, v) {
                    v["market_price"] = float_val.toFixed(2);
                });
                batch_txt.val('');
                eachMarketPrice();
            } else if (js_batch_type == 'cost_price') {// 成本价
                if (float_val > 9999999.99) {
                    showTip('价格最大为 9999999.99', 'warning');
                    batch_txt.focus();
                    return false;
                } else if (!/^\d+(\.\d+)?$/.test(batch_txt.val())) {
                    showTip('请输入合法的价格', 'warning');
                    batch_txt.focus();
                    return false;
                } else {
                    batch_txt.val(float_val.toFixed(2));
                }
                $('.js-goods-stock .js-cost-price').val(batch_txt.val());
                batch_txt.val('');
                // 商品价格
                $("input[name='price']").val(float_val.toFixed(2));
                $("input[name='price']").attr('readonly', true);
                $.each($temp_Obj, function (c, v) {
                    v["cost_price"] = float_val.toFixed(2);
                });
                eachCostPrice();
            } else {
                if (!/^\d+$/.test(batch_txt.val())) {
                    showTip('请输入合法的数字', "warning");
                    batch_txt.focus();
                    return false;
                }
                $('.js-goods-stock .js-stock-num').val(batch_txt.val());
                eachInput();
                $.each($temp_Obj, function (c, v) {
                    v["stock_num"] = float_val.toFixed(2);
                });
                $('input[name="total_stock"]').val(parseInt(batch_txt.val()) * $('.js-stock-num').size());
                batch_txt.val('');
            }
            $('.js-batch-form').hide();
            $('.js-batch-type').show();
        } else {
            showTip(batch_txt.attr("placeholder"), 'warning');
            batch_txt.focus();
        }
    });
    $('.js-batch-cancel').live('click', function () {
        $('.js-batch-form').hide();
        $('.js-batch-type').show();
    });
    /**
     * 商品图片：从图片空间选择
     */
    $('#img_box').off('click').on('click', function (e) {
        var js_img = $(this).attr("js-img");
        shopImageFlag = js_img;//所点击的商品图片标识
        speciFicationsFlag = 0;
        OpenPricureDialog("PopPicure", 'admin', 0, 1);
    });
    /**
     * 规格图片，从图片空间选择
     */
    $('#sku_img_box').live('click', function (e) {
        var js_img = $(this).attr("js-img");
        var spec_id = $(this).attr("spec_id");
        var spec_value_id = $(this).attr("spec_value_id");
        shopImageFlag = js_img;//所点击的商品图片标识
        speciFicationsFlag = 0;
        OpenPricureDialog("PopPicure", 'admin', 1, 2, spec_id, spec_value_id);
    });
    /**
     * 商品类型，SKU缩略图
     */
    $('.sku-img-check').live('click', function (e) {
        var js_img = $(this).attr("js-img");
        var spec_id = $(this).prev().prev().attr("data-spec-id");
        var spec_value_id = $(this).prev().prev().attr("data-spec-value-id");
        shopImageFlag = js_img;//所点击的商品图片标识
        speciFicationsFlag = 0;
        OpenPricureDialog("PopPicure", 'admin', 1, 3, spec_id, spec_value_id);
    });
    /**
     * 实物类别选择
     */
    $("input[name='goods_type']").live("click", function () {
        if ($(this).val() == 0) {
            $(".js-virtual-goods-type-block").show();
        } else {
            $(".js-virtual-goods-type-block").hide();
        }
    });
});
/**
 * 添加规格属性，回调函数
 */
function addGoodsSpecCallBack(spec_id, spec_name, show_type) {
    layer.msg('操作成功', {icon: 1, time: 1000});
    var spec = {
        spec_id: spec_id,
        spec_name: spec_name,
        show_type: show_type
    }
    var html = '<tr class="goods-sku-block-' + spec_id + '">';
    html += '<td width="10%">' + spec_name + '</td>';
    html += '<td width="85%">';
    html += getAddSpecValueHtml(spec);
    html += '</td>';
    html += '</tr>';
    html += '<tr><td>' + getAddSpecHtml() + '</td></tr>';
    $(".js-goods-sku tbody tr:last").remove();
    $(".js-goods-sku tbody").append(html);
}
/**
 * 返回当前添加完成后，生成的规格值HTML代码
 */
function getCurrentSpecValueHTML(spec_value) {
    var html = '<article class="goods-sku-item">';
    html += '<span data-spec-name="' + spec_value.spec_name + '"';
    html += 'data-spec-id="' + spec_value.spec_id + '" ';
    if (parseInt(spec_value.show_type) == 2 && spec_value.spec_value_data == "") {
        spec_value.spec_value_data = "#000000";
    }
    html += ' data-spec-value-data="' + spec_value.spec_value_data + '"';
    html += ' data-spec-show-type="' + spec_value.show_type + '"';
    html += 'data-spec-value-id="-1">';
    html += spec_value.spec_value_name + '</span>';
    html += '</article>';
    html += getAddSpecValueHtml(spec_value);
    return html;
}
/**
 * 返回添加规格值THML代码
 */
function getAddSpecValueHtml(spec) {
    var html = '<a href="javascript:;" data-spec-name="' + spec.spec_name + '" data-spec-id="' + spec.spec_id + '" data-show-type="' + spec.show_type + '" class="js-goods-spec-value-add goods-sku-add-text">添加规格值</a>';
    return html;
}
/**
 * 返回添加规格HTML代码
 */
function getAddSpecHtml() {
    var html = '<a href="javascript:;" class="js-goods-spec-add goods-sku-add-text" style="padding:0;">添加规格</a>';
    return html;
}
/**
 * 添加商品规格值
 * @param spec 规格对象
 * @param callBack 回调函数
 */
function addGoodsSpecValue(spec, callBack) {
    $.ajax({
        url: host + "addons/yb_mingpian/core/index.php?s=/admin/goods/addGoodsSpecValue",
        type: "post",
        data: {
            "spec_id": spec.spec_id,
            "spec_value_name": spec.spec_value_name,
            "spec_value_data": spec.spec_value_data
        },
        success: function (res) {
            if (res.code > 0) {
                layer.msg(res.message, {icon: 1, time: 1000});
                callBack();//执行回调函数
                $("span[data-spec-value-id='-1']").attr("data-spec-value-id", res.code);
            } else {
                layer.msg(res.message, {icon: 5, time: 1000});
            }
        }
    });
}
/**
 * 获取规格表头提示
 * @returns {String}
 */
function getGoodsSpecHeaderHtml() {
    var html = '<tr>';
    html += '<td colspan="2">';
    html += '<div style="text-align:left;">';
    html += '<h5 style="margin:0;padding:0;font-weight: normal;color: #FF8400;">操作提示</h5>';
    html += '<p style="color:#FF8400;font-size:12px;">双击规格值进行编辑操作(回车按钮保存)。</p>';
    html += '<p style="color:#FF8400;font-size:12px;">编辑后请单击选择以修改价格、库存等。</p>';
    html += '</div>';
    html += '</td>';
    html += '</tr>';
    return html;
}
function getGoodsSpecListNotAttrId() {
    if (goods_spec_format == "") return;
    goods_spec_format = eval(goods_spec_format);
    var html = getGoodsSpecHeaderHtml();
    var spec_length = goods_spec_format.length;
    var spec_list = goods_spec_format;
    for (var i = 0; i < spec_length; i++) {
        var curr_spec = spec_list[i];
        html += '<tr class="js-spec-item goods-sku-block-' + curr_spec.spec_id + '">';
        html += '<td width="10%">' + curr_spec.spec_name + "</td>";
        html += '<td width="85%">';
        for (var j = 0; j < curr_spec.value.length; j++) {
            var curr_spec_value = curr_spec.value[j];
            var item = {};
            item['id'] = curr_spec_value.spec_value_id;
            item['name'] = curr_spec_value.spec_value_name;
            app.goods_sku.push(item);
            html += '<article class="goods-sku-item">';
            html += '<span data-spec-name="' + curr_spec.spec_name + '"';
            html += ' data-spec-id="' + curr_spec.spec_id + '"';
            if (parseInt(curr_spec.show_type) == 2 && curr_spec_value.spec_value_data == "") {
                curr_spec_value.spec_value_data = "#000000";
            }
            html += ' data-spec-value-data="' + curr_spec_value.spec_value_data + '"';
            html += ' data-spec-show-type="' + curr_spec_value.spec_show_type + '"';
            if (curr_spec_value.spec_show_type == 3) {
                html += ' data-spec-value-data-src="' + curr_spec_value.spec_value_data_src + '"';
            }
            html += ' data-spec-value-id="' + curr_spec_value.spec_value_id + '">';
            html += curr_spec_value.spec_value_name + "</span>";
            html += '</article>';
        }
        var spec = {
            spec_id: curr_spec.spec_id,
            spec_name: curr_spec.spec_name,
            show_type: curr_spec.value[0]["spec_show_type"]
        };
        if ($("#mod_class_id").val() != 5) {
            html += getAddSpecValueHtml(spec);//添加规格值按钮
        }
        html += '</td>';
        html += '</tr>';
    }
    html += '<tr>';
    if (spec_length == 0) {
        html += '<td class="js-spec-add"  style="text-align:left;">' + getAddSpecHtml() + '</td>';//规格添加
    } else {
        if ($("#mod_class_id").val() != 5) {
            html += '<td class="js-spec-add">' + getAddSpecHtml() + '</td>';//规格添加
        }
    }
    html += '<td></td>';
    html += '</tr>';
    $(".js-goods-spec-block").show();
    $(".js-goods-sku").html(html);
    editSkuData(goods_spec_format, sku_list);
}
/**
 * 根据商品类型id，查询商品规格信息
 * @param attr_id 规格属性id
 */
function getGoodsSpecListByAttrId(attr_id, callBack) {
    if (!isNaN(attr_id) && attr_id > 0) {
        $.ajax({
            url: host + "addons/yb_mingpian/core/index.php?s=/admin/goods/getGoodsSpecListByAttrId",
            type: "post",
            data: {"attr_id": parseInt(attr_id)},
            success: function (res) {
                if (res != -1) {
                    var html = getGoodsSpecHeaderHtml();
                    var spec_length = res.spec_list.length;
                    var attribute_length = res.attribute_list.length;
                    //商品规格集合
                    if (spec_length > 0) {
                        for (var i = 0; i < spec_length; i++) {
                            var curr_spec = res.spec_list[i];
                            html += '<tr class="js-spec-item goods-sku-block-' + curr_spec.spec_id + '">';
                            html += '<td width="10%">' + curr_spec.spec_name + "</td>";
                            html += '<td width="85%">';
                            for (var j = 0; j < curr_spec.values.length; j++) {
                                var curr_spec_value = curr_spec.values[j];
                                html += '<article class="goods-sku-item">';
                                html += '<span data-spec-name="' + curr_spec.spec_name + '"';
                                html += ' data-spec-id="' + curr_spec.spec_id + '"';
                                if (parseInt(curr_spec.show_type) == 2 && curr_spec_value.spec_value_data == "") {
                                    curr_spec_value.spec_value_data = "#000000";
                                }
                                html += ' data-spec-value-data="' + curr_spec_value.spec_value_data + '"';
                                html += ' data-spec-show-type="' + curr_spec.show_type + '"';
                                html += ' data-spec-value-id="' + curr_spec_value.spec_value_id + '">';
                                html += curr_spec_value.spec_value_name + "</span>";
                                html += '</article>';
                            }
                            var spec = {
                                spec_id: curr_spec.spec_id,
                                spec_name: curr_spec.spec_name,
                                show_type: curr_spec.show_type
                            };
                            if ($("#mod_class_id").val() != 5) {
                                html += getAddSpecValueHtml(spec);//添加规格值按钮
                            }
                            html += '</td>';
                            html += '</tr>';
                        }
                        html += '<tr>';
                        if ($("#mod_class_id").val() != 5) {
                            html += '<td class="js-spec-add">' + getAddSpecHtml() + '</td>';//规格添加
                        }
                        html += '<td></td>';
                        html += '</tr>';
                        $(".js-goods-sku").html(html);
                    } else {
                        $(".js-goods-sku tr.js-spec-item").remove();
                        $(".js-goods-sku tr .js-spec-add").css("text-align", "left");
                    }
                    //商品属性集合
                    if (attribute_length > 0) {
                        var html = "";
                        console.log(res.attribute_list);
                        for (var i = 0; i < attribute_length; i++) {
                            var curr = res.attribute_list[i];
                            if ($.trim(curr.value_items) == "" && parseInt(curr.type) != 1) continue;
                            if ($.trim(curr.attr_value_name) != "") {
                                html += '<tr style="padding-top:15px;padding-bottom:15px;">';
                                html += '<td width="10%" style="border:1px solid #E9E9E9;"align="right" class="txt12" data-value="' + curr.attr_value_name + '">' + curr.attr_value_name + '</td>';
                                html += '<td width="80%" style="border:1px solid #E9E9E9;">';
                                switch (parseInt(curr.type)) {
                                    case 1:
                                        //输入框
                                        html += '<input type="text" class="js-attribute-text" id="input-text-' + curr.attr_value_id + '-' + curr.attr_value_id + '"data-attribute-value-id="' + curr.attr_value_id + '" data-attribute-value="' + curr.attr_value_name + '" data-attribute-sort="' + curr.sort + '"/>';
                                        break;
                                    case 2:
                                        //单选框
                                        for (var j = 0; j < curr.value_items.length; j++) {
                                            var value = curr.value_items[j];
                                            if ($.trim(value) != "") {
                                                html += '<div class="goods-sku-attribute-item-radio">';
                                                html += '<input type="radio" value="' + value + '" class="js-attribute-radio" id="radio_value_item' + curr.attr_value_id + '-' + j + '" data-attribute-value-id="' + curr.attr_value_id + '" data-attribute-value="' + curr.attr_value_name + '"  name="radio_value' + i + '" data-attribute-sort="' + curr.sort + '"/>&nbsp;';
                                                html += '<label for="radio_value_item' + curr.attr_value_id + '-' + j + '">' + value + '</label>';
                                                html += '</div>';
                                            }
                                        }
                                        break;
                                    case 3:
                                        //复选框
                                        for (var j = 0; j < curr.value_items.length; j++) {
                                            var value = curr.value_items[j];
                                            if ($.trim(value) != "") {
                                                html += '<div class="goods-sku-attribute-item-checkbox">';
                                                html += '<input type="checkbox" value="' + value + '" class="js-attribute-checkbox" id="checkbox_value_item' + curr.attr_value_id + '-' + j + '" data-attribute-value-id="' + curr.attr_value_id + '" data-attribute-value="' + curr.attr_value_name + '"  name="checkbox_value_item' + i + '" data-attribute-sort="' + curr.sort + '"/>&nbsp;';
                                                html += '<label for="checkbox_value_item' + curr.attr_value_id + '-' + j + '">' + value + '</label>';
                                                html += '</div>';
                                            }
                                        }
                                        break;
                                }
                                html += '</td>';
                                html += '</tr>';
                            }
                        }
                        $(".js-goods-sku-attribute").html(html);
                    }
                    if (callBack != undefined) callBack();
                    $(".js-goods-spec-block").show();
                    $(".js-goods-attribute-block").show();
                }
            }
        });
    } else {
        //标识 0：表示添加商品，1：表示编辑商品，商品分类为0,2：表示编辑商品，商品分类不为0
        var mod_class_id = $("#mod_class_id").val();
        if (mod_class_id == 5) {
            return false;
        }
        switch (parseInt($("#goodsType").attr("data-flag"))) {
            case 0:
                var html = getGoodsSpecHeaderHtml();
                html += '<tr>';
                html += '<td class="js-spec-add" style="text-align:left;">' + getAddSpecHtml() + '</td>';//规格添加
                html += '<td></td>';
                html += '</tr>';
                $(".js-goods-sku").html(html);
                $(".js-goods-spec-block").show();
                break;
            case 1:
                //如果当前商品的商品类型为0，则不根据商品类型id加载数据
                getGoodsSpecListNotAttrId();
                break;
            case 2:
                $(".js-goods-sku tr.js-spec-item").remove();
                $(".js-goods-sku tr .js-spec-add").css("text-align", "left").next().remove();
                break;
        }
    }
}
//验证
function ValidateUserInput() {
    var shop_type = $("#shop_type").val();
    var isflag = 0;
    //商品分类
    if ($("#cate_id").val() == '') {
        layer.msg('请选择商品分类', {icon: 5, time: 1000});
        $('html,body').animate({scrollTop: 0}, 200);
        return false;
    }
    // 商品标题
    if (!IsEmpty("#txtProductTitle")) {
        layer.msg('请填写商品标题', {icon: 5, time: 1000});
        $("#txtProductTitle").focus();
        return false;
    } else if ($("#txtProductTitle").val().length > 60) {
        layer.msg('商品标题不能大于60个字', {icon: 5, time: 1000});
        $("#txtProductTitle").focus();
        return false;
    }
    // 副标题
    if ($("#txtIntroduction").val().length > 60) {
        layer.msg('促销语不能大于60个字', {icon: 5, time: 1000});
        $("#txtIntroduction").focus();
        return false;
    }
    if ($("#txtKeyWords").val().length > 0 && $("#txtKeyWords").val().length > 40) {
        $("#txtKeyWords").focus();
        layer.msg('请输入商品促销语，不能超过40个字符', {icon: 5, time: 1000});
        return false;
    }
    if ($("#goodsType").val() > 0) {
        //验证SKU输入是否正确 isflag: 0：成功，1：失败
        //sku价格
        $.each($('input[name="sku_price"]'), function (i, item) {
            var $this = $(item);
            if (parseFloat($this.val()) < 0.01 || $.trim($this.val()) == "") {
                $this.val("0.00");
            } else {
                num = parseInt($this.val());
                $this.css("border-color", "");
                $this.parent().find(".help-inline").hide();
            }
        });
        $.each($('input[name="market_price"]'), function (i, item) {
            var $this = $(item);
            if (parseFloat($this.val()) < 0.01 || $.trim($this.val()) == "") {
                $this.val("0.00");
            } else {
                num = parseInt($this.val());
                $this.css("border-color", "");
                $this.parent().find(".help-inline").hide();
            }
        });
        $.each($('input[name="cost_price"]'), function (i, item) {
            var $this = $(item);
            if (parseFloat($this.val()) < 0.01 || $.trim($this.val()) == "") {
                $this.val("0.00");
            } else {
                num = parseInt($this.val());
                $this.css("border-color", "");
                $this.parent().find(".help-inline").hide();
            }
        });
        // 库存
        $.each($('input[name="stock_num"]'), function (i, item) {
            var $this = $(item);
            if ($.trim($this.val()) == "" || $.trim($this.val()) < 0) {
                $this.css("border-color", "#b94a48");
                layer.msg('总库存不能为0', {icon: 5, time: 1000});
                isflag = 1;
            } else {
                num = parseInt($this.val());
                $this.css("border-color", "");
                $this.parent().find(".help-inline").hide();
            }
        });
        //验证SKU输入是否正确 isflag: 0：成功，1：失败
        if (isflag == 1) {
            $("body").scrollTop($("#txtProductCount").offset().top + 100);
            return false;
        }
    }
    if (isflag == 0) {
        // 商品原价
        if (!IsNum("#txtProductSalePrice") || parseFloat($("#txtProductSalePrice").val()) < 0) {
            layer.msg('商品销售价不能为空，且不能为负数', {icon: 5, time: 1000});
            $("#txtProductSalePrice").focus();
            return false;
        } else {
            var price_s = $("#txtProductSalePrice").val();
            var c_price = parseFloat(price_s);
        }
        // 总库存
        if (!IsPositiveNum("#txtProductCount")) {
            $("#txtProductCount").nextAll("span:last").show();
            $("#txtProductCount").focus();
            return false;
        } else {
            $("#txtProductCount").nextAll("span:last").hide();
        }
        if (parseInt($("#txtProductCount").val()) < 0) {
            $("#txtProductCount").nextAll("span:last").show();
            $("#txtProductCount").focus();
            return false;
        } else {
            $("#txtProductCount").nextAll("span:last").hide();
        }
    }
    //最少购买数
    if ($("#minBuy").val() < 0) {
        $("#minBuy").nextAll("span:last").show();
        $("#minBuy").focus();
        return false;
    } else {
        $("#minBuy").nextAll("span:last").hide();
    }
    if (parseInt($("#txtMinStockLaram").val()) < 0) {
        $("#txtMinStockLaram").nextAll("span:last").show();
        $("#txtMinStockLaram").focus();
        return false;
    } else {
        $("#txtMinStockLaram").nextAll("span:last").hide();
    }
    var imgflag = false;// 默认：false。
    var imgtop = 0;// 如果没有商品图片，就定位到这个位置
    if ($(".upload_img_id").length == 0) {
        imgtop = $(".ncsc-goods-default-pic").offset().top - 200;
        $("body").scrollTop(imgtop);
        layer.msg('最少需要一张图片作为商品主图', {icon: 5, time: 1000});
        return false;
    }
    // 商品描述
    var description = UM.getEditor('editor').getContent();
    description = description.replace(/(\n)/g, "");
    description = description.replace(/(\t)/g, "");
    description = description.replace(/(\r)/g, "");
    description = description.replace(/\s*/g, "");
    if (description == "") {
        layer.msg('商品描述不能为空', {icon: 5, time: 1000});
        $("body").scrollTop($("#discripContainer").offset().top - 100);
        return false;
    } else if (description.length < 5 || description.length > 1000000) {
        layer.msg('商品描述字符数应在5～1000000之间', {icon: 5, time: 1000});
        $("body").scrollTop($("#discripContainer").offset().top - 100);
        return false;
    }
    var reg_integral = /^\+?[1-9][0-9]*$/;
    //如果是积分商品，则必须设置积分
    // 运费设置
    if ($("input[name='fare']:checked").val() == 1) {
        if ($("input[name='shipping_fee_type']:checked").val() == 2) {
            var goods_volume = parseFloat($("#goods_volume").val()).toFixed(2);
            if (goods_volume == '' || goods_volume <= 0) {
                $("#goods_volume").focus();
                $("#goods_volume").next("span").show();
                $("#goods_weight").next("span").hide();
                return false;
            } else {
                $("#goods_volume").next("span").hide();
            }
        } else if ($("input[name='shipping_fee_type']:checked").val() == 1) {
            var goods_weight = parseFloat($("#goods_weight").val()).toFixed(2);
            if (goods_weight == '' || goods_weight <= 0) {
                $("#goods_weight").focus();
                $("#goods_weight").next("span").show();
                $("#goods_volume").next("span").hide();
                return false;
            } else {
                $("#goods_weight").next("span").hide();
            }
        }
    }
    //最小购买数限制
    if (!(parseInt($("#PurchaseSum").val()) >= parseInt($("#minBuy").val())) && (parseInt($("#PurchaseSum").val()) > 0)) {
        imgtop = $("#PurchaseSum").offset().top - 200;
        $("body").scrollTop(imgtop);
        $("#minBuy").next("span").text("限购数不为0时,最小购买数必须小于等于限购数量").show();
        return false;
    } else {
        $("#minBuy").next("span").hide();
    }
    var shopCategoryText = "";
    return true;
}
var flag = false;//防止重复提交
//保存商品
function SubmitProductInfo(type, ADMIN_MAIN, SHOP_MAIN) {
    SSSS="";
    img_id_arr = "";// 商品主图
    var img_obj = $(".upload_img_id");
    for (var $i = 0; $i < img_obj.length; $i++) {
        var $checkObj = $(img_obj[$i]);
        if (img_id_arr == "") {
            img_id_arr = $checkObj.val();
        } else {
            img_id_arr += "," + $checkObj.val();
        }
    }
    ///*↓↓↓↓↓↓淘宝添加物品↓↓↓↓↓↓↓↓↓↓*/
    var is_ai = $("#is_ai").val();
    if(is_ai){
        $.ajax({
            url: host + "addons/yb_mingpian/core/index.php?s=/admin/goods/goods_collect_auto",
            type: "post",
            async:false,
            data: {"img_arr": img_id_arr, "type": 'uppic'},
            success: function (res) {
                img_id_arr=res;
            }
        });
    }
    ///*↑↑↑↑↑↑淘宝添加物品↑↑↑↑↑↑↑↑↑↑*/
    // 禁用按钮
    var validateResult = ValidateUserInput(); // 验证用户输
    if (validateResult) {
        $("#btnSave,#btnSavePreview").attr("disabled", "disabled");
        var productViewObj = PackageProductInfo();
        var $qrcode = $("#hidQRcode").val();
        if (flag) return;
        flag = true;
        console.log(productViewObj);
        var ed=JSON.stringify(productViewObj);
        console.log(ed);
        $.ajax({
            url: host + "addons/yb_mingpian/core/index.php?s=/admin/goods/GoodsCreateOrUpdate",
            type: "post",
            data: {"product":ed , "is_qrcode": $qrcode},
            success: function (res) {
                var url = host + "addons/yb_mingpian/core/index.php?s=/admin/goods/goodslist";
                var goodsId = parseInt($("#goodsId").val());
                var text = "";
                if (res != null) {
                    if (type == 1) {
                        var parameter_goodsid = goodsId;
                        if (parameter_goodsid == 0 || typeof(parameter_goodsid) == 'undefined') {
                            parameter_goodsid = res;
                        }
                        load_page(url);
                    }
                    if (type == 3) {
                        layer.msg('发布成功', {icon: 1, time: 1000}, function () {
                            load_page(host + "addons/yb_mingpian/core/index.php?s=/admin/restaurant/res_food");
                        });
                    }
                    layer.msg('商品发布成功', {icon: 1, time: 1000}, function () {
                        load_page(url);
                    });
                } else {
                    layer.msg('商品发布失败', {icon: 2, time: 1000});
                    flag = false;
                    $("#btnSave,#btnSavePreview").removeAttr("disabled")
                }
            }
        });
    }
}
/**
 * 获取数据已对象方式存储
 */
function PackageProductInfo() {
    // 初始化一个实体 将页面所需的数据存放到对象中
    var shop_type = $("#shop_type").val();
    var productViewObj = new Object();
    productViewObj.goodsId = $("#goodsId").val();// 商品id 11号目前为死值 0
    productViewObj.title = $("#txtProductTitle").val().replace(/^\s*/g, "").replace(/\s*$/g, "");// 商品标题
    productViewObj.goods_type = $("input[name='goods_type']:checked").val();
    productViewObj.introduction = $("#txtIntroduction").val().replace(/^\s*/g, "").replace(/\s*$/g, "");// 商品简介，促销语
    productViewObj.categoryId = $("#cate_id").val();// 商品类目
    // 12号 商品类目；
    productViewObj.market_price = $("#txtProductMarketPrice").val().replace(/^\s*/g, "").replace(/\s*$/g, "") == "" ? 0 : $("#txtProductMarketPrice").val().replace(/^\s*/g, "").replace(/\s*$/g, "");// 市场价
    productViewObj.price = $("#txtProductSalePrice").val().replace(/^\s*/g, "").replace(/\s*$/g, "") == "" ? 0 : $("#txtProductSalePrice").val().replace(/^\s*/g, "").replace(/\s*$/g, "");// 销售价
    productViewObj.cost_price = $("#txtProductCostPrice").val().replace(/^\s*/g, "").replace(/\s*$/g, "") == "" ? 0 : $("#txtProductCostPrice").val().replace(/^\s*/g, "").replace(/\s*$/g, "");// 成本价
    productViewObj.libiary_goodsid = $("#libiary_goodsid").val(); // 商品库id
    productViewObj.base_sales = $("#BasicSales").val() == '' ? 0 : $("#BasicSales").val();// 基础销量
    productViewObj.base_good = $("#BasicPraise").val() == '' ? 0 : $("#BasicPraise").val();// 基础点赞数
    productViewObj.base_share = $("#BasicShare").val() == '' ? 0 : $("#BasicShare").val();// 基础分享数
    productViewObj.is_sale = $("input[name='shelves']:checked").val();// 上下架标记
    productViewObj.display_stock = $('.controls input[name="stock"]:checked ').val();// 是否显示库存
    productViewObj.stock = $("#txtProductCount").val();// 总库存
    productViewObj.minstock = $("#txtMinStockLaram").val();// 库存预警数
    productViewObj.max_buy = $("#PurchaseSum").val();// 每人限购
    productViewObj.min_buy = $("#minBuy").val();// 最少购买数
    productViewObj.key_words = $("#txtKeyWords").val();//商品关键词
    productViewObj.description = UM.getEditor('editor').getContent();// 商品详情描述
    productViewObj.shipping_fee = $("input[name='fare']:checked").val();// 运费方式
    productViewObj.shipping_fee_id = $("#expressCompany").val();
    productViewObj.barcode = $("#code_name").val();//条形码
    productViewObj.this_lib = $("#goods_lib").val();//是否是选中商品库
    productViewObj.user_share = $("#user_share").val();
    productViewObj.share_type = $("#share_type").attr("data-type");
    /*productViewObj.shipping_fee_id = $("input[name='fare']:checked").val() != 1 ? 0 : $("#deliverySelect").val();*/// 运费模板编号
    //alert(JSON.stringify(productViewObj));
    var shopCategoryText = "";
    $(".goods-group-line .goods-gruop-select").each(function () {
        if ($(this).val() > 0) {
            shopCategoryText += $(this).val() + ",";
        }
    })
    if (shopCategoryText != "") {
        shopCategoryText = shopCategoryText.substring(0, shopCategoryText.length - 1);
        var goodsgroup_array = shopCategoryText.split(",");
        var goodsgroup_array = undulpicate(goodsgroup_array);
        shopCategoryText = goodsgroup_array.join(",");
    }
    productViewObj.groupArray = shopCategoryText;
    productViewObj.supplierId = $("#supplierSelect").val();//供货商
    productViewObj.brandId = $("#brand_id").val();//品牌id
    productViewObj.picture = img_id_arr.split(",")[0];
    var imageVals = img_id_arr;// 在页面中获取的
    productViewObj.imageArray = imageVals;// 商品图片分组
    //sku规格图片
    var sku_img_obj = $(".sku_upload_img_id");
    var sku_picture_obj = new Array();
    for (var $i = 0; $i < sku_img_obj.length; $i++) {
        var $checkObj = $(sku_img_obj[$i]);
        var spec_id = $checkObj.attr("spec_id");
        var spec_value_id = $checkObj.attr("spec_value_id");
        var img_id = $checkObj.val();
        var is_have = 0;
        for (var i = 0; i < sku_picture_obj.length; i++) {
            if (sku_picture_obj[i].spec_id == spec_id && sku_picture_obj[i].spec_value_id == spec_value_id) {
                sku_picture_obj[i]["img_ids"] = sku_picture_obj[i]["img_ids"] + "," + img_id;
                is_have = 1;
            }

        }
        if (is_have == 0) {
            //给此规格添加对象内部空间 并添加此属性
            var obj_length = sku_picture_obj.length;
            sku_picture_obj[obj_length] = new Object();
            sku_picture_obj[obj_length].spec_id = spec_id;
            sku_picture_obj[obj_length].spec_value_id = spec_value_id;
            sku_picture_obj[obj_length]["img_ids"] = img_id;
        }
    }
    productViewObj.sku_picture_vlaues = JSON.stringify(sku_picture_obj);
    productViewObj.skuArray = synchroSkuValueData();
    productViewObj.goods_spec_format = JSON.stringify($specObj);
    productViewObj.goods_attribute_id = $("#goodsType").val();
    productViewObj.sort = $("#hidden_sort").val();
    var goods_attribute_arr = new Array();
    $(".js-attribute-text").each(function () {
        var goods_attribute = {
            attr_value_id: $(this).attr("data-attribute-value-id"),
            attr_value: $(this).attr("data-attribute-value"),
            attr_value_name: $(this).val(),
            sort: $(this).attr("data-attribute-sort")
        };
        goods_attribute_arr.push(goods_attribute);
    });
    $(".js-attribute-radio").each(function () {
        if ($(this).is(":checked")) {
            var goods_attribute = {
                attr_value_id: $(this).attr("data-attribute-value-id"),
                attr_value: $(this).attr("data-attribute-value"),
                attr_value_name: $(this).val(),
                sort: $(this).attr("data-attribute-sort")
            };
            goods_attribute_arr.push(goods_attribute);
        }
    });
    $(".js-attribute-checkbox").each(function () {
        if ($(this).is(":checked")) {
            var goods_attribute = {
                attr_value_id: $(this).attr("data-attribute-value-id"),
                attr_value: $(this).attr("data-attribute-value"),
                attr_value_name: $(this).val(),
                sort: $(this).attr("data-attribute-sort")
            };
            goods_attribute_arr.push(goods_attribute);
        }
    });
    productViewObj.goods_attribute = "";
    if (goods_attribute_arr.length > 0) {
        productViewObj.goods_attribute = JSON.stringify(goods_attribute_arr);
    }
    //productViewObj.point_exchange_type = $("#integralSelect").val();
    productViewObj.point_exchange_type = $("input[name='integralSelect']:checked").val();
    productViewObj.province_id = $("#provinceSelect").val();// 商品所在地：省
    productViewObj.city_id = $("#citySelect").val();// 商品所在地：市
    productViewObj.qrcode = $("#hidden_qrcode").val();
    //物流信息
    productViewObj.goods_weight = $("#goods_weight").val();
    productViewObj.goods_volume = $("#goods_volume").val();
    productViewObj.shipping_fee_type = $("input[name='shipping_fee_type']:checked").val();
    ;
    return productViewObj;
}
//非空判断
function IsEmpty(obj) {
    var val = $.trim($(obj).val());
    if (val == "") {
        $(obj).focus();
        return false;
    }
    return true;
}
/**
 * 获取当前时间随机数
 * @returns
 */
function getDate() {
    var date = new Date();
    var time = date.getSeconds().toString() + date.getMilliseconds().toString();
    return time;
}
/**
 * 循环价格
 */
function eachPrice() {
    var $price = 0;
    $.each($('input[name="sku_price"]'), function (i, item) {
        var $this = $(item);
        var num = $this.val();
        var numint = parseFloat(num);
        var priceint = parseFloat($price);
        if ($price == 0 || numint < priceint) $price = num;
    });
    $("#txtProductSalePrice").val($price);
    app.goods_price = $price;
}
/**
 * 循环市场价
 */
function eachMarketPrice() {
    var $price = 0;
    $.each($('input[name="market_price"]'), function (i, item) {
        var $this = $(item);
        var num = $this.val();
        var numint = parseFloat(num);
        var priceint = parseFloat($price);
        if ($price == 0 || numint < priceint) $price = num;
    });
    $("#txtProductMarketPrice").val($price);
}
/**
 * 循环成本价
 */
function eachCostPrice() {
    var $price = 0;
    $.each($('input[name="cost_price"]'), function (i, item) {
        var $this = $(item);
        var num = $this.val();
        var numint = parseFloat(num);
        var priceint = parseFloat($price);
        if ($price == 0 || numint < priceint) $price = num;
    });
    $("#txtProductCostPrice").val($price);
}
/**
 * 循环库存
 */
function eachInput() {
    var $stockTotal = 0;
    $.each($('input[name="stock_num"]'), function (i, item) {
        var $this = $(item);
        var num = 0;
        num = parseInt($this.val());
        $stockTotal = $stockTotal + num;
    });
    $("#txtProductCount").val($stockTotal);
}
/**
 * 删除本条扩展分类
 * @param obj
 */
function removeParentBox(obj) {
    $(obj).parent().remove();
}
//导航分组
function changeGoodsGroup(obj) {
    if ($(obj).val() > 0) {
        var exist_num = 0;
        $(".goods-group-div .goods-group-line .goods-gruop-select").each(function () {
            if ($(this).val() == 0) exist_num++;
        })
        if (exist_num < 1) {
            if ($.trim(group_str) != "" && $.trim(group_str) != undefined) {
                var html = "<div class='goods-group-line'><select class='goods-gruop-select' onchange='changeGoodsGroup(this);'>";
                html += "<option value='0'></option>"
                var group_array = eval(group_str);
                for (var i = 0; i < group_array.length; i++) {
                    html += "<option value='" + group_array[i]["group_id"] + "'>" + group_array[i]["group_name"] + "</option>"
                }
                html += "</select></div>";
                $(".goods-group-div").append(html);
            } else {
                $(".span-error").show();
            }
        }
    } else {
        if ($(".goods-group-div .goods-group-line .goods-gruop-select").length > 1) $(obj).parent().remove();
    }
}
//数组去重
function undulpicate(array) {
    for (var i = 0; i < array.length; i++) {
        for (var j = i + 1; j < array.length; j++) {
            //注意 ===
            if (array[i] === array[j]) {
                array.splice(j, 1);
                j--;
            }
        }
    }
    return array;
}
/**
 * 根据商品类型id，查询商品规格信息
 * @param attr_id 规格属性id
 */
function getGoodsAttributeListByAttrId(attr_id, callBack) {
    if (!isNaN(attr_id) && attr_id > 0) {
        $.ajax({
            url: host + "addons/yb_mingpian/core/index.php?s=/admin/goods/getGoodsSpecListByAttrId",
            type: "post",
            data: {"attr_id": parseInt(attr_id)},
            success: function (res) {
                if (res != -1) {
                    var attribute_length = res.attribute_list.length;
                    //商品属性集合
                    if (attribute_length > 0) {
                        var html = "";
                        for (var i = 0; i < attribute_length; i++) {
                            var curr = res.attribute_list[i];
                            if ($.trim(curr.value_items) == "" && parseInt(curr.type) != 1) continue;
                            if ($.trim(curr.attr_value_name) != "") {
                                html += '<tr style="padding-top:15px;padding-bottom:15px;">';
                                html += '<td width="10%" style="border:1px solid #E9E9E9;"align="right" class="txt12" data-value="' + curr.attr_value_name + '">' + curr.attr_value_name + '</td>';
                                html += '<td width="80%" style="border:1px solid #E9E9E9;">';
                                switch (parseInt(curr.type)) {
                                    case 1:
                                        //输入框
                                        html += '<input type="text" class="js-attribute-text" id="input-text-' + curr.attr_value_id + '-' + curr.attr_value_id + '"data-attribute-value-id="' + curr.attr_value_id + '" data-attribute-value="' + curr.attr_value_name + '" />';
                                        break;
                                    case 2:
                                        //单选框
                                        for (var j = 0; j < curr.value_items.length; j++) {
                                            var value = curr.value_items[j];
                                            if ($.trim(value) != "") {
                                                html += '<div class="goods-sku-attribute-item-radio">';
                                                html += '<input type="radio" value="' + value + '" class="js-attribute-radio" id="radio_value_item' + curr.attr_value_id + '-' + j + '" data-attribute-value-id="' + curr.attr_value_id + '" data-attribute-value="' + curr.attr_value_name + '"  name="radio_value' + i + '" />&nbsp;';
                                                html += '<label for="radio_value_item' + curr.attr_value_id + '-' + j + '">' + value + '</label>';
                                                html += '</div>';
                                            }
                                        }
                                        break;
                                    case 3:
                                        //复选框
                                        for (var j = 0; j < curr.value_items.length; j++) {
                                            var value = curr.value_items[j];
                                            if ($.trim(value) != "") {
                                                html += '<div class="goods-sku-attribute-item-checkbox">';
                                                html += '<input type="checkbox" value="' + value + '" class="js-attribute-checkbox" id="checkbox_value_item' + curr.attr_value_id + '-' + j + '" data-attribute-value-id="' + curr.attr_value_id + '" data-attribute-value="' + curr.attr_value_name + '"  name="checkbox_value_item' + i + '" />&nbsp;';
                                                html += '<label for="checkbox_value_item' + curr.attr_value_id + '-' + j + '">' + value + '</label>';
                                                html += '</div>';
                                            }
                                        }
                                        break;
                                }
                                html += '</td>';
                                html += '</tr>';
                            }
                        }
                        $(".js-goods-sku-attribute").html(html);
                        if (callBack != undefined) callBack();
                    }
                    $(".js-goods-attribute-block").show();
                }
            }
        });
    }
}