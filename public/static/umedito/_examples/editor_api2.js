var addd = '';
var snap = location.href.toString(),
    cuff = snap.split("addons"),
    host = cuff[0];
!
    function () {
        var d, c,
            a = ["editor.js", "core/browser.js", "core/utils.js", "core/EventBase.js", "core/dtd.js", "core/domUtils.js", "core/Range.js", "core/Selection.js", "core/Editor.js", "core/filterword.js", "core/node.js", "core/htmlparser.js", "core/filternode.js", "plugins/inserthtml.js", "plugins/image.js", "plugins/justify.js", "plugins/font.js", "plugins/link.js", "plugins/print.js", "plugins/paragraph.js", "plugins/horizontal.js", "plugins/cleardoc.js", "plugins/undo.js", "plugins/paste.js", "plugins/list.js", "plugins/source.js", "plugins/enterkey.js", "plugins/preview.js", "plugins/basestyle.js", "plugins/video.js", "plugins/selectall.js", "plugins/removeformat.js", "plugins/keystrokes.js", "plugins/autosave.js", "plugins/autoupload.js", "plugins/formula.js", "plugins/xssFilter.js", "ui/widget.js", "ui/button.js", "ui/toolbar.js", "ui/menu.js", "ui/dropmenu.js", "ui/splitbutton.js", "ui/colorsplitbutton.js", "ui/popup.js", "ui/scale.js", "ui/colorpicker.js", "ui/combobox.js", "ui/buttoncombobox.js", "ui/modal.js", "ui/tooltip.js", "ui/tab.js", "ui/separator.js", "ui/scale.js", "adapter/adapter.js", "adapter/button.js", "adapter/fullscreen.js", "adapter/dialog.js", "adapter/popup.js", "adapter/imagescale.js", "adapter/autofloat.js", "adapter/source.js", "adapter/combobox.js"],
            // b = host + "addons/yb_mingpian/core/public/static/umedito/_src/";
            b = "public/static/umedito/_src/";
        // var script=document.createElement("script");
        // script.type="text/javascript";
        for (c = 0; d = a[c++];) {
            addd += '<script type="text/javascript" src="' + b + d + '"></script>';
            // $("#newks2").append('<script type="text/javascript" src="' + b + d + '"></script>');
            // script.src= b + d ;
            // document.getElementById('newks2').appendChild(script);
        }
        console.log("wwwwwwwww");
        $("#newks2").append(addd);
    }();
function do_someaa() {
    console.log(addd);
    // addd+='<script type="text/javascript" src="/public/static/umedito/lang/zh-cn/zh-cn.js"></script>';
    $("#newks2").append(addd);
}