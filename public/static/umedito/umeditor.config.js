etpl.config({
    commandOpen: "<%",
    commandClose: "%>"
}), function() {
    var a = window.UMEDITOR_HOME_URL ||
        function() {
            function a() {
                this.documentURL = self.document.URL.toString() || self.location.href.toString(), this.separator = "/", this.separatorPattern = /\\|\//g, this.currentDir = "./", this.currentDirPattern = /^[.]\/]/, this.path = this.documentURL, this.stack = [], this.push(this.documentURL)
            }
            function b() {
                var b = a.getProtocol(this.path || "");
                b ? (this.protocol = b, this.localSeparator = /\\|\//.exec(this.path.replace(b, ""))[0], this.stack = []) : (b = /\\|\//.exec(this.path), b && (this.localSeparator = b[0]))
            }
            function c() {
                var b, c, d, e, f = this.path.replace(this.currentDirPattern, "");
                for (a.hasProtocol(this.path) && (f = f.replace(this.protocol, "")), f = f.split(this.localSeparator), f.length = f.length - 1, c = 0, d = f.length, e = this.stack; d > c; c++) b = f[c], b && (a.isParentPath(b) ? e.pop() : e.push(b))
            }
            a.isParentPath = function(a) {
                return ".." === a
            }, a.hasProtocol = function(b) {
                return !!a.getProtocol(b)
            }, a.getProtocol = function(a) {
                var b = /^[^:]*:\/*/.exec(a);
                return b ? b[0] : null
            }, a.prototype = {
                push: function(a) {
                    return this.path = a, b.call(this), c.call(this), this
                },
                getPath: function() {
                    return this + ""
                },
                toString: function() {
                    return this.protocol + this.stack.concat([""]).join(this.separator)
                }
            };
            var d = document.getElementsByTagName("script");
            return d = d[d.length - 1].src, (new a).push(d) + ""
        }();
    window.UMEDITOR_CONFIG = {
        UMEDITOR_HOME_URL: a,
        imageUrl: a + "php/imageUp.php",
        imagePath: a + "php/",
        imageFieldName: "upfile",
        toolbar: ["source undo redo | bold italic underline | forecolor | removeformat", "| selectall cleardoc paragraph | fontsize", "| justifyleft justifycenter justifyright justifyjustify | image", "| preview fullscreen"],
        filterRules: {},
        xssFilterRules: !0,
        inputXssFilter: !0,
        outputXssFilter: !0,
        whiteList: {
            a: ["target", "href", "title", "style", "class", "id"],
            abbr: ["title", "style", "class", "id"],
            address: ["style", "class", "id"],
            area: ["shape", "coords", "href", "alt", "style", "class", "id"],
            article: ["style", "class", "id"],
            aside: ["style", "class", "id"],
            audio: ["autoplay", "controls", "loop", "preload", "src", "style", "class", "id"],
            b: ["style", "class", "id"],
            bdi: ["dir"],
            bdo: ["dir"],
            big: [],
            blockquote: ["cite", "style", "class", "id"],
            br: [],
            caption: ["style", "class", "id"],
            center: [],
            cite: [],
            code: ["style", "class", "id"],
            col: ["align", "valign", "span", "width", "style", "class", "id"],
            colgroup: ["align", "valign", "span", "width", "style", "class", "id"],
            dd: ["style", "class", "id"],
            del: ["datetime", "style", "class", "id"],
            details: ["open", "style", "class", "id"],
            div: ["style", "class", "id"],
            dl: ["style", "class", "id"],
            dt: ["style", "class", "id"],
            em: ["style", "class", "id"],
            embed: ["style", "class", "id", "_url", "type", "pluginspage", "src", "width", "height", "wmode", "play", "loop", "menu", "allowscriptaccess", "allowfullscreen"],
            font: ["color", "size", "face", "style", "class", "id"],
            footer: ["style", "class", "id"],
            h1: ["style", "class", "id"],
            h2: ["style", "class", "id"],
            h3: ["style", "class", "id"],
            h4: ["style", "class", "id"],
            h5: ["style", "class", "id"],
            h6: ["style", "class", "id"],
            header: ["style", "class", "id"],
            hr: ["style", "class", "id"],
            i: ["style", "class", "id"],
            img: ["src", "alt", "title", "width", "height", "style", "class", "id", "_url"],
            ins: ["datetime", "style", "class", "id"],
            li: ["style", "class", "id"],
            mark: [],
            nav: [],
            ol: ["style", "class", "id"],
            p: ["style", "class", "id"],
            pre: ["style", "class", "id"],
            s: [],
            section: [],
            small: ["style", "class", "id"],
            span: ["style", "class", "id"],
            sub: ["style", "class", "id"],
            sup: ["style", "class", "id"],
            strong: ["style", "class", "id"],
            table: ["width", "border", "align", "valign", "style", "class", "id"],
            tbody: ["align", "valign", "style", "class", "id"],
            td: ["width", "rowspan", "colspan", "align", "valign", "style", "class", "id"],
            tfoot: ["align", "valign", "style", "class", "id"],
            th: ["width", "rowspan", "colspan", "align", "valign", "style", "class", "id"],
            thead: ["align", "valign", "style", "class", "id"],
            tr: ["rowspan", "align", "valign", "style", "class", "id"],
            tt: ["style", "class", "id"],
            u: [],
            ul: ["style", "class", "id"],
            svg: ["style", "class", "id", "width", "height", "xmlns", "fill", "viewBox"],
            video: ["autoplay", "controls", "loop", "preload", "src", "height", "width", "style", "class", "id"]
        }
    }
}();