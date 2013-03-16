<?php
include("../config/config.php");
include("../locale/language.php");
include("../inc/database.php");
include("../inc/functions.php");
include("../security.php");
header("Content-type:text/javascript; charset=utf-8");
?>

function playlist_export_form() {
	var form_name = "playlist_export";
	var fs = new Ext.FormPanel({
		plain: true,
		border: false,
		stateful: false,
		monitorValid: true,
		cls: 'playlist_export',
		layout: 'anchor',
		buttonAlign: "center",
		id: form_name + "formID",
		frame: true,
		baseParams: {
			submit_type: form_name,
			action: 'save'
		},
		autowidth: true,
		waitMsgTarget: true,
		items: [
		new Ext.form.LabelField({
			anchor: '50%',
			value: 'Playlist importieren:'
		}), new Ext.form.LabelField({
			anchor: '45%',
			value: 'Name der Playlist'
		})]
	})
	jsonReader = new Ext.data.JsonReader({
		root: 'rows',
		totalProperty: 'recordcount',
		id: 'ID',
		fields: ['ID', 'name']
	});
	myDataStore = new Ext.data.Store({
		url: 'json.data.php?tab=playlist&no_assign=1',
		reader: jsonReader,
		autoLoad: true,
		remoteSort: false,
		listeners: {
			exception: function(misc) {
				//console.log("error!");
			},
			load: function(store, records, options) {
				for (var i = 0; i < records.length; i++) {
					fs.add(new Ext.form.Checkbox({
						boxLabel: records[i].data.name,
						anchor: '50%',
						checked: true,
						name: "exportID[" + records[i].data.ID + "]"
					}));
					fs.add(new Ext.form.TextField({
						fieldLabel: 'name',
						anchor: '45%',
						value: records[i].data.name + ".m3u",
						name: "exportName[" + records[i].data.ID + "]"
					}));
					console.log(records[i].data.name);
				}
				fs.doLayout();
			}
		}
	});
	fs.addButton({
		anchor: '50%',
		iconCls: 'icon-accept',
		text: 'importieren',
		id: 'playlist_export_ok_buttonID'
	},

	function() {});
	fs.addButton({
		anchor: '50%',
		iconCls: 'icon-delete',
		text: 'abbrechen',
		id: 'playlist_export_cancel_buttonID'
	},

	function() {});
	return fs;
}

function user_form(id_to_load) {
	var form_name = "user";
	var submitting = false;
	var stream_option_data = [
		['stream', '<?=lang("stream_files",1);?>'],
		['direct', '<?=lang("dont_stream_files",1);?>']
	];
	var stream_option_store = new Ext.data.SimpleStore({
		fields: [{
			name: 'value'
		}, {
			name: 'text'
		}]
	});
	stream_option_store.loadData(stream_option_data);
	var fs = new Ext.FormPanel({
		plain: true,
		border: false,
		stateful: false,
		monitorValid: true,
		buttonAlign: "center",
		new_function: function() {
			Ext.Msg.show({
				title: '<?=lang("warning",1);?>',
				msg: '<?=lang("warning_changes_get_lost",1);?>',
				buttons: Ext.Msg.YESNO,
				buttonText: Ext.MessageBox.buttonText.yes = '<?=lang("yes",1);?>',
				buttonText: Ext.MessageBox.buttonText.no = '<?=lang("no",1);?>',
				icon: Ext.MessageBox.QUESTION,
				fn: function(btn) {
					if (btn == "yes") {
						fs.getForm().reset();
						//Ext.getCmp(form_name + 'delete_buttonID').disable();
						Ext.getCmp(form_name + 'user_name').enable();
						Ext.getCmp(form_name + 'user_password').enable();
						Ext.getCmp(form_name + 'user_password2').enable();
						Ext.getCmp('permission_has_access').enable();
						Ext.getCmp('permission_useradmin').enable();
						Ext.getCmp(form_name + 'formID').load_function(0);
					}
				}
			})
		},
		load_function: function(value) {
			if (value < 0) value = 0;
			if (value > 0) {
				Ext.getCmp(form_name + 'navigationID').setValue(value);
				this.getForm().load({
					url: 'get_form_data.php?tab=' + form_name + '&id=' + value,
					success: function(f, a) {
						var checkboxes = Ext.getCmp(form_name + "permissions_checkboxgroup").items;
						for (i = 0; i < checkboxes.items.length; i++) {
							if (a.result.data[checkboxes.items[i].name]) Ext.getCmp(checkboxes.items[i].id).setValue(true);
							else Ext.getCmp(checkboxes.items[i].id).setValue(false);
						}
						if (Ext.getCmp(form_name + 'navigationID').getValue()) {
							enable_disable_buttons(form_name, true);
							if (Ext.getCmp(form_name + 'formID').getForm().reader.jsonData.rows[0].type != "guest") {
								Ext.getCmp(form_name + 'user_password').enable();
								Ext.getCmp(form_name + 'user_password2').enable();
							} else {
								Ext.getCmp(form_name + 'user_password').disable();
								Ext.getCmp(form_name + 'user_password2').disable();
							}
							if (Ext.getCmp(form_name + 'formID').getForm().reader.jsonData.rows[0].type == "admin") {
								Ext.getCmp('permission_has_access').disable();
								Ext.getCmp('permission_useradmin').disable();
							} else {
								Ext.getCmp('permission_has_access').enable();
								Ext.getCmp('permission_useradmin').enable();
							}
							if (Ext.getCmp(form_name + 'formID').getForm().reader.jsonData.rows[0].type == "guest" || Ext.getCmp(form_name + 'formID').getForm().reader.jsonData.rows[0].type == "admin") Ext.getCmp(form_name + 'user_name').disable();
							else Ext.getCmp(form_name + 'user_name').enable();
						} else enable_disable_buttons(form_name, false);
					}
				});
			}
		},
		listeners: {
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
		items: [
		new Ext.form.ComboBox({
			id: form_name + 'navigationID',
			fieldLabel: '<?=lang("user",1);?>',
			hiddenName: 'ID',
			store: new Ext.data.JsonStore({
				url: 'json.data.php',
				root: 'rows',
				baseParams: {
					tab: form_name,
					no_assign: 1
				},
				fields: ['ID', 'name'],
				autoLoad: true,
				listeners: {
					load: function() {
						if (id_to_load) {
							Ext.getCmp(form_name + 'formID').load_function(id_to_load);
							id_to_load = 0;
						}
					}
				}
			}),
			valueField: 'ID',
			displayField: 'name',
			forceSelection: true,
			typeAhead: true,
			mode: 'local',
			triggerAction: 'all',
			emptyText: '<?=lang("create_new_user",1);?>',
			selectOnFocus: true,
			width: 250,
			onSelect: function(record, index) {
				if (this.fireEvent('beforeselect', this, record, index) !== false) {
					var v = this.getValue();
					this.setValue(record.data[this.valueField || this.displayField]);
					this.collapse();
					this.fireEvent('select', this, record, index);
					if (v != this.getValue()) {
						this.fireEvent('change', this, this.getValue(), v);
					}
				}
				fs.load_function(this.getValue());
			}
		}), new Ext.Panel({
			AutoHeight: true,
			width: 650,
			id: form_name + 'tabsID',
			plain: true,
			items: [{
				plain: true,
				layout: 'form',
				autoHeight: true,
				bodyStyle: 'padding-top:10px; padding-bottom:10px',
				defaultType: 'textfield',
				items: [{
					fieldLabel: '<?=lang("name",1);?>',
					id: form_name + 'user_name',
					name: 'name',
					allowBlank: false,
					invalidText: '<?=lang("please_enter_username",1);?>',
					width: 190
				}, {
					fieldLabel: '<?=lang("password",1);?>',
					id: form_name + 'user_password',
					name: 'password',
					allowBlank: true,
					invalidText: '<?=lang("please_enter_password",1);?>',
					width: 190,
					inputType: 'password'
				}, {
					fieldLabel: '<?=lang("repeat_password",1);?>',
					id: form_name + 'user_password2',
					name: 'password2',
					allowBlank: true,
					invalidText: '<?=lang("please_repeat_password",1);?>',
					width: 190,
					inputType: 'password',
					vtype: 'password',
					initialPassField: form_name + 'user_password' // id of the initial password field
				},
				new Ext.form.ComboBox({
					store: stream_option_store,
					forceSelection: true,
					hiddenName: 'access_option',
					fieldLabel: '<?=lang("access_option",1);?>',
					valueField: 'value',
					displayField: 'text',
					typeAhead: true,
					mode: 'local',
					triggerAction: 'all',
					value: 'stream',
					width: 200
				}),
				{
					fieldLabel: 'IP',
					name: 'ip',
					width: 190
				}, {
					xtype: 'checkboxgroup',
					fieldLabel: '<?=lang("permissions",1);?>',
					id: form_name + 'permissions_checkboxgroup',
					columns: 3,
					items: [{
						inputValue: "1",
						id: 'permission_has_access',
						boxLabel: '<?=lang("access_jukebox",1);?>',
						name: 'permission_has_access'
					}, {
						inputValue: "1",
						id: 'permission_useradmin',
						boxLabel: '<?=lang("administrate_user",1);?>',
						name: 'permission_useradmin'
					}, {
						inputValue: "1",
						id: 'permission_read_files',
						boxLabel: '<?=lang("read_files",1);?>',
						name: 'permission_read_files'
					}, {
						inputValue: "1",
						id: 'permission_see_all_playlists',
						boxLabel: '<?=lang("see_all_playlists",1);?>',
						name: 'permission_see_all_playlists'
					}, {
						inputValue: "1",
						id: 'permission_edit_own_playlists',
						boxLabel: '<?=lang("edit_own_playlists",1);?>',
						name: 'permission_edit_own_playlists'
					}, {
						inputValue: "1",
						id: 'permission_edit_all_playlists',
						boxLabel: '<?=lang("edit_all_playlists",1);?>',
						name: 'permission_edit_all_playlists'
					}, {
						inputValue: "1",
						id: 'permission_edit_tags',
						boxLabel: '<?=lang("edit_tags",1);?>',
						name: 'permission_edit_tags'
					}, {
						inputValue: "1",
						id: 'permission_download',
						boxLabel: '<?=lang("download_files",1);?>',
						name: 'permission_download'
					}, {
						inputValue: "1",
						id: 'permission_lyrics',
						boxLabel: '<?=lang("fetch_lyrics",1);?>',
						name: 'permission_lyrics'
					}, {
						inputValue: "1",
						id: 'permission_youtube',
						boxLabel: '<?=lang("fetch_youtube_videos",1);?>',
						name: 'permission_youtube'
					}]
				}]
			}]
		})]
	});

	fs.addButton({
		iconCls: 'icon-new-user',
		text: '<?=lang("new",1);?>',
		id: form_name + 'new_buttonID'
	},

	function() {
		Ext.getCmp(form_name + 'formID').new_function();
	});

	var submit = fs.addButton({
		iconCls: 'icon-accept',
		text: '<?=lang("save",1);?>',
		id: form_name + 'save_buttonID',

		handler: function() {
			if (!Ext.getCmp(form_name + 'navigationID').getValue()) {
				if (Ext.getCmp(form_name + 'user_password').getValue() == "") {
					Ext.getCmp(form_name + 'user_password').markInvalid();
					return false;
				}
				if (Ext.getCmp(form_name + 'user_password2').getValue() == "") {
					Ext.getCmp(form_name + 'user_password2').markInvalid();
					return false;
				}
				if (Ext.getCmp(form_name + 'user_password').getValue() != Ext.getCmp(form_name + 'user_password2').getValue()) {
					Ext.getCmp(form_name + 'user_password2').markInvalid("<?=lang('passwords_dont_match',1);?>");
					return false;
				}
			}
			submitting = true;
			enable_disable_buttons(form_name, false);
			fs.getForm().baseParams.action = 'save';
			fs.getForm().submit({
				url: 'submit.php',
				waitMsg: '<?=lang("saving",1);?>',
				success: function(form, action) {
					enable_disable_buttons(form_name, true);
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
							if (Ext.getCmp(form_name + 'formID').getForm().reader.jsonData.rows[0].type == "guest" || Ext.getCmp(form_name + 'formID').getForm().reader.jsonData.rows[0].type == "admin") Ext.getCmp(form_name + 'delete_buttonID').disable();
							else Ext.getCmp(form_name + 'delete_buttonID').enable();
						}
					})
				},
				failure: function(form, action) {
					enable_disable_buttons(form_name, true);
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

	fs.addButton({
		iconCls: 'icon-delete',
		text: '<?=lang("delete",1);?>',
		id: form_name + 'delete_buttonID',
		disabled: true
	}, function() {
		Ext.Msg.show({
			title: '<?=lang("warning",1);?>',
			msg: '<?=lang("confirm_user_delete",1);?>',
			buttons: Ext.Msg.YESNO,
			buttonText: Ext.MessageBox.buttonText.yes = 'Ja',
			buttonText: Ext.MessageBox.buttonText.no = 'Nein',
			icon: Ext.MessageBox.QUESTION,
			fn: function(btn) {
				if (btn == "yes") {
					submitting = true;
					enable_disable_buttons(form_name, false);
					fs.getForm().baseParams.action = 'delete';
					fs.getForm().submit({
						url: 'submit.php',
						success: function(form, action) {
							Ext.Msg.show({
								title: '<?=lang("delete",1);?>',
								msg: '<?=lang("delete_succesfull",1);?>',
								minWidth: 200,
								modal: true,
								icon: Ext.Msg.INFO,
								buttons: Ext.Msg.OK
							});
							Ext.getCmp(form_name + 'navigationID').disable();
							Ext.getCmp(form_name + 'navigationID').store.reload({
								callback: function() {
									fs.getForm().reset();
									Ext.getCmp(form_name + 'navigationID').enable();
									enable_disable_buttons(form_name, true);
									submitting = false;
								}
							})
						},
						failure: function(form, action) {
							enable_disable_buttons(form_name, true);
							submitting = false;
							Ext.Msg.show({
								title: '<?=lang("error",1);?>',
								msg: '<?=lang("error_deleting_user",1);?>',
								minWidth: 200,
								modal: true,
								icon: Ext.Msg.ERROR,
								buttons: Ext.Msg.OK
							});
						}
					})
				}
			}
		})
	});
	return fs;
}