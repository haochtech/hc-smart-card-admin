!function(){var a={textarea:function(a,b){var c=b.ownerDocument.createElement("textarea");return c.style.cssText="resize:none;border:0;padding:0;margin:0;overflow-y:auto;outline:0",browser.ie&&browser.version<8&&(c.style.width=b.offsetWidth+"px",c.style.height=b.offsetHeight+"px",b.onresize=function(){c.style.width=b.offsetWidth+"px",c.style.height=b.offsetHeight+"px"}),b.appendChild(c),{container:c,setContent:function(a){c.value=a},getContent:function(){return c.value},select:function(){var a;browser.ie?(a=c.createTextRange(),a.collapse(!0),a.select()):(c.setSelectionRange(0,0),c.focus())},dispose:function(){b.removeChild(c),b.onresize=null,c=null,b=null}}}};UM.plugins["source"]=function(){function f(c){return a.textarea(b,c)}var e,i,h,j,b=this,c=this.options,d=!1;c.sourceEditor="textarea",b.setOpt({sourceEditorFirst:!1}),h=b.getContent,b.commands["source"]={execCommand:function(){var a,c,g,j,k;if(d=!d)i=b.selection.getRange().createAddress(!1,!0),b.undoManger&&b.undoManger.save(!0),browser.gecko&&(b.body.contentEditable=!1),b.body.style.cssText+=";position:absolute;left:-32768px;top:-32768px;",b.fireEvent("beforegetcontent"),a=UM.htmlparser(b.body.innerHTML),b.filterOutputRule(a),a.traversal(function(a){if("element"==a.type)switch(a.tagName){case"td":case"th":case"caption":a.children&&1==a.children.length&&"br"==a.firstChild().tagName&&a.removeChild(a.firstChild());break;case"pre":a.innerText(a.innerText().replace(/&nbsp;/g," "))}}),b.fireEvent("aftergetcontent"),c=a.toHtml(!0),e=f(b.body.parentNode),e.setContent(c),g=function(a){return parseInt($(b.body).css(a))},$(e.container).width($(b.body).width()+g("padding-left")+g("padding-right")).height($(b.body).height()),setTimeout(function(){e.select()}),b.getContent=function(){return e.getContent()||"<p>"+(browser.ie?"":"<br/>")+"</p>"};else{b.$body.css({position:"",left:"",top:""}),j=e.getContent()||"<p>"+(browser.ie?"":"<br/>")+"</p>",j=j.replace(new RegExp("[\\r\\t\\n ]*</?(\\w+)\\s*(?:[^>]*)>","g"),function(a,b){return b&&!dtd.$inlineWithA[b.toLowerCase()]?a.replace(/(^[\n\r\t ]*)|([\n\r\t ]*$)/g,""):a.replace(/(^[\n\r\t]*)|([\n\r\t]*$)/g,"")}),b.setContent(j),e.dispose(),e=null,b.getContent=h,k=b.body.firstChild,k||(b.body.innerHTML="<p>"+(browser.ie?"":"<br/>")+"</p>"),b.undoManger&&b.undoManger.save(!0),browser.gecko&&(b.body.contentEditable=!0);try{b.selection.getRange().moveToAddress(i).select()}catch(l){}}this.fireEvent("sourcemodechanged",d)},queryCommandState:function(){return 0|d},notNeedUndo:1},j=b.queryCommandState,b.queryCommandState=function(a){return a=a.toLowerCase(),d?a in{source:1,fullscreen:1}?j.apply(this,arguments):-1:j.apply(this,arguments)}}}();