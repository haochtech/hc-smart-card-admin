!function () {
    var utils = UM.utils, browser = UM.browser,
        Base = {
        checkURL: function (a) {
            return a ? (a = utils.trim(a), a.length <= 0 ? !1 : (0 !== a.search(/http:\/\/|https:\/\//) && (a += "http://"), a = a.replace(/\?[\s\S]*$/, ""), /(.gif|.jpg|.jpeg|.png)$/i.test(a) ? a : !1)) : !1
        }, getAllPic: function (a, b) {
            var e = [], f = $(a, b);
            return $.each(f, function (a, b) {
                return $(b).removeAttr("width").removeAttr("height"), e.push({_src: b.src, src: b.src})
            }), e
        }, scale: function (a, b, c, d) {
            var g, e = 0, f = 0, h = a.width || c, i = a.height || d;
            return (h > b || i > b) && (h >= i ? (e = h - b) && (g = (e / h).toFixed(2), a.height = i - i * g, a.width = b) : (f = i - b) && (g = (f / i).toFixed(2), a.width = h - h * g, a.height = b)), this
        }, close: function (a) {
            return a.css({
                top: (a.parent().height() - a.height()) / 2,
                left: (a.parent().width() - a.width()) / 2
            }).prev().on("click", function () {
                $(this).parent().remove().hasClass("edui-image-upload-item") && (Upload.showCount--, Upload.updateView())
            }), this
        }, createImgBase64: function (a, b, c) {
            if (browser.webkit) a.src = window.webkitURL.createObjectURL(b); else if (browser.gecko) a.src = window.URL.createObjectURL(b); else {
                var d = new FileReader;
                d.onload = function () {
                    a.src = this.result, c.append(a)
                }, d.readAsDataURL(b)
            }
        }, callback: function (a, b, c, d) {
            if ("SUCCESS" == d) {
                Upload.showCount++;
                var e = $("<img src='" + a.options.imagePath + c + "' class='edui-image-pic' />"),
                    f = $("<div class='edui-image-item edui-image-upload-item'><div class='edui-image-close'></div></div>").append(e);
                $(".edui-image-upload2", b).length < 1 ? ($(".edui-image-content", b).append(f), Upload.render(".edui-image-content", 2).config(".edui-image-upload2")) : $(".edui-image-upload2", b).before(f).show(), e.on("load", function () {
                    Base.scale(this, 120), Base.close($(this)), $(".edui-image-content", b).focus()
                })
            } else currentDialog.showTip(d), window.setTimeout(function () {
                currentDialog.hideTip()
            }, 3e3);
            Upload.toggleMask()
        }
    },
        Upload = {
        showCount: 0,
        uploadTpl: '<div class="edui-image-upload%%"><span class="edui-image-icon"></span><form class="edui-image-form" method="post" enctype="multipart/form-data" target="up"><input style="filter: alpha(opacity=0);" class="edui-image-file" type="file" hidefocus name="upfile" accept="image/gif,image/jpeg,image/png,image/jpg,image/bmp"/></form></div>',
        init: function (a, b) {
            var c = this;
            return c.editor = a, c.dialog = b, c.render(".edui-image-local", 1), c.config(".edui-image-upload1"), c.submit(), c.drag(), $(".edui-image-upload1").hover(function () {
                $(".edui-image-icon", this).toggleClass("hover")
            }), UM.browser.ie && UM.browser.version <= 9 || $(".edui-image-dragTip", c.dialog).css("display", "block"), c
        },
        render: function (a, b) {
            var c = this;
            return $(a, c.dialog).append($(c.uploadTpl.replace(/%%/g, b))), c
        },
        config: function (a) {
            var b = this, c = b.editor.options.imageUrl;
            return c = c + (-1 == c.indexOf("?") ? "?" : "&") + "editorid=" + b.editor.id, $("form", $(a, b.dialog)).attr("action", c), b
        },
        uploadComplete: function (r) {
            var json, lang, me = this;
            try {
                json = eval("(" + r + ")"), Base.callback(me.editor, me.dialog, json.url, json.state)
            } catch (e) {
                lang = me.editor.getLang("image"), Base.callback(me.editor, me.dialog, "", lang && lang.uploadError || "Error!")
            }
        },
        submit: function (a) {
            var b = this,
                c = $('<input style="filter: alpha(opacity=0);" class="edui-image-file" type="file" hidefocus="" name="upfile" accept="image/gif,image/jpeg,image/png,image/jpg,image/bmp">'),
                c = c[0];
            return $(b.dialog).delegate(".edui-image-file", "change", function () {
                this.parentNode && ($('<iframe name="up"  style="display: none"></iframe>').insertBefore(b.dialog).on("load", function () {
                    var a = this.contentWindow.document.body.innerHTML;
                    "" != a && (b.uploadComplete(a), $(this).unbind("load"), $(this).remove())
                }), $(this).parent()[0].submit(), Upload.updateInput(c), b.toggleMask("Loading...."), a && a())
            }), b
        },
        updateInput: function (a) {
            $(".edui-image-file", this.dialog).each(function (b, c) {
                c.parentNode.replaceChild(a.cloneNode(!0), c)
            })
        },
        updateView: function () {
            0 === Upload.showCount && ($(".edui-image-upload2", this.dialog).hide(), $(".edui-image-dragTip", this.dialog).show(), $(".edui-image-upload1", this.dialog).show())
        },
        drag: function () {
            var a = this;
            UM.browser.ie9below || a.dialog.find(".edui-image-content").on("drop", function (b) {
                var c = b.originalEvent.dataTransfer.files, d = document.createElement("img"), e = !1;
                $.each(c, function (b, f) {
                    var g, h;
                    /^image/.test(f.type) && (Base.createImgBase64(d, f, a.dialog), g = new XMLHttpRequest, g.open("post", a.editor.getOpt("imageUrl") + "?type=ajax", !0), g.setRequestHeader("X-Requested-With", "XMLHttpRequest"), h = new FormData, h.append(a.editor.getOpt("imageFieldName"), f), g.send(h), g.addEventListener("load", function (e) {
                        var f = e.target.response;
                        a.uploadComplete(f), b == c.length - 1 && $(d).remove()
                    }), e = !0)
                }), e && (b.preventDefault(), a.toggleMask("Loading...."))
            }).on("dragover", function (a) {
                a.preventDefault()
            })
        },
        toggleMask: function (a) {
            var b = this, c = $(".edui-image-mask", b.dialog);
            if (a) UM.browser.ie && UM.browser.version <= 9 || $(".edui-image-dragTip", b.dialog).css("display", "none"), $(".edui-image-upload1", b.dialog).css("display", "none"), c.addClass("edui-active").html(a); else {
                if (c.removeClass("edui-active").html(), Upload.showCount > 0)return b;
                UM.browser.ie && UM.browser.version <= 9 || $(".edui-image-dragTip", b.dialog).css("display", "block"), $(".edui-image-upload1", b.dialog).css("display", "block")
            }
            return b
        }
    },
        NetWork = {
        init: function (a, b) {
            var c = this;
            c.editor = a, c.dialog = b, c.initEvt()
        }, initEvt: function () {
            var b, a = this, c = $(".edui-image-searchTxt", a.dialog);
            $(".edui-image-searchAdd", a.dialog).on("click", function () {
                b = Base.checkURL(c.val()), b && $("<img src='" + b + "' class='edui-image-pic' />").on("load", function () {
                    var b = $("<div class='edui-image-item'><div class='edui-image-close'></div></div>").append(this);
                    $(".edui-image-searchRes", a.dialog).append(b), Base.scale(this, 120), b.width($(this).width()), Base.close($(this)), c.val("")
                })
            }).hover(function () {
                $(this).toggleClass("hover")
            })
        }
    },
        $tab = null,
        currentDialog = null;

    UM.registerWidget("image", {
        tpl: '<link rel="stylesheet" type="text/css" href="<%=image_url%>image.css"><div class="edui-image-wrapper"><ul class="edui-tab-nav"><li class="edui-tab-item edui-active"><a data-context=".edui-image-local" class="edui-tab-text"><%=lang_tab_local%></a></li><li  class="edui-tab-item"><a data-context=".edui-image-JimgSearch" class="edui-tab-text"><%=lang_tab_imgSearch%></a></li></ul><div class="edui-tab-content"><div class="edui-image-local edui-tab-pane edui-active"><div class="edui-image-content"></div><div class="edui-image-mask"></div><div class="edui-image-dragTip"><%=lang_input_dragTip%></div></div><div class="edui-image-JimgSearch edui-tab-pane"><div class="edui-image-searchBar"><table><tr><td><input class="edui-image-searchTxt" type="text"></td><td><div class="edui-image-searchAdd"><%=lang_btn_add%></div></td></tr></table></div><div class="edui-image-searchRes"></div></div></div></div>',
        initContent: function (a, b) {
            var e, c = a.getLang("image")["static"],
                d = $.extend({}, c, {image_url: UMEDITOR_CONFIG.UMEDITOR_HOME_URL + "dialogs/image/"});
            Upload.showCount = 0, c && (e = $.parseTmpl(this.tpl, d)), currentDialog = b.edui(), this.root().html(e)
        },
        initEvent: function (a, b) {

            $tab = $.eduitab({selector: ".edui-image-wrapper"}).edui().on("beforeshow", function (a) {
                a.stopPropagation()
            }), Upload.init(a, b), NetWork.init(a, b)
        },
        buttons: {
            ok: {
                exec: function (a, b) {
                    var e, c = "", d = $tab.activate();
                    0 == d ? c = ".edui-image-content .edui-image-pic" : 1 == d && (c = ".edui-image-searchRes .edui-image-pic"), e = Base.getAllPic(c, b, a), -1 != d && a.execCommand("insertimage", e)
                }
            }, cancel: {}
        },
        width: 700,
        height: 408
    }, function (a, b, c, d) {
        Base.callback(a, b, c, d)
    })
}();

function um_images(id_array,path_array) {
    var parr = path_array.split(",");
    var arr = [];
    parr.forEach(function (c, index) {
        var item = {};
        item.src = c;
        item._src = c;
        arr.push(item);
    });
    NOW_UM.execCommand("insertimage", arr);
}