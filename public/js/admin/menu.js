Ext.onReady(function(){
    // The action
    var users_action = new Ext.Action({
        text: 'Пользователи',
        handler: function(){
            //Ext.example.msg('Click','Вы кликнули на "Пользователи".');
            //http://localhost:8081/index/users/
            window.location = "/admin/index/users/";
        }
    });
    var sys_action = new Ext.Action({
        text: 'Системные',
        handler: function(){
            Ext.example.msg('Click','Вы кликнули на "Системные".');
        }
    });
    var video_action = new Ext.Action({
        text: 'Видео',
        handler: function(){
            window.location = "/admin/index/video/";
        }
    });
    var abuse_action = new Ext.Action({
        text: 'Жалобы',
        handler: function(){
            window.location = "/admin/index/abuse/";
        }
    });

    var exit_action = new Ext.Action({
        text: 'Выход',
        handler: function(){
            Ext.example.msg('Click','Вы кликнули на "Выход".');
        }
    });

    var panel = new Ext.Panel({
        title: 'Рабочее место администратора',
        width:'100%',
        height:'auto',
        bodyStyle: 'padding:10px;',     // lazy inline style

        tbar: [
            {                   
                text: 'Справочники',
                menu: [users_action, sys_action, video_action, abuse_action]
            }
        ],

        //renderTo: Ext.getBody()
        renderTo: 'menu-container'
    });

    // Buttons added to the toolbar of the Panel above
    // to test/demo doing group operations with an action
    panel.getTopToolbar().add('->', {
        text: 'Выход',
        handler: function(){
            // Show a dialog using config options:
            Ext.Msg.show({
               title:'Выход',
               msg: 'Вы действительно хотите выйти?',
               buttons: Ext.Msg.YESNOCANCEL,
               fn: function(){},
               icon: Ext.MessageBox.QUESTION
            });
        }
    });
});
