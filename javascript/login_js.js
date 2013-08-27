Login = function() {
	var dialog, form, submitUrl = 'functions/try_login.php';

	return {
		Init: function() {
			Ext.QuickTips.init();

			var formPanel = new Ext.form.FormPanel({
				baseCls: 'x-plain',
				keys: [{
					key: [10, 13],
					fn: fn_submit_login
				}],
				baseParams: {},
				bodyStyle: 'padding:75px 35px;',
				defaults: {
					width: 200
				},
				monitorValid: true,
				defaultType: 'textfield',
				items: [{
					fieldLabel: 'Username',
					name: 'username',
					value: ''
				}, {
					fieldLabel: 'Password',
					id: 'passwordID',
					inputType: 'password',
					name: 'password',
					value: ''
				}, {
					fieldLabel: 'Save Login',
					id: 'save_login',
					xtype: 'checkbox',
					name: 'save_login',
					value: ''
				}],
				labelWidth: 120,
				region: 'center',
				url: submitUrl
			});

			function fn_submit_login() {
				form.submit({
					reset: true,
					success: Login.Success,
					failure: Login.Failure
				});
			}

			var dialog = new Ext.Window({
				buttons: [{
					handler: fn_submit_login,
					text: 'Login'
				}],
				buttonAlign: 'right',
				closable: false,
				draggable: false,
				layout: 'border',
				plain: false,
				resizable: false,
				html: "<div class='login-title'><img onClick='alert(0)' border=0 src='img/mywebjukebox_hg.png'></div>",
				items: [
				formPanel],
				title: 'Login To Jukepod',
				width: 430,
				height: 230
			});

			form = formPanel.getForm();

			dialog.show();
		},

		Success: function(f, a) {

			window.location.href = 'index.php';

		},
		Failure: function(f, a) {
			Ext.MessageBox.alert('Error', 'Error Login');
			Ext.getCmp("passwordID").focus(true, 1000);
		}
	};
}();

Ext.onReady(Login.Init, Login, true);