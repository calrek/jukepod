
Ext.ux.SlidingPager = Ext.extend(Ext.util.Observable, {
    init : function(pbar){
        this.pagingBar = pbar;

        pbar.on('render', this.onRender, this);
        pbar.on('beforedestroy', this.onDestroy, this);
    },

    onRender : function(pbar){
        Ext.each(pbar.items.getRange(2,6), function(c){
            //c.hide();
        });
        var td = document.createElement("td");
        pbar.tr.insertBefore(td, pbar.tr.childNodes[6]);

        /*var td_text = document.createElement("td");
        pbar.tr.insertBefore(td_text, pbar.tr.childNodes[5]);*/

        td.style.padding = '0 5px';
				
        this.slider = new Ext.Slider({
            width: 70,
            minValue: 1,
            maxValue: 1,
            plugins:new Ext.ux.SliderTip({
                bodyStyle:'padding:5px;',
                getText : function(s){
                    return String.format('Seite <b>{0}</b> von <b>{1}</b>', s.value, s.maxValue);
                }
            })
        });
        this.slider.render(td);

        this.slider.on('changecomplete', function(s, v){
            pbar.changePage(v);
        });
				
				pbar.myinit = function(){
					//this.tr.childNodes[5].innerHTML = "Seite " + this.getPageData().activePage + " von " + this.getPageData().pages;
				}

        pbar.on('change', function(pb, data){
            this.slider.maxValue = data.pages;
            this.slider.setValue(data.activePage);
						//pb.myinit();
        }, this);
    },

    onDestroy : function(){
        this.slider.destroy();
    }
});

Ext.ux.SliderTip = Ext.extend(Ext.Tip, {
    minWidth: 10,
    offsets : [0, -10],
    init : function(slider){
        slider.on('dragstart', this.onSlide, this);
        slider.on('drag', this.onSlide, this);
        slider.on('dragend', this.hide, this);
        slider.on('destroy', this.destroy, this);
    },

    onSlide : function(slider){
        this.show();
        this.body.update(this.getText(slider));
        this.doAutoWidth();
        this.el.alignTo(slider.thumb, 'b-t?', this.offsets);
    },

    getText : function(slider){
        return slider.getValue();
    }
});
