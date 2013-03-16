<?php
include("../config/config.php");
include("../locale/language.php");
include("../inc/database.php");
include("../inc/functions.php");
include("../security.php");
?>
function setup_form(id_to_load) {
	var form_name = "setup";
	var submitting = false;

	var fs = new Ext.FormPanel({
		plain: true,
		border: false,
		stateful: false,
		monitorValid: true,
		buttonAlign: "center",
		listeners: {
			afterrender: function(form) {
				this.getForm().load({
					url: 'load_config.php',
					success: function(f, a) {}
				});
			},
			clientvalidation: function(form, valid) {
				if (valid && !submitting) Ext.getCmp(form_name + 'save_buttonID').enable();
				else Ext.getCmp(form_name + 'save_buttonID').disable();
			}
		},
		id: form_name + "formID",
		frame: true,
		baseParams: {
			submit_type: form_name,
			action: 'save'
		},
		labelAlign: 'right',
		labelWidth: 125,
		autowidth: true,
		autoHeight: true,
		waitMsgTarget: true,
		defaultType: 'textfield',
		// configure how to read the XML Data
		reader: new Ext.data.JsonReader(),
		// reusable eror reader class defined at the end of this file
		items: [{
			xtype: 'fieldset',
			checkboxToggle: false,
			title: 'Datenbank',
			autoHeight: true,
			defaults: {
				width: 210
			},
			defaultType: 'textfield',
			collapsed: false,
			items: [{
				fieldLabel: 'Datenbank-Server',
				name: 'DB_SERVER',
				allowBlank: false
			}, {
				fieldLabel: 'Datenbank-Name',
				name: 'DB_USER',
				allowBlank: false
			}, {
				fieldLabel: 'Datebank-User',
				name: 'DB_PASSWD',
				allowBlank: false
			}, {
				fieldLabel: 'Datenbank-Passwort',
				name: 'DB_NAME',
				allowBlank: false
			}]
		}, {
			xtype: 'fieldset',
			checkboxToggle: false,
			title: 'Filesystem',
			autoHeight: true,
			defaults: {
				width: 210
			},
			defaultType: 'textfield',
			collapsed: false,
			items: [{
				fieldLabel: 'MP3-Pfad',
				name: 'MP3_PATH',
				allowBlank: false
			}, {
				fieldLabel: 'Album-Cover-Muster',
				name: 'ALBUM_MATCH',

			}]
		}]
	});

	var submit = fs.addButton({
		iconCls: 'icon-accept',
		text: '<?=lang("save",1);?>',
		id: form_name + 'save_buttonID',
		handler: function() {
			submitting = true;
			Ext.getCmp(form_name + 'save_buttonID').disable();
			fs.getForm().baseParams.action = 'save';

			fs.getForm().submit({
				url: 'save_config.php',
				waitMsg: '<?=lang("saving",1);?>',
				success: function(form, action) {
					Ext.getCmp(form_name + 'save_buttonID').enable();
					submitting = false;
					Ext.Msg.show({
						title: '<?=lang("save",1);?>',
						msg: "<?=lang('saving_successfull',1);?>",
						minWidth: 200,
						modal: true,
						icon: Ext.Msg.INFO,
						buttons: Ext.Msg.OK
					});
					Ext.getCmp(form_name + 'navigationID').disable();
					Ext.getCmp(form_name + 'navigationID').store.reload({
						callback: function() {
							Ext.getCmp(form_name + 'navigationID').setValue(action.result.id);
							Ext.getCmp(form_name + 'navigationID').enable();
						}
					})
				},
				failure: function(form, action) {
					Ext.getCmp(form_name + 'save_buttonID').enable();
					submitting = false;
					Ext.Msg.show({
						title: '<?=lang("error",1);?>',
						msg: "<?=lang('error_saving',1);?>",
						minWidth: 200,
						modal: true,
						icon: Ext.Msg.ERROR,
						buttons: Ext.Msg.OK
					});
				}
			});
		}
	});

	return fs;
}