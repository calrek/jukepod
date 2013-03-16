Ext.namespace('Ext.ux.layout.flexAccord');Ext.ux.layout.flexAccord.SplitBar=function(dragElement,resizingElement,orientation,placement,existingProxy){this.resizingComponent=resizingElement;Ext.ux.layout.flexAccord.SplitBar.superclass.constructor.call(this,dragElement,resizingElement.el,orientation,placement,existingProxy);this.adapter.setElementSize=function(s,newSize,onComplete)
{var resizedElement=s.resizingComponent;resizedElement.ownerCt.getLayout().setItemHeight(resizedElement,newSize,true);};};Ext.extend(Ext.ux.layout.flexAccord.SplitBar,Ext.SplitBar,{getMinimumSize:function()
{var item=this.resizingComponent;var items=item.ownerCt.items.items;var sibl=null;var layout=item.ownerCt.getLayout()
var itemPos=items.indexOf(item);if(itemPos==items.length-2&&items[items.length-1].resizable===false){if(!items[items.length-1].collapsed){return item.getSize().height;}else{return item.getSize().height-
(layout._orgHeights[items[items.length-1].id]-layout.getHeaderHeight(item));}}
return this.resizingComponent.getSize().height-
this.resizingComponent.bwrap.getHeight();},getMaximumSize:function()
{var items=this.resizingComponent.ownerCt.items.items;var sibl=null;var item=this.resizingComponent;var innerHeight=item.ownerCt.getInnerHeight();var layout=item.ownerCt.getLayout();var itemPos=items.indexOf(item);if(item.resizable!==false&&itemPos!=items.length-2){var pH=0;for(var i=0,len=items.length;i<len;i++){if(i>itemPos){pH+=layout.getHeaderHeight(items[i]);}else if(i!=itemPos){pH+=items[i].height;}}
return(innerHeight-pH);}
if(item.resizable===false){return item.ownerCt.getLayout()._orgHeights[item.id];}
for(var i=0,len=items.length;i<len;i++){if(items[i]==item&&items[i+1]){sibl=items[i+1];return item.getSize().height+
sibl.getSize().height-
(item.getSize().height-item.bwrap.getHeight());}}}});