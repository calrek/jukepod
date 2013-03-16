Ext.namespace('Ext.ux.layout.flexAccord');Ext.ux.layout.flexAccord.DropPanel=Ext.extend(Ext.Panel,{initComponent:function()
{Ext.apply(this,{layout:new Ext.ux.layout.flexAccord.Layout(this.layoutConfig||{})});this.addEvents('validatedrop','beforedragover','dragover','beforedrop','drop');Ext.ux.layout.flexAccord.DropPanel.superclass.initComponent.call(this);},initEvents:function()
{Ext.ux.layout.flexAccord.DropPanel.superclass.initEvents.call(this);this.dd=new Ext.ux.layout.flexAccord.DropTarget(this,this.dropConfig);},beforeDestroy:function()
{if(this.dd){this.dd.unreg();}
Ext.ux.layout.flexAccord.DropPanel.superclass.beforeDestroy.call(this);}});