Ext.namespace('Ext.ux.layout.flexAccord');Ext.ux.layout.flexAccord.Layout=Ext.extend(Ext.layout.ContainerLayout,{defaultHeight:100,titleCollapse:true,hideCollapseTool:false,animate:false,monitorResize:true,_orgHeights:null,renderItem:function(c,position,target)
{if(this.animate===false){c.animCollapse=false;}
c.collapsible=true;c.autoWidth=true;if(this.titleCollapse){c.titleCollapse=true;}
if(this.hideCollapseTool){c.hideCollapseTool=true;}
var initDDOverride=!c.rendered;Ext.ux.layout.flexAccord.Layout.superclass.renderItem.call(this,c,position,target);if(initDDOverride&&c.dd){c.dd.b4StartDrag=c.dd.b4StartDrag.createInterceptor(function(){if(!this.panel.collapsed){this.panel._wasExpanded=true;this.panel.ownerCt.getLayout().collapse(this.panel,true);}},c.dd);}
if(!this._orgHeights){this._orgHeights={};}
if(c.height===undefined||c.height=='auto'){c.height=this.defaultHeight;}
if(c.resizable===false&&!this._orgHeights[c.getId()]){this._orgHeights[c.getId()]=c.height;}
if(c.collapsed){c.height=this.getHeaderHeight(c);}
c.header.addClass('x-accordion-hd');c.un('beforeexpand',this.beforeExpand,this);c.un('collapse',this.onCollapse,this);c.on('beforeexpand',this.beforeExpand,this);c.on('collapse',this.onCollapse,this);},deleteSplitter:function(c)
{if(c.splitter){c.splitter.destroy(true);c.splitter=null;delete c.splitter;delete c.splitEl;}},addSplitter:function(c)
{if(c.splitter)return;c.splitEl=c.el.createChild({cls:'ext-ux-flexaccord-splitter x-layout-split x-layout-split-south',html:"&#160;",id:c.getId()+'-xsplit'});c.splitter=new Ext.ux.layout.flexAccord.SplitBar(c.splitEl.dom,c,Ext.SplitBar.TOP);},unregisterPanel:function(panel,newContainer)
{panel.un('collapse',this.onCollapse,this);panel.un('beforeexpand',this.beforeExpand,this);this.deleteSplitter(panel);if(this._orgHeights[panel.getId()]){var newLayout=newContainer.getLayout();if(!newLayout._orgHeights){newLayout._orgHeights={};}
newLayout._orgHeights[panel.getId()]=this._orgHeights[panel.getId()];delete this._orgHeights[panel.getId()];}},collapse:function(p,suspend)
{if(!p.rendered){return;}
if(suspend===true){p.un('collapse',this.onCollapse,this);}
p.collapse(false);if(suspend===true){p.on('collapse',this.onCollapse,this);}},expand:function(p,ignoreListener)
{if(ignoreListener!==false){p.un('beforeexpand',this.beforeExpand,this);}
p.expand(false);if(ignoreListener!==false){p.on('beforeexpand',this.beforeExpand,this);}},manageSplitbars:function()
{var items=this.container.items.items;var len=items.length;if(len==0){return;}
this.deleteSplitter(items[len-1]);for(var i=0;i<len-1;i++){this.addSplitter(items[i]);if(items[i+1]&&items[i+1].dd&&items[i+1].dd.proxy&&items[i+1].dd.proxy.getProxy()){this.deleteSplitter(items[i]);}}},onLayout:function(ct,target)
{Ext.ux.layout.flexAccord.Layout.superclass.onLayout.call(this,ct,target);var width=target.getStyleSize().width;var items=ct.items.items;this.manageSplitbars();for(var i=0,len=items.length;i<len;i++){if(items[i].splitEl){items[i].splitEl.setWidth(items[i].el.getWidth());}}
this.rendered=true;},getHeaderHeight:function(panel,toolbars)
{return panel.getSize().height
+(toolbars===true&&panel.getBottomToolbar()?panel.getBottomToolbar().getSize().height:0)
+(toolbars===true&&panel.getTopToolbar()?panel.getTopToolbar().getSize().height:0)
-panel.bwrap.getHeight();},adjustHeight:function(exclude)
{var width=this.container.el.getStyleSize().width;if(!Ext.isArray(exclude)){exclude=[];}
var items=this.container.items;var innerHeight=this.container.getInnerHeight();var panelHeights=0;var firstSpillItem=null;for(var i=0,len=items.items.length;i<len;i++){var item=items.get(i);if(item.hidden){continue;}
if(!firstSpillItem&&this._isResizable(item,true)){if(exclude.indexOf(item)==-1){firstSpillItem=item;}}
if(item.height<=this.getHeaderHeight(item)+3){this.collapse(item,true);}else if(item.collapsed){this.expand(item);}
item.setSize({height:item.height,width:width});panelHeights+=item.height;}
if(panelHeights<innerHeight&&firstSpillItem){firstSpillItem.height=firstSpillItem.getSize().height+(innerHeight-panelHeights);firstSpillItem.setHeight(firstSpillItem.height);}else if(panelHeights>innerHeight&&firstSpillItem){var rem=firstSpillItem.getSize().height-(panelHeights-innerHeight);var hh=this.getHeaderHeight(firstSpillItem);firstSpillItem.height=Math.max(hh,rem);firstSpillItem.setHeight(firstSpillItem.height);if(rem<=hh){this.adjustHeight(exclude.concat(firstSpillItem));}}},onResize:function()
{if(!this.rendered){return;}
Ext.ux.layout.flexAccord.Layout.superclass.onResize.call(this);var items=this.container.items;var resizables=[];var notResizables=[];var panelsHeight=0;var innerHeight=this.container.getInnerHeight();for(var i=0,len=items.length;i<len;i++){if(this._isResizable(items.get(i))){resizables.push(items.get(i));}else{notResizables.push(items.get(i));}
panelsHeight+=items.get(i).height;}
var remaining=this.container.getInnerHeight()-panelsHeight;if(remaining<0){var sortFunc=function(a,b){return a.height-b.height;};resizables.sort(sortFunc);notResizables.sort(sortFunc);resizables.reverse();var spill=remaining;if(resizables.length>0){spill=Math.floor(spill/resizables.length);}else if(notResizables.length>0){spill=Math.floor(spill/notResizables.length);}
for(var i=0,len=resizables.length;i<len&&spill!=0&&panelsHeight>innerHeight;i++){spill=this.addSpill(resizables[i],spill);panelsHeight-=spill;}
for(var i=0,len=notResizables.length;i<len&&spill!=0&&panelsHeight>innerHeight;i++){spill=this.addSpill(notResizables[i],spill);panelsHeight-=spill;}
this.adjustHeight(notResizables.concat(resizables));}else{this.adjustHeight();}},_isResizable:function(item,ignoreCollapse,ignoreResizable)
{if(!item.rendered||(ignoreResizable!==true&&item.resizable===false)){return false;}
itemId=item.getId();itemHeight=item.getSize().height;if(itemHeight<=this.getHeaderHeight(item)&&ignoreCollapse!==true){return false;}
return true;},beforeExpand:function(p,anim)
{var heightToSet=this._orgHeights[p.id]?this._orgHeights[p.id]:this.defaultHeight;var items=this.container.items;var item=null;var panelHeights=null;for(var i=0,len=items.length;i<len;i++){item=items.get(i);panelHeights+=items.get(i).getSize().height;}
if(panelHeights<this.container.getInnerHeight()){heightToSet=this.container.getInnerHeight()-
(panelHeights-this.getHeaderHeight(p));if(this._orgHeights[p.id]&&heightToSet>this._orgHeights[p.id]){p.height=this._orgHeights[p.id];this.adjustHeight(items.items);return;}else if(this._orgHeights[p.id]){heightToSet=this._orgHeights[p.id];}}
this.setItemHeight(p,heightToSet);},onCollapse:function(p,anim)
{var items=this.container.items;var itemPos=items.indexOf(p);var item=null;var panelHeights=0;var tmpItem=null;for(var i=0,len=items.length;i<len;i++){tmpItem=items.get(i);if(!item&&this._isResizable(tmpItem)){item=tmpItem;}
panelHeights+=tmpItem.getSize().height;}
if(item){item.height=item.height+(this.container.getInnerHeight()-panelHeights);item.setHeight(item.height);}
p.height=this.getHeaderHeight(p);},setItemHeight:function(resizedElement,newSize,considerAll)
{if(newSize<=0){return;}
var container=this.container;var items=container.items;var spill=0;var itemPos=items.indexOf(resizedElement);var innerHeight=container.getInnerHeight();spill=newSize-resizedElement.height;var direction=spill>0?'down':'up';spill=this.addSpill(resizedElement,spill);var panelHeights=resizedElement.height;var after=[];var ordered=[];var notResizables=[];for(var i=itemPos+1,len=items.items.length;i<len;i++){if(items.get(i).resizable===false){notResizables.push(items.get(i));}else{after.push(items.get(i));}}
if(items.get(itemPos+1)&&items.get(itemPos+1).resizable===false){notResizables.reverse();}
ordered=after.concat(notResizables);for(var i=0,len=ordered.length;i<len&&spill!=0;i++){spill=this.addSpill(ordered[i],spill);}
this.adjustHeight();},addSpill:function(panel,spill)
{var tHeight=panel.resizable===false?(spill>0?this._orgHeights[panel.id]:this.getHeaderHeight(panel)):(panel.height+spill)<=this.getHeaderHeight(panel)+2?this.getHeaderHeight(panel):(panel.height+spill);var retSpill=0;panel.height=tHeight<=0?this.getHeaderHeight(panel):tHeight;var panelHeights=0;for(var i=0,len=this.container.items.items.length;i<len;i++){panelHeights+=this.container.items.get(i).height;}
return this.container.getInnerHeight()-panelHeights;},});