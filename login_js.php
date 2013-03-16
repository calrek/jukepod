<?php
include ("config/config.php");
include ("locale/language.php");
include ("inc/database.php");
include ("inc/functions.php");
?>
Login = function(){
	var dialog,form,submitUrl = 'try_login.php';

	return{
		Init:function(){
			Ext.QuickTips.init();

			var formPanel = new Ext.form.FormPanel({
				baseCls: 'x-plain',
				keys: [
					{
					key : [10,13],
					fn: fn_submit_login
					}
					],
				baseParams: {	},
				bodyStyle: 'padding:75px 35px;',
				defaults: {
					width: 200
				},
				monitorValid:true,
				defaultType: 'textfield',
				items: [
				{
						fieldLabel: '<?=lang("username",1);?>',
						name: 'username',
						value: ''
				},{
						fieldLabel: '<?=lang("password",1);?>',
						id: 'passwordID',
						inputType: 'password',
						name: 'password',
						value: ''
				},{
						fieldLabel: '<?=lang("save_login",1);?>',
						id: 'save_login',
						xtype: 'checkbox',
						name: 'save_login',
						value: ''
				}],
				labelWidth:120,
				region: 'center',
				url: submitUrl
		    });

			function fn_submit_login()
			{
			form.submit({
				reset:true,
				success:Login.Success,
				failure:Login.Failure
				});
			}

		  var dialog = new Ext.Window({
		        buttons: [{
			        	handler: fn_submit_login,
		            text: '<?=lang("login",1);?>'
		        		}
							],
		        buttonAlign: 'right',
		        closable: false,
		        draggable: false,
		        layout: 'border',
		        plain: false,
		        resizable: false,
						html: "<div class='login-title'><img onClick='alert(0)' border=0 src='img/mywebjukebox_hg.png'></div>",
		        items: [
							formPanel
		        ],
				title: '<?=lang("title_login",1);?>',
        width: 430,
				height: 230
		    });

 			 	form = formPanel.getForm();

		    dialog.show();
		},

		Success: function(f,a){

			window.location.href = 'index.php';

		},
	  Failure: function(f, a) {
			Ext.MessageBox.alert('<?=lang("error",1);?>', '<?=lang("login_fail",1);?>');
			Ext.getCmp("passwordID").focus(true,1000);
		}
	};
}();

Ext.onReady(Login.Init, Login, true);
