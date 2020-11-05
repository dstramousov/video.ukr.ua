Ext.onReady(function(){
    
Ext.QuickTips.init();
Ext.form.Field.prototype.msgTarget = 'side';

/**
 * Search criteria form
 */
var searchFormItems = [
        {
            name: 'login',
            fieldLabel: 'Login'
        },
        {
            name: 'email',
            fieldLabel: 'E-mail'
        },
        {
            name: 'fname',
            fieldLabel: 'Фамилия'
        },
        {
            name: 'lname',
            fieldLabel: 'Имя'
        }
    ];

/**
 * UserStatesDataStore defination
 */
var UserStatesDataStore = new Ext.data.SimpleStore({
    fields:['StateId', 'StateName'],
    data: [ [0, 'Заблокирована'], [1, 'Активная'] ],
    id: 0
});
function renderState(value, metadata, record) {
    var rec = UserStatesDataStore.getById(value);
    if(rec != undefined) {
        return rec.data['StateName'];
    }
    return "";
}

/**
 * User form
 */
var createFormItems = [
        {
            name: 'login',
            fieldLabel: 'Login',
            xtype: 'loginfield'
        }, {
            name: 'email',
            fieldLabel: 'E-mail',
            xtype: 'emailfield'
        }, {
            name: 'fname',
            fieldLabel: 'Фамилия',
            xtype: 'namefield'
        }, {
            name: 'lname',
            fieldLabel: 'Имя',
            xtype: 'namefield'
        }, {
            name: 'password',
            fieldLabel: 'Password',
            xtype: 'passwordfield'
        }, {
            name: 'pass-cfrm',
            fieldLabel: 'Confirm Password',
            vtype: 'password',
            initialPassField: 'password', // id of the initial password field
            xtype: 'passwordfield'
        },
        new Ext.form.ComboBox({
            name: 'state',
            fieldLabel: 'Состояние',
            typeAhead: true,
            triggerAction: 'all',
            store: UserStatesDataStore,
            mode: 'local',
            displayField: 'StateName',
            hiddenName:'state',
            valueField: 'StateId'
        })
    ];
    
/**
 * UserDataStore defination
 */
var User = Ext.data.Record.create([
    {name: 'id', mapping: 'id'},
    {name: 'login', type: 'string'},
    {name: 'fname', type: 'string'},
    {name: 'lname', type: 'string'},
    {name: 'state', type: 'int'},
    {name: 'email', type: 'string'}
  ]);

UsersDataStore = new Ext.data.Store({
        id: 'UsersDataStore',
        proxy: new Ext.data.HttpProxy({
                url: '/admin/storage/users',      // File to connect to
                method: 'POST'
        }),
        baseParams:{task: "LISTING"},       // this parameter asks for listing
        reader: new Ext.data.JsonReader({   
                root: 'rows',               //элемент в котором лежат строки
                totalProperty: 'total',     //количество элементов в результате
                id: 'id'                    //первичный ключ
            },
            User
        ),
        sortInfo:{field: 'login', direction: "ASC"}
    });

UsersColumnModel = new Ext.grid.ColumnModel(
    [   {
            header: '#',
            readOnly: true,
            dataIndex: 'id', // this is where the mapped name is important!
            width: 50,
            hidden: false
        },
        {
            header: 'Имя учетной записи',
            dataIndex: 'login',
            width: 120,
            readOnly: true
        },
        { 
            header: 'Состояние',
            dataIndex: 'state',
            width: 120,
            editor: new Ext.form.ComboBox({
                  typeAhead: true,
                  triggerAction: 'all',
                  store: UserStatesDataStore,
                  mode: 'local',
                  displayField: 'StateName',
                  valueField: 'StateId'
              }),
            renderer: renderState,
            hidden: false
        },
        {
            header: 'E-mail',
            dataIndex: 'email',
            readOnly: true,
            width: 150
            //editor: new Vida.EmailField({})
        },
        {
          header: 'Фамилия',
          dataIndex: 'fname',
          width: 150,
          editor: new Vida.NameField({})
        },
        {
          header: 'Имя',
          dataIndex: 'lname',
          width: 150,
          editor: new Vida.NameField({})
        }
    ]);
    UsersColumnModel.defaultSortable= true;

    var UsersGrid = new Ext.grid.EditorGridPanel({
        title: "Справочник пользователей",
        store: UsersDataStore,
        cm: UsersColumnModel,
        height:300,
        selModel: new Ext.grid.RowSelectionModel({singleSelect:true}),
        enableColLock:false,
        clicksToEdit:1,
        loadMask: true,
        renderTo:'grid-container',
        tbar: [{}],
        bbar: new Ext.PagingToolbar({
                pageSize: 10,
                store: UsersDataStore,
                displayInfo: true
            })
        
    });

    var storageHelper = new Vida.StorageHelper('/admin/storage/users', UsersGrid);
    storageHelper.addSearchForm( {
        filterTitle: "Поиск пользователей по критериям",
        width: 300,
        defaultType: 'filtertextfield',
        items: searchFormItems
    });
    
    //
    function setupView(form, mode) {
        if(mode == Vida.DataForm_Mode.CREATE_MODE) {
            var state = form.findField('state');
            if(!Ext.isEmpty(state)) {
                state.setValue(1);
                state.disable();
            } 
        } else if(mode == Vida.DataForm_Mode.EDIT_MODE) {
            var login = form.findField('login');
            if(!Ext.isEmpty(login)) login.disable();
            
            var email = form.findField('email');
            if(!Ext.isEmpty(email)) email.disable();

            var password = form.findField('password');
            if(!Ext.isEmpty(password)) password.allowBlank = true;
            
            var pass_cfrm = form.findField('pass-cfrm');
            if(!Ext.isEmpty(pass_cfrm)) pass_cfrm.allowBlank = true;
            
        }
    }
    storageHelper.addEditForm({
            createTitle: "Создание нового пользователя",
            editTitle: "Изменение выбранного пользователя",
            width: 450,
            defaultType: 'textfield',
            items: createFormItems,
            setupView: setupView.createDelegate(storageHelper)
        }        
    );
    
    UsersGrid.render();
    UsersDataStore.load({params:{start:0, limit:10}});

});