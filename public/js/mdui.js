!function (a, b, c) {
    "use strict";
    var e, f, g, h, i, j, k, l, d = {};
    !function () {
        var b = 0;
        a.requestAnimationFrame || (a.requestAnimationFrame = a.webkitRequestAnimationFrame, a.cancelAnimationFrame = a.webkitCancelAnimationFrame), a.requestAnimationFrame || (a.requestAnimationFrame = function (c) {
            var e = (new Date).getTime(), f = Math.max(0, 16.7 - (e - b)), g = a.setTimeout(function () {
                c(e + f)
            }, f);
            return b = e + f, g
        }), a.cancelAnimationFrame || (a.cancelAnimationFrame = function (a) {
            clearTimeout(a)
        })
    }(), e = function (a, b, c) {
        function i(a) {
            return "number" == typeof a.length
        }

        function j(a, b) {
            var c, d;
            if (i(a)) {
                for (c = 0; c < a.length; c++) if (b.call(a[c], c, a[c]) === !1) return a
            } else for (d in a) if (a.hasOwnProperty(d) && b.call(a[d], d, a[d]) === !1) return a;
            return a
        }

        function k(a, b) {
            var d, e = [];
            return j(a, function (a, f) {
                d = b(f, a), null !== d && d !== c && e.push(d)
            }), f.apply([], e)
        }

        function l(a, b) {
            return j(b, function (b, c) {
                a.push(c)
            }), a
        }

        function m(a) {
            var c, b = [];
            for (c = 0; c < a.length; c++) -1 === b.indexOf(a[c]) && b.push(a[c]);
            return b
        }

        function n(a) {
            return null === a
        }

        function o(a, b) {
            return a.nodeName && a.nodeName.toLowerCase() === b.toLowerCase()
        }

        function p(a) {
            return "function" == typeof a
        }

        function q(a) {
            return "string" == typeof a
        }

        function r(a) {
            return "object" == typeof a
        }

        function s(a) {
            return r(a) && !n(a)
        }

        function t(a) {
            return a && a === a.window
        }

        function u(a) {
            return a && a.nodeType === a.DOCUMENT_NODE
        }

        function w(a) {
            var c, d;
            return v[a] || (c = b.createElement(a), b.body.appendChild(c), d = getComputedStyle(c, "").getPropertyValue("display"), c.parentNode.removeChild(c), "none" === d && (d = "block"), v[a] = d), v[a]
        }

        var z, d = [], e = d.slice, f = d.concat, g = Array.isArray, h = b.documentElement, v = {}, x = function (a) {
            var c, b = this;
            for (c = 0; c < a.length; c++) b[c] = a[c];
            return b.length = a.length, this
        }, y = function (c) {
            var f, g, h, d = [], e = 0;
            if (!c) return new x(d);
            if (c instanceof x) return c;
            if (q(c)) if (c = c.trim(), "<" === c[0] && ">" === c[c.length - 1]) for (h = "div", 0 === c.indexOf("<li") && (h = "ul"), 0 === c.indexOf("<tr") && (h = "tbody"), (0 === c.indexOf("<td") || 0 === c.indexOf("<th")) && (h = "tr"), 0 === c.indexOf("<tbody") && (h = "table"), 0 === c.indexOf("<option") && (h = "select"), g = b.createElement(h), g.innerHTML = c, e = 0; e < g.childNodes.length; e++) d.push(g.childNodes[e]); else for (f = "#" !== c[0] || c.match(/[ .<>:~]/) ? b.querySelectorAll(c) : [b.getElementById(c.slice(1))], e = 0; e < f.length; e++) f[e] && d.push(f[e]); else {
                if (p(c)) return y(b).ready(c);
                if (c.nodeType || c === a || c === b) d.push(c); else if (c.length > 0 && c[0].nodeType) for (e = 0; e < c.length; e++) d.push(c[e])
            }
            return new x(d)
        };
        return y.fn = x.prototype, y.extend = y.fn.extend = function (a) {
            var b, d, e, f;
            if (a === c) return this;
            if (b = arguments.length, 1 === b) {
                for (d in a) a.hasOwnProperty(d) && (this[d] = a[d]);
                return this
            }
            for (e = 1; b > e; e++) {
                f = arguments[e];
                for (d in f) f.hasOwnProperty(d) && (a[d] = f[d])
            }
            return a
        }, y.extend({
            each: j, merge: l, unique: m, map: k, contains: function (a, b) {
                return a && !b ? h.contains(a) : a !== b && a.contains(b)
            }, param: function (a) {
                function c(a, d) {
                    var e;
                    s(d) ? j(d, function (b, f) {
                        e = g(d) && !s(f) ? "" : b, c(a + "[" + e + "]", f)
                    }) : (e = n(d) || "" === d ? "" : "=" + encodeURIComponent(d), b.push(encodeURIComponent(a) + e))
                }

                if (!s(a)) return "";
                var b = [];
                return j(a, function (a, b) {
                    c(a, b)
                }), b.join("&")
            }
        }), y.fn.extend({
            each: function (a) {
                return j(this, a)
            }, map: function (a) {
                return new x(k(this, function (b, c) {
                    return a.call(b, c, b)
                }))
            }, get: function (a) {
                return a === c ? e.call(this) : this[a >= 0 ? a : a + this.length]
            }, slice: function () {
                return new x(e.apply(this, arguments))
            }, filter: function (a) {
                if (p(a)) return this.map(function (b, d) {
                    return a.call(d, b, d) ? d : c
                });
                var b = y(a);
                return this.map(function (a, d) {
                    return b.index(d) > -1 ? d : c
                })
            }, not: function (a) {
                var b = this.filter(a);
                return this.map(function (a, d) {
                    return b.index(d) > -1 ? c : d
                })
            }, offset: function () {
                if (this[0]) {
                    var b = this[0].getBoundingClientRect();
                    return {left: b.left + a.pageXOffset, top: b.top + a.pageYOffset, width: b.width, height: b.height}
                }
                return null
            }, offsetParent: function () {
                return this.map(function () {
                    for (var a = this.offsetParent; a && "static" === y(a).css("position");) a = a.offsetParent;
                    return a || h
                })
            }, position: function () {
                var b, c, d, a = this;
                return a[0] ? (d = {
                    top: 0,
                    left: 0
                }, "fixed" === a.css("position") ? c = a[0].getBoundingClientRect() : (b = a.offsetParent(), c = a.offset(), o(b[0], "html") || (d = b.offset()), d = {
                    top: d.top + b.css("borderTopWidth"),
                    left: d.left + b.css("borderLeftWidth")
                }), {
                    top: c.top - d.top - a.css("marginTop"),
                    left: c.left - d.left - a.css("marginLeft"),
                    width: c.width,
                    height: c.height
                }) : null
            }, show: function () {
                return this.each(function () {
                    "none" === this.style.display && (this.style.display = ""), "none" === a.getComputedStyle(this, "").getPropertyValue("display") && (this.style.display = w(this.nodeName))
                })
            }, hide: function () {
                return this.each(function () {
                    this.style.display = "none"
                })
            }, toggle: function () {
                return this.each(function () {
                    this.style.display = "none" === this.style.display ? "" : "none"
                })
            }, hasClass: function (a) {
                return this[0] && a ? this[0].classList.contains(a) : !1
            }, removeAttr: function (a) {
                return this.each(function () {
                    this.removeAttribute(a)
                })
            }, removeProp: function (a) {
                return this.each(function () {
                    try {
                        delete this[a]
                    } catch (b) {
                    }
                })
            }, eq: function (a) {
                var b = -1 === a ? this.slice(a) : this.slice(a, +a + 1);
                return new x(b)
            }, first: function () {
                return this.eq(0)
            }, last: function () {
                return this.eq(-1)
            }, index: function (a) {
                return a ? q(a) ? y(a).eq(0).parent().children().get().indexOf(this[0]) : this.get().indexOf(a) : this.eq(0).parent().children().get().indexOf(this[0])
            }, is: function (d) {
                var f, g, h, e = this[0];
                if (!e || d === c || null === d) return !1;
                if (q(d)) return e === b || e === a ? !1 : (h = e.matches || e.matchesSelector || e.webkitMatchesSelector || e.mozMatchesSelector || e.oMatchesSelector || e.msMatchesSelector, h.call(e, d));
                if (d === b || d === a) return e === d;
                if (d.nodeType || i(d)) {
                    for (f = d.nodeType ? [d] : d, g = 0; g < f.length; g++) if (f[g] === e) return !0;
                    return !1
                }
                return !1
            }, find: function (a) {
                var b = [];
                return this.each(function (c, d) {
                    l(b, d.querySelectorAll(a))
                }), new x(b)
            }, children: function (a) {
                var b = [];
                return this.each(function (c, d) {
                    j(d.childNodes, function (c, d) {
                        return 1 !== d.nodeType ? !0 : ((!a || a && y(d).is(a)) && b.push(d), void 0)
                    })
                }), new x(m(b))
            }, has: function (a) {
                var b = q(a) ? this.find(a) : y(a), c = b.length;
                return this.filter(function () {
                    for (var a = 0; c > a; a++) if (y.contains(this, b[a])) return !0
                })
            }, siblings: function (a) {
                return this.prevAll(a).add(this.nextAll(a))
            }, closest: function (a) {
                var b = this;
                return b.is(a) || (b = b.parents(a).eq(0)), b
            }, remove: function () {
                return this.each(function (a, b) {
                    b.parentNode && b.parentNode.removeChild(b)
                })
            }, add: function (a) {
                return new x(m(l(this.get(), y(a))))
            }, empty: function () {
                return this.each(function () {
                    this.innerHTML = ""
                })
            }, clone: function () {
                return this.map(function () {
                    return this.cloneNode(!0)
                })
            }, replaceWith: function (a) {
                return this.before(a).remove()
            }, serializeArray: function () {
                var b, c, a = [], d = this[0];
                return d && d.elements ? (y(e.call(d.elements)).each(function () {
                    b = y(this), c = b.attr("type"), "fieldset" === this.nodeName.toLowerCase() || this.disabled || -1 !== ["submit", "reset", "button"].indexOf(c) || -1 !== ["radio", "checkbox"].indexOf(c) && !this.checked || a.push({
                        name: b.attr("name"),
                        value: b.val()
                    })
                }), a) : a
            }, serialize: function () {
                var a = [];
                return j(this.serializeArray(), function (b, c) {
                    a.push(encodeURIComponent(c.name) + "=" + encodeURIComponent(c.value))
                }), a.join("&")
            }
        }), j(["val", "html", "text"], function (a, b) {
            var d = {0: "value", 1: "innerHTML", 2: "textContent"}, e = {0: c, 1: c, 2: null};
            y.fn[b] = function (b) {
                return b === c ? this[0] ? this[0][d[a]] : e[a] : this.each(function (c, e) {
                    e[d[a]] = b
                })
            }
        }), j(["attr", "prop", "css"], function (b, d) {
            var e = function (a, c, d) {
                0 === b ? a.setAttribute(c, d) : 1 === b ? a[c] = d : a.style[c] = d
            }, f = function (d, e) {
                if (!d) return c;
                var f;
                return f = 0 === b ? d.getAttribute(e) : 1 === b ? d[e] : a.getComputedStyle(d, null).getPropertyValue(e)
            };
            y.fn[d] = function (a, b) {
                var c = arguments.length;
                return 1 === c && q(a) ? f(this[0], a) : this.each(function (d, f) {
                    2 === c ? e(f, a, b) : j(a, function (a, b) {
                        e(f, a, b)
                    })
                })
            }
        }), j(["add", "remove", "toggle"], function (a, b) {
            y.fn[b + "Class"] = function (a) {
                if (!a) return this;
                var c = a.split(" ");
                return this.each(function (a, d) {
                    j(c, function (a, c) {
                        d.classList[b](c)
                    })
                })
            }
        }), j({Width: "width", Height: "height"}, function (b, d) {
            y.fn[d] = function (e) {
                var f, g, h;
                return e === c ? (f = this[0], t(f) ? f["inner" + b] : u(f) ? f.documentElement["scroll" + b] : (g = y(f), h = 0, "ActiveXObject" in a && "border-box" === g.css("box-sizing") && (h = parseFloat(g.css("padding-" + ("width" === d ? "left" : "top"))) + parseFloat(g.css("padding-" + ("width" === d ? "right" : "bottom")))), parseFloat(y(f).css(d)) + h)) : (isNaN(Number(e)) || "" === e || (e += "px"), this.css(d, e))
            }
        }), j({Width: "width", Height: "height"}, function (a, b) {
            y.fn["inner" + a] = function () {
                var a = this[b](), c = y(this[0]);
                return "border-box" !== c.css("box-sizing") && (a += parseFloat(c.css("padding-" + ("width" === b ? "left" : "top"))), a += parseFloat(c.css("padding-" + ("width" === b ? "right" : "bottom")))), a
            }
        }), z = function (a, b, c, d) {
            var f, e = [];
            return a.each(function (a, g) {
                for (f = g[d]; f;) {
                    if (2 === c) {
                        if (!b || b && y(f).is(b)) break;
                        e.push(f)
                    } else {
                        if (0 === c) {
                            (!b || b && y(f).is(b)) && e.push(f);
                            break
                        }
                        (!b || b && y(f).is(b)) && e.push(f)
                    }
                    f = f[d]
                }
            }), new x(m(e))
        }, j(["", "All", "Until"], function (a, b) {
            y.fn["prev" + b] = function (b) {
                var c = 0 === a ? this : y(this.get().reverse());
                return z(c, b, a, "previousElementSibling")
            }
        }), j(["", "All", "Until"], function (a, b) {
            y.fn["next" + b] = function (b) {
                return z(this, b, a, "nextElementSibling")
            }
        }), j(["", "s", "sUntil"], function (a, b) {
            y.fn["parent" + b] = function (b) {
                var c = 0 === a ? this : y(this.get().reverse());
                return z(c, b, a, "parentNode")
            }
        }), j(["append", "prepend"], function (a, c) {
            y.fn[c] = function (c) {
                var d, g, f = this.length > 1;
                return q(c) ? (g = b.createElement("div"), g.innerHTML = c, d = e.call(g.childNodes)) : d = y(c).get(), 1 === a && d.reverse(), this.each(function (b, c) {
                    j(d, function (d, e) {
                        f && b > 0 && (e = e.cloneNode(!0)), 0 === a ? c.appendChild(e) : c.insertBefore(e, c.childNodes[0])
                    })
                })
            }
        }), j(["insertBefore", "insertAfter"], function (a, b) {
            y.fn[b] = function (b) {
                var c = y(b);
                return this.each(function (b, d) {
                    c.each(function (b, e) {
                        e.parentNode.insertBefore(1 === c.length ? d : d.cloneNode(!0), 0 === a ? e : e.nextSibling)
                    })
                })
            }
        }), j({
            appendTo: "append",
            prependTo: "prepend",
            before: "insertBefore",
            after: "insertAfter",
            replaceAll: "replaceWith"
        }, function (a, b) {
            y.fn[a] = function (a) {
                return y(a)[b](this), this
            }
        }), function () {
            var a = "mduiElementDataStorage";
            y.extend({
                data: function (b, d, e) {
                    var g, h, f = {};
                    if (e !== c) f[d] = e; else {
                        if (!s(d)) return d === c ? (g = {}, j(b.attributes, function (a, b) {
                            var d, c = b.name;
                            0 === c.indexOf("data-") && (d = c.slice(5).replace(/-./g, function (a) {
                                return a.charAt(1).toUpperCase()
                            }), g[d] = b.value)
                        }), b[a] && j(b[a], function (a, b) {
                            g[a] = b
                        }), g) : b[a] && d in b[a] ? b[a][d] : (h = b.getAttribute("data-" + d), h ? h : c);
                        f = d
                    }
                    b[a] || (b[a] = {}), j(f, function (c, d) {
                        b[a][c] = d
                    })
                }, removeData: function (b, c) {
                    b[a] && b[a][c] && (b[a][c] = null, delete b.mduiElementDataStorage[c])
                }
            }), y.fn.extend({
                data: function (a, b) {
                    return b === c ? this[0] ? y.data(this[0], a) : c : this.each(function (c, d) {
                        y.data(d, a, b)
                    })
                }, removeData: function (a) {
                    return this.each(function (b, c) {
                        y.removeData(c, a)
                    })
                }
            })
        }(), function () {
            function f(b, d, e, f, g) {
                var j, i = h(b);
                a[i] || (a[i] = []), j = !1, s(f) && f.useCapture && (j = !0), d.split(" ").forEach(function (d) {
                    var h = {e: d, fn: e, sel: g, i: a[i].length}, k = function (a, b) {
                        var d = e.apply(b, a._data === c ? [a] : [a].concat(a._data));
                        d === !1 && (a.preventDefault(), a.stopPropagation())
                    }, l = h.proxy = function (a) {
                        a.data = f, g ? y(b).find(g).get().reverse().forEach(function (b) {
                            (b === a.target || y.contains(b, a.target)) && k(a, b)
                        }) : k(a, b)
                    };
                    a[i].push(h), b.addEventListener(h.e, l, j)
                })
            }

            function g(b, c, d, e) {
                (c || "").split(" ").forEach(function (c) {
                    i(b, c, d, e).forEach(function (c) {
                        delete a[h(b)][c.i], b.removeEventListener(c.e, c.proxy, !1)
                    })
                })
            }

            function h(a) {
                return a._elementId || (a._elementId = d++)
            }

            function i(b, c, d, e) {
                return (a[h(b)] || []).filter(function (a) {
                    return !(!a || c && a.e !== c || d && a.fn.toString() !== d.toString() || e && a.sel !== e)
                })
            }

            var a = {}, d = 1, e = function () {
                return !1
            };
            y.fn.extend({
                ready: function (a) {
                    return /complete|loaded|interactive/.test(b.readyState) && b.body ? a(y) : b.addEventListener("DOMContentLoaded", function () {
                        a(y)
                    }, !1), this
                }, on: function (a, b, d, g, h) {
                    var k, i = this;
                    return a && !q(a) ? (j(a, function (a, c) {
                        i.on(a, b, d, c)
                    }), i) : (q(b) || p(g) || g === !1 || (g = d, d = b, b = c), (p(d) || d === !1) && (g = d, d = c), g === !1 && (g = e), 1 === h && (k = g, g = function () {
                        return i.off(a, b, g), k.apply(this, arguments)
                    }), this.each(function () {
                        f(this, a, g, d, b)
                    }))
                }, one: function (a, b, c, d) {
                    var e = this;
                    return q(a) ? a.split(" ").forEach(function (a) {
                        e.on(a, b, c, d, 1)
                    }) : j(a, function (a, d) {
                        a.split(" ").forEach(function (a) {
                            e.on(a, b, c, d, 1)
                        })
                    }), this
                }, off: function (a, b, d) {
                    var f = this;
                    return a && !q(a) ? (j(a, function (a, c) {
                        f.off(a, b, c)
                    }), f) : (q(b) || p(d) || d === !1 || (d = b, b = c), d === !1 && (d = e), f.each(function () {
                        g(this, a, d, b)
                    }))
                }, trigger: function (a, c) {
                    if (q(a)) {
                        var d;
                        try {
                            d = new CustomEvent(a, {detail: c, bubbles: !0, cancelable: !0})
                        } catch (e) {
                            d = b.createEvent("Event"), d.initEvent(a, !0, !0), d.detail = c
                        }
                        return d._data = c, this.each(function () {
                            this.dispatchEvent(d)
                        })
                    }
                }
            })
        }(), y
    }(a, b), f = e("body"), g = e(b), h = e(a), i = {}, function () {
        var a = [];
        i.queue = function (b, d) {
            return a[b] === c && (a[b] = []), d === c ? a[b] : (a[b].push(d), void 0)
        }, i.dequeue = function (b) {
            a[b] !== c && a[b].length && a[b].shift()()
        }
    }(), j = {
        touches: 0,
        isAllow: function (a) {
            var b = !0;
            return j.touches && ["mousedown", "mouseup", "mousemove", "click", "mouseover", "mouseout", "mouseenter", "mouseleave"].indexOf(a.type) > -1 && (b = !1), b
        },
        register: function (a) {
            "touchstart" === a.type ? j.touches += 1 : ["touchmove", "touchend", "touchcancel"].indexOf(a.type) > -1 && setTimeout(function () {
                j.touches && (j.touches -= 1)
            }, 500)
        },
        start: "touchstart mousedown",
        move: "touchmove mousemove",
        end: "touchend mouseup",
        cancel: "touchcancel mouseleave",
        unlock: "touchend touchmove touchcancel"
    }, e(function () {
        setTimeout(function () {
            f.addClass("mdui-loaded")
        }, 0)
    }), k = function (a) {
        var c, b = {};
        if (null === a || !a) return b;
        if ("object" == typeof a) return a;
        c = a.indexOf("{");
        try {
            b = new Function("", "var json = " + a.substr(c) + "; return JSON.parse(JSON.stringify(json));")()
        } catch (d) {
        }
        return b
    }, l = function (a, b, c, d, f) {
        f || (f = {}), f.inst = c;
        var g = a + ".mdui." + b;
        "undefined" != typeof jQuery && jQuery(d).trigger(g, f), e(d).trigger(g, f)
    }, e.fn.extend({
        reflow: function () {
            return this.each(function () {
                return this.clientLeft
            })
        }, transition: function (a) {
            return "string" != typeof a && (a += "ms"), this.each(function () {
                this.style.webkitTransitionDuration = a, this.style.transitionDuration = a
            })
        }, transitionEnd: function (a) {
            function e(f) {
                if (f.target === this) for (a.call(this, f), c = 0; c < b.length; c++) d.off(b[c], e)
            }

            var c, b = ["webkitTransitionEnd", "transitionend"], d = this;
            if (a) for (c = 0; c < b.length; c++) d.on(b[c], e);
            return this
        }, transformOrigin: function (a) {
            return this.each(function () {
                this.style.webkitTransformOrigin = a, this.style.transformOrigin = a
            })
        }, transform: function (a) {
            return this.each(function () {
                this.style.webkitTransform = a, this.style.transform = a
            })
        }
    }), e.extend({
        showOverlay: function (a) {
            var d, b = e(".mdui-overlay");
            return b.length ? (b.data("isDeleted", 0), a !== c && b.css("z-index", a)) : (a === c && (a = 2e3), b = e('<div class="mdui-overlay">').appendTo(f).reflow().css("z-index", a)), d = b.data("overlay-level") || 0, b.data("overlay-level", ++d).addClass("mdui-overlay-show")
        }, hideOverlay: function (a) {
            var c, b = e(".mdui-overlay");
            if (b.length) return c = a ? 1 : b.data("overlay-level"), c > 1 ? (b.data("overlay-level", --c), void 0) : (b.data("overlay-level", 0).removeClass("mdui-overlay-show").data("isDeleted", 1).transitionEnd(function () {
                b.data("isDeleted") && b.remove()
            }), void 0)
        }, lockScreen: function () {
            var b, a = f.width();
            f.addClass("mdui-locked").width(a), b = f.data("lockscreen-level") || 0, f.data("lockscreen-level", ++b)
        }, unlockScreen: function (a) {
            var b = a ? 1 : f.data("lockscreen-level");
            return b > 1 ? (f.data("lockscreen-level", --b), void 0) : (f.data("lockscreen-level", 0).removeClass("mdui-locked").width(""), void 0)
        }, throttle: function (a, b) {
            var c = null;
            return (!b || 16 > b) && (b = 16), function () {
                var d = this, e = arguments;
                null === c && (c = setTimeout(function () {
                    a.apply(d, e), c = null
                }, b))
            }
        }, guid: function (a) {
            function b() {
                return Math.floor(65536 * (1 + Math.random())).toString(16).substring(1)
            }

            var c = b() + b() + "-" + b() + "-" + b() + "-" + b() + "-" + b() + b() + b();
            return a && (c = "mdui-" + a + "-" + c), c
        }
    }), d.Headroom = function () {
        function c(a, c) {
            var f, g, d = this;
            if (d.$headroom = e(a).eq(0), d.$headroom.length) {
                if (f = d.$headroom.data("mdui.headroom")) return f;
                d.options = e.extend({}, b, c || {}), g = d.options.tolerance, g !== Object(g) && (d.options.tolerance = {
                    down: g,
                    up: g
                }), d._init()
            }
        }

        var d, b = {
            tolerance: 5,
            offset: 0,
            initialClass: "mdui-headroom",
            pinnedClass: "mdui-headroom-pinned-top",
            unpinnedClass: "mdui-headroom-unpinned-top"
        };
        return c.prototype._init = function () {
            var a = this;
            a.state = "pinned", a.$headroom.addClass(a.options.initialClass).removeClass(a.options.pinnedClass + " " + a.options.unpinnedClass), a.inited = !1, a.lastScrollY = 0, a._attachEvent()
        }, c.prototype._scroll = function () {
            var b = this;
            b.rafId = a.requestAnimationFrame(function () {
                var c = a.pageYOffset, d = c > b.lastScrollY ? "down" : "up",
                    e = Math.abs(c - b.lastScrollY) >= b.options.tolerance[d];
                c > b.lastScrollY && c >= b.options.offset && e ? b.unpin() : (c < b.lastScrollY && e || c <= b.options.offset) && b.pin(), b.lastScrollY = c
            })
        }, d = function (a) {
            "pinning" === a.state && (a.state = "pinned", l("pinned", "headroom", a, a.$headroom)), "unpinning" === a.state && (a.state = "unpinned", l("unpinned", "headroom", a, a.$headroom))
        }, c.prototype.pin = function () {
            var a = this;
            "pinning" !== a.state && "pinned" !== a.state && a.$headroom.hasClass(a.options.initialClass) && (l("pin", "headroom", a, a.$headroom), a.state = "pinning", a.$headroom.removeClass(a.options.unpinnedClass).addClass(a.options.pinnedClass).transitionEnd(function () {
                d(a)
            }))
        }, c.prototype.unpin = function () {
            var a = this;
            "unpinning" !== a.state && "unpinned" !== a.state && a.$headroom.hasClass(a.options.initialClass) && (l("unpin", "headroom", a, a.$headroom), a.state = "unpinning", a.$headroom.removeClass(a.options.pinnedClass).addClass(a.options.unpinnedClass).transitionEnd(function () {
                d(a)
            }))
        }, c.prototype.enable = function () {
            var a = this;
            a.inited || a._init()
        }, c.prototype.disable = function () {
            var b = this;
            b.inited && (b.inited = !1, b.$headroom.removeClass([b.options.initialClass, b.options.pinnedClass, b.options.unpinnedClass].join(" ")), h.off("scroll", function () {
                b._scroll()
            }), a.cancelAnimationFrame(b.rafId))
        }, c.prototype.getState = function () {
            return this.state
        }, c
    }(), d.Drawer = function () {
        function c(c, d) {
            var g, f = this;
            if (f.$drawer = e(c).eq(0), f.$drawer.length) {
                if (g = f.$drawer.data("mdui.drawer")) return g;
                f.options = e.extend({}, a, d || {}), f.overlay = !1, f.position = f.$drawer.hasClass("mdui-drawer-right") ? "right" : "left", f.state = f.$drawer.hasClass("mdui-drawer-close") ? "closed" : f.$drawer.hasClass("mdui-drawer-open") ? "opened" : b() ? "opened" : "closed", h.on("resize", e.throttle(function () {
                    b() ? (f.overlay && !f.options.overlay && (e.hideOverlay(), f.overlay = !1, e.unlockScreen()), f.$drawer.hasClass("mdui-drawer-close") || (f.state = "opened")) : f.overlay || "opened" !== f.state || (f.$drawer.hasClass("mdui-drawer-open") ? (e.showOverlay(), f.overlay = !0, e.lockScreen(), e(".mdui-overlay").one("click", function () {
                        f.close()
                    })) : f.state = "closed")
                }, 100)), f.$drawer.find("[mdui-drawer-close]").each(function () {
                    e(this).on("click", function () {
                        f.close()
                    })
                })
            }
        }

        var a = {overlay: !1}, b = function () {
            return h.width() >= 1024
        }, d = function (a) {
            a.$drawer.hasClass("mdui-drawer-open") ? (a.state = "opened", l("opened", "drawer", a, a.$drawer)) : (a.state = "closed", l("closed", "drawer", a, a.$drawer))
        };
        return c.prototype.open = function () {
            var a = this;
            "opening" !== a.state && "opened" !== a.state && (a.state = "opening", l("open", "drawer", a, a.$drawer), a.options.overlay || f.addClass("mdui-drawer-body-" + a.position), a.$drawer.removeClass("mdui-drawer-close").addClass("mdui-drawer-open").transitionEnd(function () {
                d(a)
            }), (!b() || a.options.overlay) && (a.overlay = !0, e.showOverlay().one("click", function () {
                a.close()
            }), e.lockScreen()))
        }, c.prototype.close = function () {
            var a = this;
            "closing" !== a.state && "closed" !== a.state && (a.state = "closing", l("close", "drawer", a, a.$drawer), a.options.overlay || f.removeClass("mdui-drawer-body-" + a.position), a.$drawer.addClass("mdui-drawer-close").removeClass("mdui-drawer-open").transitionEnd(function () {
                d(a)
            }), a.overlay && (e.hideOverlay(), a.overlay = !1, e.unlockScreen()))
        }, c.prototype.toggle = function () {
            var a = this;
            "opening" === a.state || "opened" === a.state ? a.close() : ("closing" === a.state || "closed" === a.state) && a.open()
        }, c.prototype.getState = function () {
            return this.state
        }, c
    }(), e(function () {
        e("[mdui-drawer]").each(function () {
            var f, g, a = e(this), b = k(a.attr("mdui-drawer")), c = b.target;
            delete b.target, f = e(c).eq(0), g = f.data("mdui.drawer"), g || (g = new d.Drawer(f, b), f.data("mdui.drawer", g)), a.on("click", function () {
                g.toggle()
            })
        })
    }), function () {
        function l(a) {
            var d, g, c = this;
            c.options = e.extend({}, h, a || {}), c.options.message && (c.state = "closed", c.timeoutId = !1, d = "", g = "", 0 === c.options.buttonColor.indexOf("#") || 0 === c.options.buttonColor.indexOf("rgb") ? d = 'style="color:' + c.options.buttonColor + '"' : "" !== c.options.buttonColor && (g = "mdui-text-color-" + c.options.buttonColor), c.$snackbar = e('<div class="mdui-snackbar"><div class="mdui-snackbar-text">' + c.options.message + "</div>" + (c.options.buttonText ? '<a href="javascript:void(0)" class="mdui-snackbar-action mdui-btn mdui-ripple mdui-ripple-white ' + g + '" ' + d + ">" + c.options.buttonText + "</a>" : "") + "</div>").appendTo(f), c.$snackbar.transform("translateY(" + c.$snackbar[0].clientHeight + "px)").css("left", (b.body.clientWidth - c.$snackbar[0].clientWidth) / 2 + "px").addClass("mdui-snackbar-transition"))
        }

        var a, c = "__md_snackbar", h = {
            message: "",
            timeout: 4e3,
            buttonText: "",
            buttonColor: "",
            closeOnButtonClick: !0,
            closeOnOutsideClick: !0,
            onClick: function () {
            },
            onButtonClick: function () {
            },
            onClose: function () {
            }
        }, k = function (b) {
            var c = e(b.target);
            c.hasClass("mdui-snackbar") || c.parents(".mdui-snackbar").length || a.close()
        };
        l.prototype.open = function () {
            var b = this;
            if ("opening" !== b.state && "opened" !== b.state) {
                if (a) return i.queue(c, function () {
                    b.open()
                }), void 0;
                a = b, b.state = "opening", b.$snackbar.transform("translateY(0)").transitionEnd(function () {
                    "opening" === b.state && (b.state = "opened", b.options.buttonText && b.$snackbar.find(".mdui-snackbar-action").on("click", function () {
                        b.options.onButtonClick(), b.options.closeOnButtonClick && b.close()
                    }), b.$snackbar.on("click", function (a) {
                        e(a.target).hasClass("mdui-snackbar-action") || b.options.onClick()
                    }), b.options.closeOnOutsideClick && g.on(j.start, k), b.timeoutId = setTimeout(function () {
                        b.close()
                    }, b.options.timeout))
                })
            }
        }, d.snackbar = function (a) {
            var b = new l(a);
            return b.open(), b
        }
    }(), d.JQ = e, a.mdui = d
}(window, document);