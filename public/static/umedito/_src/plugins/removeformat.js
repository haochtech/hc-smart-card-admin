UM.plugins["removeformat"]=function(){var a=this;a.setOpt({removeFormatTags:"b,big,code,del,dfn,em,font,i,ins,kbd,q,samp,small,span,strike,strong,sub,sup,tt,u,var",removeFormatAttributes:"class,style,lang,width,height,align,hspace,valign"}),a.commands["removeformat"]={execCommand:function(a,b,c,d,e){function m(a){var b,c,d;if(3==a.nodeType||"span"!=a.tagName.toLowerCase())return 0;if(browser.ie&&(b=a.attributes,b.length)){for(c=0,d=b.length;d>c;c++)if(b[c].specified)return 0;return 1}return!a.attributes.length}function n(a){var d,j,h,n,p,o,q,b=a.createBookmark();for(a.collapsed&&a.enlarge(!0),e||(d=domUtils.findParentByTagName(a.startContainer,"a",!0),d&&a.setStartBefore(d),d=domUtils.findParentByTagName(a.endContainer,"a",!0),d&&a.setEndAfter(d)),i=a.createBookmark(),o=i.start;(k=o.parentNode)&&!domUtils.isBlockElm(k);)domUtils.breakParent(o,k),domUtils.clearEmptySibling(o);if(i.end){for(o=i.end;(k=o.parentNode)&&!domUtils.isBlockElm(k);)domUtils.breakParent(o,k),domUtils.clearEmptySibling(o);for(h=domUtils.getNextDomNode(i.start,!1,l);h&&h!=i.end;)j=domUtils.getNextDomNode(h,!0,l),dtd.$empty[h.tagName.toLowerCase()]||domUtils.isBookmarkNode(h)||(f.test(h.tagName)?c?(domUtils.removeStyle(h,c),m(h)&&"text-decoration"!=c&&domUtils.remove(h,!0)):domUtils.remove(h,!0):dtd.$tableContent[h.tagName]||dtd.$list[h.tagName]||(domUtils.removeAttributes(h,g),m(h)&&domUtils.remove(h,!0))),h=j}for(n=i.start.parentNode,!domUtils.isBlockElm(n)||dtd.$tableContent[n.tagName]||dtd.$list[n.tagName]||domUtils.removeAttributes(n,g),n=i.end.parentNode,i.end&&domUtils.isBlockElm(n)&&!dtd.$tableContent[n.tagName]&&!dtd.$list[n.tagName]&&domUtils.removeAttributes(n,g),a.moveToBookmark(i).moveToBookmark(b),o=a.startContainer,q=a.collapsed;1==o.nodeType&&domUtils.isEmptyNode(o)&&dtd.$removeEmpty[o.tagName];)p=o.parentNode,a.setStartBefore(o),a.startContainer===a.endContainer&&a.endOffset--,domUtils.remove(o),o=p;if(!q)for(o=a.endContainer;1==o.nodeType&&domUtils.isEmptyNode(o)&&dtd.$removeEmpty[o.tagName];)p=o.parentNode,a.setEndBefore(o),domUtils.remove(o),o=p}var i,k,f=new RegExp("^(?:"+(b||this.options.removeFormatTags).replace(/,/g,"|")+")$","i"),g=c?[]:(d||this.options.removeFormatAttributes).split(","),h=new dom.Range(this.document),l=function(a){return 1==a.nodeType};h=this.selection.getRange(),h.collapsed||(n(h),h.select())}}};