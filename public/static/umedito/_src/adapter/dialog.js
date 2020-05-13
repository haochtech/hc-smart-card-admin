var NOW_UM = null;
var snap = location.href.toString(),
    cuff = snap.split("addons"),
    host = cuff[0];
UM.registerUI("link image video map formula", function (a) {
    var ss=this.options.UMEDITOR_HOME_URL;
    if(ss.indexOf("static/umedito") == -1){
        this.options.UMEDITOR_HOME_URL=host + "addons/yb_mingpian/core/public/static/umedito/";
    }
    var c, d, b = this, e = {
        title: b.options.labelMap && b.options.labelMap[a] || b.getLang("labelMap." + a),
        url: b.options.UMEDITOR_HOME_URL + "dialogs/" + a + "/" + a + ".js"
    }, f = $.eduibutton({icon: a, title: this.getLang("labelMap")[a] || ""});
    return utils.loadFile(document, {src: e.url, tag: "script", type: "text/javascript", defer: "defer"}, function () {
        var h, i, g = UM.getWidgetData(a);
        g && (g.buttons && (h = g.buttons.ok, h && (e.oklabel = h.label || b.getLang("ok"), h.exec && (e.okFn = function () {
            return $.proxy(h.exec, null, b, d)()
        })), i = g.buttons.cancel, i && (e.cancellabel = i.label || b.getLang("cancel"), i.exec && (e.cancelFn = function () {
            return $.proxy(i.exec, null, b, d)()
        }))), g.width && (e.width = g.width), g.height && (e.height = g.height), d = $.eduimodal(e), d.attr("id", "edui-dialog-" + a).addClass("edui-dialog-" + a).find(".edui-modal-body").addClass("edui-dialog-" + a + "-body"), d.edui().on("beforehide", function () {
            var a = b.selection.getRange();
            a.equals(c) && a.select()
        }).on("beforeshow", function () {
            var e = this.root(), f = null, g = null;
            c = b.selection.getRange(), e.parent()[0] || b.$container.find(".edui-dialog-container").append(e), $.IE6 && (f = {
                width: $(window).width(),
                height: $(window).height()
            }, g = e.parents(".edui-toolbar")[0].getBoundingClientRect(), e.css({
                position: "absolute",
                margin: 0,
                left: (f.width - e.width()) / 2 - g.left,
                top: 100 - g.top
            })), UM.setWidgetBody(a, d, b), UM.setTopEditor(b)
        }).on("afterbackdrop", function () {
            this.$backdrop.css("zIndex", b.getOpt("zIndex") + 1).appendTo(b.$container.find(".edui-dialog-container")), d.css("zIndex", b.getOpt("zIndex") + 2)
        }).on("beforeok", function () {
            try {
                c.select()
            } catch (a) {
            }
        }).attachTo(f,b))
    }), b.addListener("selectionchange", function () {
        var b = this.queryCommandState(a);
        f.edui().disabled(-1 == b).active(1 == b)
    }), f
});