Ext.form.LabelField = function(config){
    Ext.form.LabelField.superclass.constructor.call(this, config);
};

Ext.extend(Ext.form.LabelField, Ext.form.Field,  {
    defaultAutoCreate : {tag: "div"},
    fieldClass: 'x-form-label',
    value: '',
    setValue:function(val) { 
	    if(this.rendered){
	         this.el.update(val);
	    }
    }
});

Ext.override(Ext.tree.TreeNodeUI, {
focus : function()
{
	if(!this.node.preventHScroll)
	{
		try
		{
			this.anchor.focus();
		}
		catch(e){}
	}
	else if(!Ext.isIE)
	{
		try
		{
			var noscroll = this.node.getOwnerTree().getTreeEl().dom;
			var l = noscroll.scrollLeft;
			if(this.anchor) this.anchor.focus();
			noscroll.scrollLeft = l;
		}
		catch(e){}
	}
}
});

Ext.override(Ext.form.Checkbox, {
	getResizeEl : function(){
	if(!this.resizeEl){
	this.resizeEl = Ext.isSafari||Ext.isIE ? this.wrap : (this.wrap.up('.x-form-element', 5) || this.wrap);
	}
	return this.resizeEl;
	}
	});

Ext.override(Ext.grid.GridView, {
	getCell : function(row, col){
				if(this.getRow(row))
					return this.getRow(row).getElementsByTagName('td')[col];
				else
					return false;
    }
	});

// Fix für falsches plus/minus bei eingeklappten Pael
/*Ext.override(Ext.layout.BorderLayout.Region, {
	slideOut : function(){
		if(this.isSlid || this.el.hasActiveFx()){
			return;
		}
		this.isSlid = true;
		var ts = this.panel.tools;
		if(ts && ts.toggle){
			ts.toggle.hide();
		}
		this.el.show();
		if(this.position == 'east' || this.position == 'west'){
			this.panel.setSize(undefined, this.collapsedEl.getHeight());
		}else{
			this.panel.setSize(this.collapsedEl.getWidth(), undefined);
		}
		this.restoreLT = [this.el.dom.style.left, this.el.dom.style.top];
		this.el.alignTo(this.collapsedEl, this.getCollapseAnchor());
		this.el.setStyle("z-index", 102);
		this.panel.el.replaceClass('x-panel-collapsed', 'x-panel-floating');
		if(this.animFloat !== false){
			this.beforeSlide();
			this.el.slideIn(this.getSlideAnchor(), {
				callback: function(){
					this.afterSlide();
					this.initAutoHide();
					Ext.getDoc().on("click", this.slideInIf, this);
				},
				scope: this,
				block: true
			});
		}else{
			this.initAutoHide();
			 Ext.getDoc().on("click", this.slideInIf, this);
		}
	},

	afterSlideIn : function(){
		this.clearAutoHide();
		this.isSlid = false;
		this.clearMonitor();
		this.el.setStyle("z-index", "");
		this.panel.el.replaceClass('x-panel-floating', 'x-panel-collapsed');
		this.el.dom.style.left = this.restoreLT[0];
		this.el.dom.style.top = this.restoreLT[1];

		var ts = this.panel.tools;
		if(ts && ts.toggle){
			ts.toggle.show();
		}
	}
});*/

Ext.override(Ext.Element, {
	findParent : function(simpleSelector, maxDepth, returnEl){
		var p = this.dom, b = document.body, depth = 0, dq = Ext.DomQuery, stopEl;
		maxDepth = maxDepth || 50;
		if(typeof maxDepth != "number"){
			try {
				stopEl = Ext.getDom(maxDepth);
				maxDepth = 10;
			}
			catch(e) {};
		}
		try {
			while(p && p.nodeType && p.nodeType == 1 && depth < maxDepth && p != b && p != stopEl){
				if(dq.is(p, simpleSelector)){
					return returnEl ? Ext.get(p) : p;
				}
				depth++;
				p = p.parentNode;
			}
		} catch(e) {};
		return null;
	}
});

Ext.override(Ext.form.ComboBox, {
		doQuery : function(q, forceAll){
				if(q === undefined || q === null){
						q = '';
				}
				var qe = {
						query: q,
						forceAll: forceAll,
						combo: this,
						cancel:false
				};
				if(this.fireEvent('beforequery', qe)===false || qe.cancel){
						return false;
				}
				q = qe.query;
				forceAll = qe.forceAll;
				if(forceAll === true || (q.length >= this.minChars)){
						if(this.lastQuery !== q){
								this.lastQuery = q;
								if(this.mode == 'local'){
										this.selectedIndex = -1;
										if(forceAll){
												this.store.clearFilter();
										}else{
								this.anyMatch = this.anyMatch === undefined? false : this.anyMatch;
								this.caseSensitive = this.caseSensitive === undefined? false : this.caseSensitive;
												this.store.filter(this.displayField, q, this.anyMatch, this.caseSensitive);
										}
										this.onLoad();
								}else{
										this.store.baseParams[this.queryParam] = q;
										this.store.load({
												params: this.getParams(q)
										});
										this.expand();
								}
						}else{
								this.selectedIndex = -1;
								this.onLoad();
						}
				}
		}
}); 

Ext.override(Ext.Element,{
         mask : function(msg, msgCls){

              if(this.getStyle("position") == "static"){
                  if(Ext.isIE) this.addClass("x-masked-relative");
              }

             this._mask ||
                 (this._mask = Ext.DomHelper.append(this.dom, {cls:"ext-el-mask"}, true));

             if(!this.select('iframe,frame,object,embed').elements.length){
                 this.addClass("x-masked");  //causes element re-init after reflow (overflow:hidden)
             }

             //may have been hidden previously (and not removed)
             this._mask.setDisplayed(true)//.removeClass("x-hide-offsets");

             if(typeof msg == 'string'){
                  this._maskMsg || (this._maskMsg = Ext.DomHelper.append(this.dom, {style:"visibility:hidden",cls:"ext-el-mask-msg", cn:{tag:'div'}}, true));
                  var mm = this._maskMsg;
                  mm.dom.className = msgCls ? "ext-el-mask-msg " + msgCls : "ext-el-mask-msg";
                  mm.dom.firstChild.innerHTML = msg;
                  mm.center(this).setVisible(true);
             }

             //Adjust Mask Height for IE strict
             if(Ext.isIE && !(Ext.isIE7 && Ext.isStrict) && this.getStyle('height') == 'auto'){ // ie will not expand full height automatically
                  //see: http://www.extjs.com/forum/showthread.php?p=252925#post252925
                  this._mask.setHeight(this.getHeight());
             }

             return this._mask;
         },

         /**
          * Removes a previously applied mask.
          */
         unmask : function(remove){

            if(this._maskMsg ){

                this._maskMsg.setVisible(false);
                if(remove){
                    this._maskMsg.remove(true);
                    delete this._maskMsg;
                 }
            }

            if(this._mask ) {

                 this._mask.setDisplayed(false);
                 if(remove){
                     this._mask.remove(true);
                     delete this._mask;
                 }
             }

             this.removeClass(["x-masked", "x-masked-relative"]);

         }
})

Ext.override(Ext.grid.RowSelectionModel, {
	selectRow : function(index, keepExisting, preventViewNotify){
		if(this.isLocked() || (index < 0 || index >= this.grid.store.getCount()) ||
			(keepExisting && this.isSelected(index))) return;
		var r = this.grid.store.getAt(index);
		if(r && this.fireEvent("beforerowselect", this, index, keepExisting, r) !== false){
			if(!keepExisting || this.singleSelect){
				this.clearSelections();
			}
			this.selections.add(r);
			this.last = this.lastActive = index;
			if(!preventViewNotify){
				this.grid.getView().onRowSelect(index);
			}
			this.fireEvent("rowselect", this, index, r);
			this.fireEvent("selectionchange", this);
		}
	}
});	

Ext.ns('Ext.ux.grid');
Ext.ux.grid.AutoSizeColumns = function(config) {
	Ext.apply(this, config);
};
Ext.extend(Ext.ux.grid.AutoSizeColumns, Object, {
	cellPadding: 8,
	init: function(grid) {
		grid.getView().onHeaderClick = this.onHeaderClick;
		grid.on('headerdblclick', function(grid, colIndex, e) {
			var h = grid.getView().getHeaderCell(colIndex);
			if(h.style.cursor != 'col-resize'){
				return;
			}
			var xy = Ext.lib.Dom.getXY(h);
			if(e.getXY()[0] - xy[0] <= 5){
				colIndex--;
				h = grid.getView().getHeaderCell(colIndex);
			}
			if(grid.getColumnModel().isFixed(colIndex) || grid.getColumnModel().isHidden(colIndex)){
				return;
			}
			var hi = h.firstChild;
			hi.style.width = '0px';
			var w = hi.scrollWidth;
			hi.style.width = 'auto';
			for (var r = 0, len = grid.getStore().getCount(); r < len; r++) {
				var ci = grid.getView().getCell(r, colIndex).firstChild;
				ci.style.width = '0px';
				w = Math.max(w, ci.scrollWidth);
				ci.style.width = 'auto';
			}
			w += this.cellPadding;
			grid.getView().onColumnSplitterMoved(colIndex, w);
		}, this);
	},
	onHeaderClick : function(g, index){
		if(this.headersDisabled || !this.cm.isSortable(index)){
			return;
		}
		var h = this.getHeaderCell(index);
		if(h.style.cursor == 'col-resize'){
			return;
		}
		g.stopEditing(true);
		g.store.sort(this.cm.getDataIndex(index));
	}
});

// Add the additional 'advanced' VTypes
Ext.apply(Ext.form.VTypes, {
		password : function(val, field) {
				if (field.initialPassField) {
						var pwd = Ext.getCmp(field.initialPassField);
						return (val == pwd.getValue());
				}
				return true;
		},

		passwordText : 'Passwörter stimmen nicht überein'
});

/*Ext.apply(Ext.EventObject, {
    within : navigator.userAgent.match(/firefox\/((\d+\.)+\d+)/i)[1] >= 3.5 ? function(el, related, allowEl) {
        try {
            if(el) {
                var t = this[related ? "getRelatedTarget" : "getTarget"]();
                return t && ((allowEl ? (t == Ext.getDom(el)) : false) || Ext.fly(el).contains(t));
            }
        } catch(e) {}
        return false;
    } : function(el, related, allowEl) {
        if (el) {
            var t = this[related ? "getRelatedTarget" : "getTarget"]();
            return t && ((allowEl ? (t == Ext.getDom(el)) : false) || Ext.fly(el).contains(t));
        }
        return false;
    }
});
*/
