!function () {
    function a(a, b) {
        var d, f, k, e, g, h, i, j, l, m, n, c = domUtils.getNodeIndex;
        if (a = a.duplicate(), a.collapse(b), d = a.parentElement(), !d.hasChildNodes())return {
            container: d,
            offset: 0
        };
        for (e = d.children, g = a.duplicate(), h = 0, i = e.length - 1, j = -1; i >= h;)if (j = Math.floor((h + i) / 2), f = e[j], g.moveToElementText(f), l = g.compareEndPoints("StartToStart", a), l > 0) i = j - 1; else {
            if (!(0 > l))return {container: d, offset: c(f)};
            h = j + 1
        }
        if (-1 == j) {
            if (g.moveToElementText(d), g.setEndPoint("StartToStart", a), k = g.text.replace(/(\r\n|\r)/g, "\n").length, e = d.childNodes, !k)return f = e[e.length - 1], {
                container: f,
                offset: f.nodeValue.length
            };
            for (m = e.length; k > 0;)k -= e[--m].nodeValue.length;
            return {container: e[m], offset: -k}
        }
        if (g.collapse(l > 0), g.setEndPoint(l > 0 ? "StartToStart" : "EndToStart", a), k = g.text.replace(/(\r\n|\r)/g, "\n").length, !k)return dtd.$empty[f.tagName] || dtd.$nonChild[f.tagName] ? {
            container: d,
            offset: c(f) + (l > 0 ? 0 : 1)
        } : {container: f, offset: l > 0 ? 0 : f.childNodes.length};
        for (; k > 0;)try {
            n = f, f = f[l > 0 ? "previousSibling" : "nextSibling"], k -= f.nodeValue.length
        } catch (o) {
            return {container: d, offset: c(n)}
        }
        return {container: f, offset: l > 0 ? -k : f.nodeValue.length + k}
    }

    function b(b, c) {
        if (b.item) c.selectNode(b.item(0)); else {
            var d = a(b, !0);
            c.setStart(d.container, d.offset), 0 != b.compareEndPoints("StartToEnd", b) && (d = a(b, !1), c.setEnd(d.container, d.offset))
        }
        return c
    }

    function c(a, b) {
        var c, e;
        try {
            c = a.getNative(b).createRange()
        } catch (d) {
            return null
        }
        return e = c.item ? c.item(0) : c.parentElement(), (e.ownerDocument || e) === a.document ? c : null
    }

    var d = dom.Selection = function (a, b) {
        var d = this;
        d.document = a, d.body = b, browser.ie9below && $(b).on("beforedeactivate", function () {
            d._bakIERange = d.getIERange()
        }).on("activate", function () {
            try {
                var a = c(d);
                a && d.rangeInBody(a) || !d._bakIERange || d._bakIERange.select()
            } catch (b) {
            }
            d._bakIERange = null
        })
    };
    d.prototype = {
        hasNativeRange: function () {
            var a, b;
            if (!browser.ie || browser.ie9above) {
                if (b = this.getNative(), !b.rangeCount)return !1;
                a = b.getRangeAt(0)
            } else a = c(this);
            return this.rangeInBody(a)
        }, getNative: function (a) {
            var b = this.document;
            try {
                return b ? browser.ie9below || a ? b.selection : domUtils.getWindow(b).getSelection() : null
            } catch (c) {
                return null
            }
        }, getIERange: function (a) {
            var b = c(this, a);
            return b && this.rangeInBody(b, a) || !this._bakIERange ? b : this._bakIERange
        }, rangeInBody: function (a, b) {
            var c = browser.ie9below || b ? a.item ? a.item() : a.parentElement() : a.startContainer;
            return c === this.body || domUtils.inDoc(c, this.body)
        }, cache: function () {
            this.clear(), this._cachedRange = this.getRange(), this._cachedStartElement = this.getStart(), this._cachedStartElementPath = this.getStartElementPath()
        }, getStartElementPath: function () {
            if (this._cachedStartElementPath)return this._cachedStartElementPath;
            var a = this.getStart();
            return a ? domUtils.findParents(a, !0, null, !0) : []
        }, clear: function () {
            this._cachedStartElementPath = this._cachedRange = this._cachedStartElement = null
        }, isFocus: function () {
            return this.hasNativeRange()
        }, getRange: function () {
            function c(b) {
                for (var c = a.body.firstChild, d = b.collapsed; c && c.firstChild;)b.setStart(c, 0), c = c.firstChild;
                b.startContainer || b.setStart(a.body, 0), d && b.collapse(!0)
            }

            var d, e, g, h, i, a = this;
            if (null != a._cachedRange)return this._cachedRange;
            if (d = new dom.Range(a.document, a.body), browser.ie9below)if (e = a.getIERange(), e && this.rangeInBody(e))try {
                b(e, d)
            } catch (f) {
                c(d)
            } else c(d); else if (g = a.getNative(), g && g.rangeCount && a.rangeInBody(g.getRangeAt(0))) h = g.getRangeAt(0), i = g.getRangeAt(g.rangeCount - 1), d.setStart(h.startContainer, h.startOffset).setEnd(i.endContainer, i.endOffset), d.collapsed && domUtils.isBody(d.startContainer) && !d.startOffset && c(d); else {
                if (this._bakRange && (this._bakRange.startContainer === this.body || domUtils.inDoc(this._bakRange.startContainer, this.body)))return this._bakRange;
                c(d)
            }
            return this._bakRange = d
        }, getStart: function () {
            if (this._cachedStartElement)return this._cachedStartElement;
            var b, c, d, e, a = browser.ie9below ? this.getIERange() : this.getRange();
            if (browser.ie9below) {
                if (!a)return this.document.body.firstChild;
                if (a.item)return a.item(0);
                for (b = a.duplicate(), b.text.length > 0 && b.moveStart("character", 1), b.collapse(1), c = b.parentElement(), e = d = a.parentElement(); d = d.parentNode;)if (d == c) {
                    c = e;
                    break
                }
            } else if (c = a.startContainer, 1 == c.nodeType && c.hasChildNodes() && (c = c.childNodes[Math.min(c.childNodes.length - 1, a.startOffset)]), 3 == c.nodeType)return c.parentNode;
            return c
        }, getText: function () {
            var a, b;
            return this.isFocus() && (a = this.getNative()) ? (b = browser.ie9below ? a.createRange() : a.getRangeAt(0), browser.ie9below ? b.text : b.toString()) : ""
        }
    }
}();