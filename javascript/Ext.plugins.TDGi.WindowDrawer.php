Ext.ns('Ext.plugins.TDGi');
Ext.plugins.TDGi.WindowDrawer = Ext.extend(Ext.Window, {
		closable : false,
		resizable : false,
		frame : true,
		draggable : false,
		modal : false,
		closeAction : 'hide',
		show : function (skipAnim) {
			var me = this;
			if (me.hidden && me.fireEvent("beforeshow", me) !== false) {
				me.hidden = false;
				me.onBeforeShow();
				me.afterShow(!!skipAnim);
			}
		},
		toFront : Ext.emptyFn,
		hide : function (skipAnim) {
			var me = this;
			if (me.hidden) {
				return;
			}
			if (me.animate === true && !skipAnim) {
				if (me.el.shadow) {
					me.el.disableShadow();
				}
				me.el.slideOut(me.alignToParams.slideDirection, {
					duration : me.animDuration || .25,
					callback : me.onAfterAnimHide,
					scope : me
				});
			} else {
				Ext.plugins.TDGi.WindowDrawer.superclass.hide.call(me);
			}
			me.hidden = false;
		},
		init : function (parent) {
			var me = this;
			me.win = parent;
			me.alignToParams = {};
			me.resizeHandles = me.side;
			parent.drawers = parent.drawers || {};
			parent.drawers[me.side] = me;
			parent.on({
				scope : me,
				tofront : me.onBeforeShow,
				toback : me.onBeforeShow,
				resize : me.alignAndShow,
				show : me.alignAndShow,
				beforedestroy : me.destroy,
				afterrender : function (p) {
					me.renderTo = p.el;
				}
			});
			if (!Ext.isIE) {
				var drawer = this;
				parent.ghost = parent.ghost.createSequence(function () {
						if (drawer.el && !drawer.hidden) {
							var winGhost = me.activeGhost,
							drawerGhost = drawer.ghost();
							if (winGhost) {
								winGhost.appendChild(drawerGhost);
								drawerGhost.anchorTo(winGhost.dom, drawer.alignToParams.alignTo, drawer.alignToParams.alignToXY);
								drawerGhost.applyStyles('z-index: -1;');
								winGhost.applyStyles('overflow: visible;');
							}
						}
					});
				parent.unghost = parent.unghost.createInterceptor(function () {
						if (drawer.activeGhost) {
							drawer.unghost();
						}
					});
			}
		},
		initComponent : function () {
			var me = this;
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
			if (me.size) {
				if (me.side == 'e' || me.side == 'w') {
					me.width = me.size;
				} else {
					me.height = me.size;
				}
			}
			Ext.plugins.TDGi.WindowDrawer.superclass.initComponent.apply(me);
		},
		onBeforeResize : function () {
			var me = this;
			if (!me.hidden) {
				me.showAgain = true;
			}
			me.hide(true);
		},
		onBeforeHide : function () {
			var me = this;
			if (me.animate) {
				me.getEl().addClass('x-panel-animated');
			}
		},
		onAfterAnimHide : function () {
			this.el.setVisible(false);
		},
		onBeforeShow : function () {
			var me = this;
			if (!me.rendered) {
				me.render(me.renderTo);
			}
			me.el.addClass('x-panel-animated');
			me.setAlignment();
			me.setZIndex();
		},
		afterShow : function (skipAnim) {
			var me = this;
			if (me.animate && !skipAnim) {
				me.getEl().removeClass('x-panel-animated');
				me.el.slideIn(me.alignToParams.slideDirection, {
					scope : me,
					duration : me.animDuration || .25,
					callback : function () {
						if (me.el.shadow) {
							me.el.enableShadow(true);
						}
						me.el.show();
					}
				});
			} else {
				Ext.plugins.TDGi.WindowDrawer.superclass.afterShow.call(me);
			}
		},
		alignAndShow : function () {
			var me = this;
			me.setAlignment();
			if (me.showAgain) {
				me.show(true);
			}
			me.showAgain = false;
		},
		setAlignment : function () {
			var me = this;
			if (!me.el) {
				return;
			}
			switch (me.side) {
			case 'n':
				me.setWidth(me.win.el.getWidth() - 10);
				Ext.apply(me.alignToParams, {
					alignTo : 'tl',
					alignToXY : [5, (me.el.getComputedHeight() * -1) + 5],
					slideDirection : 'b'
				});
				break;
			case 's':
				me.setWidth(me.win.el.getWidth() - 10);
				Ext.apply(me.alignToParams, {
					alignTo : 'bl',
					alignToXY : [5, (Ext.isIE6) ? -2 : -7],
					slideDirection : 't'
				});
				break;
			case 'e':
				me.setHeight(me.win.el.getHeight() - 10);
				Ext.apply(me.alignToParams, {
					alignTo : 'tr',
					alignToXY : [-5, 5],
					slideDirection : 'l'
				});
				break;
			case 'w':
				me.setHeight(me.win.el.getHeight() - 10);
				Ext.apply(me.alignToParams, {
					alignTo : 'tl',
					alignToXY : [(me.el.getComputedWidth() * -1) + 5, 5],
					slideDirection : 'r'
				});
				break;
			}
			if (!me.hidden) {
				me.el.alignTo(me.win.el, me.alignToParams.alignTo, me.alignToParams.alignToXY);
				if (Ext.isIE) {
					me.bwrap.hide();
					me.bwrap.show();
				}
			}
			me.doLayout();
		},
		setZIndex : function () {
			return this.constructor.superclass.setZIndex.call(this, -3);
		}
	});
Ext.preg('Ext.plugins.TDGi.WindowDrawer', Ext.plugins.TDGi.WindowDrawer);
