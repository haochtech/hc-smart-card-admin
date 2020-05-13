"use strict";
module.exports = function(a) {
    function b() {
        var b = "umeditor.config.js",
            c = a.file.read(b),
            d = f + "/",
            e = "net" === f ? ".ashx" : "." + f;
        c = c.replace(/php\//gi, d).replace(/\.php/gi, e), a.file.write(h + b, c) ? a.log.writeln("config file update success") : a.log.warn("config file update error")
    }
    var c = require("fs"),
        d = {
            jsBasePath: "_src/",
            cssBasePath: "themes/default/_css/",
            fetchScripts: function() {
                var a = c.readFileSync("_examples/editor_api.js");
                return a = /\[([^\]]+\.js'[^\]]+)\]/.exec(a), a = a[1].replace(/\/\/.*[\n\r]/g, "\n").replace(/'|"|\n|\t|\s/g, ""), a = a.split(","), a.forEach(function(b, c) {
                    a[c] = d.jsBasePath + b
                }), a
            },
            fetchStyles: function() {
                for (var a = c.readFileSync(this.cssBasePath + "umeditor.css"), b = null, d = /@import\s+([^;]+)*;/g, e = []; b = d.exec(a);) e.push(this.cssBasePath + b[1].replace(/'|"/g, ""));
                return e
            }
        },
        e = a.file.readJSON("package.json"),
        f = a.option("server") || "php",
        g = a.option("encode") || "utf8",
        h = "dist/",
        i = h,
        j = "/*!\n * UEditor Mini版本\n * version: <%= pkg.version %>\n * build: <%= new Date() %>\n */\n\n";
    !
        function() {
            f = "string" == typeof f ? f.toLowerCase() : "php", g = "string" == typeof g ? g.toLowerCase() : "utf8", h = h + g + "-" + f + "/", i = i + e.name + e.version.replace(/\./g, "_") + "-" + g + "-" + f + ".zip"
        }(), a.initConfig({
        pkg: e,
        concat: {
            js: {
                options: {
                    banner: j + "(function($){\n\n",
                    footer: "\n\n})(jQuery)"
                },
                src: d.fetchScripts(),
                dest: h + "<%= pkg.name %>.js"
            },
            css: {
                src: d.fetchStyles(),
                dest: h + "themes/default/css/umeditor.css"
            }
        },
        cssmin: {
            options: {
                banner: j
            },
            files: {
                expand: !0,
                cwd: h + "themes/default/css/",
                src: ["*.css", "!*.min.css"],
                dest: h + "themes/default/css/",
                ext: ".min.css"
            }
        },
        closurecompiler: {
            dist: {
                src: h + "<%= pkg.name %>.js",
                dest: h + "<%= pkg.name %>.min.js"
            }
        },
        copy: {
            base: {
                files: [{
                    src: ["themes/default/images/**", "dialogs/**", "third-party/**", "lang/**"],
                    dest: h
                }]
            },
            demo: {
                files: [{
                    src: "_examples/completeDemo.html",
                    dest: h + "index.html"
                }]
            },
            php: {
                expand: !0,
                src: "php/**",
                dest: h
            },
            asp: {
                expand: !0,
                src: "asp/**",
                dest: h
            },
            jsp: {
                expand: !0,
                src: "jsp/**",
                dest: h
            },
            net: {
                expand: !0,
                src: "net/**",
                dest: h
            }
        },
        transcoding: {
            options: {
                charset: g
            },
            src: [h + "*.js", h + "dialogs/*.js", h + "lang/*.js", h + "**/*.js", h + "**/*.js", h + "**/*.js", h + "**/*.js", h + "**/*.html", h + "**/*.css", h + "**/*.jsp", h + "**/*.java", h + "**/*.php", h + "**/*.asp", h + "**/*.ashx", h + "**/*.cs"]
        },
        replace: {
            fileEncode: {
                src: [h + "**/*.html", h + "**/*.css", h + "**/*.php", h + "**/*.jsp", h + "**/*.net", h + "**/*.asp"],
                overwrite: !0,
                replacements: [{
                    from: /utf-8/gi,
                    to: "gbk"
                }]
            },
            demo: {
                src: h + "index.html",
                overwrite: !0,
                replacements: [{
                    from: /\.\.\//gi,
                    to: ""
                }, {
                    from: "editor_api.js",
                    to: "<%= pkg.name %>.min.js"
                }, {
                    from: "_css",
                    to: "css"
                }]
            },
            gbkasp: {
                src: [h + "asp/*.asp"],
                overwrite: !0,
                replacements: [{
                    from: /65001/gi,
                    to: "936"
                }]
            }
        },
        compress: {
            main: {
                options: {
                    archive: i
                },
                expand: !0,
                cwd: h,
                src: ["**/*"]
            }
        }
    }), a.loadNpmTasks("grunt-text-replace"), a.loadNpmTasks("grunt-contrib-concat"), a.loadNpmTasks("grunt-contrib-cssmin"), a.loadNpmTasks("grunt-closurecompiler"), a.loadNpmTasks("grunt-contrib-copy"), a.loadNpmTasks("grunt-transcoding"), a.loadNpmTasks("grunt-contrib-compress"), a.registerTask("default", "UEditor Mini build", function() {
        var c = ["concat", "cssmin", "closurecompiler", "copy:base", "copy:" + f, "copy:demo", "replace:demo"];
        "gbk" === g && (c.push("replace:fileEncode"), "asp" === f && c.push("replace:gbkasp")), c.push("transcoding"), b(), a.task.run(c)
    })
};