Ext.ns('Ext.ux.grid');
Ext.ux.grid.RateColumnPlugin = function(config){
	Ext.apply(this, config);
	if(!this.id){
		this.id = Ext.id(null, 'rating-');
	}
}
Ext.apply(Ext.ux.grid.RateColumnPlugin.prototype, {
	tickSize: 20,
	selectedCls: 'rating-selected',
	unselectedCls: 'rating-unselected',
	roundToTick: true,
	init: function(grid){
		this.grid = grid;
		grid.on('render', function(c){
			c.getView().mainBody.on('mousedown', this.onMouseDown, this, {delegate: '.' + this.id, stopEvent: true});
		}, this);
	},
	onMouseDown: function(e, t){
		var value = (e.getXY()[0] - Ext.fly(t).getX()) / this.tickSize;
		if (value < this.zeroSensitivity) { value = 0} //<--- added this line
		if(this.roundToTick){
			value = Math.ceil(value);
		}
		var view = this.grid.getView();
		var rowIndex = view.findRowIndex(t);
		var colIndex = view.findCellIndex(t);
		var dataIndex = this.grid.getColumnModel().getDataIndex(colIndex);
		this.grid.getStore().getAt(rowIndex).set(dataIndex, value);
		
		var record = this.grid.getStore().getAt(rowIndex);
		var ID = record.get('ID');
		
		
	},
	createRenderer: function(count){
		return function(value, count){
			return '<div class="' + this.id + ' ' + this.unselectedCls + '" style="width:' + Math.round(this.tickSize * count) + 'px">' +
				'<div class="' + this.selectedCls + '" style="width:' + Math.round(this.tickSize * value) + 'px">&nbsp;</div></div>';
		}.createDelegate(this, [count || 5], 1);
	}
});
