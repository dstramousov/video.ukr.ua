Ext.onReady(function(){
    // The action
    var users_action = new Ext.Action({
        text: '������������',
        handler: function(){
            //Ext.example.msg('Click','�� �������� �� "������������".');
            //http://localhost:8081/index/users/
            window.location = "/admin/index/users/";
        }
    });
    var sys_action = new Ext.Action({
        text: '���������',
        handler: function(){
            Ext.example.msg('Click','�� �������� �� "���������".');
        }
    });
    var video_action = new Ext.Action({
        text: '�����',
        handler: function(){
            window.location = "/admin/index/video/";
        }
    });
    var abuse_action = new Ext.Action({
        text: '������',
        handler: function(){
            window.location = "/admin/index/abuse/";
        }
    });

    var exit_action = new Ext.Action({
        text: '�����',
        handler: function(){
            Ext.example.msg('Click','�� �������� �� "�����".');
        }
    });

    var panel = new Ext.Panel({
        title: '������� ����� ��������������',
        width:'100%',
        height:'auto',
        bodyStyle: 'padding:10px;',     // lazy inline style

        tbar: [
            {                   
                text: '�����������',
                menu: [users_action, sys_action, video_action, abuse_action]
            }
        ],

        //renderTo: Ext.getBody()
        renderTo: 'menu-container'
    });

    // Buttons added to the toolbar of the Panel above
    // to test/demo doing group operations with an action
    panel.getTopToolbar().add('->', {
        text: '�����',
        handler: function(){
            // Show a dialog using config options:
            Ext.Msg.show({
               title:'�����',
               msg: '�� ������������� ������ �����?',
               buttons: Ext.Msg.YESNOCANCEL,
               fn: function(){},
               icon: Ext.MessageBox.QUESTION
            });
        }
    });
});
