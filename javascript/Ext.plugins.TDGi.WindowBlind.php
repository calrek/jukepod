Ext.ns('Ext.plugins.TDGi');

Ext.plugins.TDGi.WindowBlind = Ext.extend(Ext.Panel, {
		frame : true,
		draggable : false,
		animate : true,
		hidden : true,
		animDuration : .55,
		offsetHeight : 10,
		offsetWidth : 5,
		show : function (skipAnim) {
			var me = this;
			if (me.hidden && me.fireEvent("beforeshow", me) !== false) {
				me.hidden = false;
				me.constructor.superclass.show.call(me);
				me.afterShow(!!skipAnim);
			}
		},
		hide : function (skipAnim) {
			var me = this;
			if (me.hidden) {
				return;
			}
			if (me.animate === true && !skipAnim) {
				me.el.slideOut('t', {
					duration : me.animDuration,
					callback : me.onAfterAnimHide,
					scope : this
				});
			} else {
				Ext.plugins.TDGi.WindowBlind.superclass.hide.call(me);
			}
			me.hidden = true;
		},
		init : function (parent) {
			var me = this;
			me.win = parent;
			parent.blind = this;
			me.win.on({
				scope : me,
				afterrender : me.onAfterParentRender,
				destroy : me.destroy,
				resize : me.onParentResize
			});
		},
		initComponent : function () {
			var me = this;
			delete me.renderTo;
			Ext.plugins.TDGi.WindowBlind.superclass.initComponent.call(me);
			me.on({
				beforeshow : {
					scope : me,
					fn : me.onBeforeShow
				},
				beforehide : {
					scope : me,
					fn : me.onBeforeHide
				}
			});
		},
		onBeforeHide : function () {
			if (this.animate) {
				this.getEl().addClass('x-panel-animated');
			}
		},
		onAfterAnimHide : function () {
			var me = this;
			me.el.setVisible(false);
			var thisWin = me.win;
			thisWin.body.unmask();
			if (thisWin.fbar) {
				thisWin.fbar.el.unmask();
			}
			me.fireEvent('hide', me)
		},
		onBeforeShow : function () {
			var me = this,
			thisWin = me.win;
			thisWin.body.mask();
			if (thisWin.fbar) {
				thisWin.fbar.el.mask();
			}
			if (!me.rendered) {
				me.render(me.renderTo);
				delete me.renderTo;
			}
		},
		onAfterParentRender : function (win) {
			var me = this;
			me.prntTitleHeight = win.el.child('.x-window-tl').getHeight();
			Ext.apply(me, {
				renderTo : win.el,
				style : 'z-index: 2;position:absolute;top: ' + (--me.prntTitleHeight) + 'px; left: 10px;',
				height : win.body.getHeight() + (me.prntTitleHeight - me.offsetHeight)
			});
		},
		afterShow : function (skipAnim) {
			var me = this;
			if (me.animate && !skipAnim) {
				me.el.down('.x-panel-tl').hide();
				me.el.slideIn('t', {
					scope : me,
					easing : 'easeOut',
					duration : me.animDuration
				});
			} else {
				Ext.plugins.TDGi.WindowBlind.superclass.afterShow.call(me);
			}
		},
		onParentResize : function (win, width, height) {
			var me = this,
			w = win.body.getWidth() - me.offsetWidth,
			h = win.body.getHeight() + (me.prntTitleHeight - me.offsetHeight);
			me.setSize(w, h);
		}
	});
Ext.preg('Ext.plugins.TDGi.WindowBlind', Ext.plugins.TDGi.WindowBlind);
