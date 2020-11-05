Ext.onReady(function(){
    
Ext.QuickTips.init();
Ext.form.Field.prototype.msgTarget = 'side';

/**
 * Search criteria form
 */
var searchFormItems = [
        {
            name: 'title',
            fieldLabel: '��������� �����'
        },
        {
            name: 'id',
            fieldLabel: 'ID �����'
        },
    ];

/**
 * File form
 */
var fileInfoItems = [
        {
            name: 'title',
            fieldLabel: '���������',
            xtype: 'namefield'
        }, {
            name: 'email',
            fieldLabel: '',
            xtype: 'namefield'
        },
        new Ext.form.ComboBox({
            name: 'state',
            fieldLabel: '���������',
            typeAhead: true,
            triggerAction: 'all',
            store: FileStatesDataStore,
            mode: 'local',
            displayField: 'StateName',
            hiddenName:'state',
            valueField: 'StateId'
        })
    ];
    
/**
 * FileDataStore defination
 */
var FileStatesDataStore = new Ext.data.SimpleStore({
    fields:['StateId', 'StateName'],
    data: [ [0, '������������'], [1, '�������'] ],
    id: 0
});


var FileCatDataStore = new Ext.data.SimpleStore({
    fields:['CatId', 'CatName'],
    data: [
    		[1, '���������� � ���������'], 
    		[2, '�������'], 
    		[3, '�����������'], 
    		[4, '�����������'], 
    		[5, '����� � ��������'], 
    		[6, '�������'], 
    		[7, '�����'], 
    		[8, '������'], 
    		[9, '������'], 
    		[10, '������� � ��������'], 
    		[11, '���� � �����'], 
    		[12, '�������� ��������'], 
    		[13, '����� � ����������'], 
    		[14, '�����'], 
    		[15, '����������� � �������']
    	  ],
    id: 0
});


/**
 * User form
 */

var createFormItems = [
        {
            name: 'id',
            fieldLabel: '�����',
            xtype: 'namefield',
            readOnly: true
        },
        {
            name: 'title',
            fieldLabel: '�������� �����',
            xtype: 'namefield'
        },
        {
            name: 'filetags',
            fieldLabel: '���� �����',
            width:250,
            xtype: 'field'
        },
        {
            name: 'description',
            fieldLabel: '��������',
            xtype: 'field'
        },
        new Ext.form.ComboBox({
            name: 'category_id',
            fieldLabel: '��������� �����',
            typeAhead: true,
            triggerAction: 'all',
            store: FileCatDataStore,
            mode: 'local',
            displayField: 'CatName',
            hiddenName:'category_id',
            valueField: 'CatId'
        }),
        new Ext.form.ComboBox({
            name: 'state',
            fieldLabel: '���������',
            typeAhead: true,
            triggerAction: 'all',
            store: FileStatesDataStore,
            mode: 'local',
            displayField: 'StateName',
            hiddenName:'state',
            valueField: 'StateId'
        })
    ];




function renderState(value, metadata, record) {
    var rec = FileStatesDataStore.getById(value);
    if(rec != undefined) {
        return rec.data['StateName'];
    }

    return "";
}

var File = Ext.data.Record.create([
    {name: 'id', mapping: 'id'},
    {name: 'created', type: 'string'},
    {name: 'accessed', type: 'string'},
    {name: 'user_id', type: 'string'},
    {name: 'category_id', type: 'string'},
    {name: 'state', type: 'string'},
    {name: 'path', type: 'string'},
    {name: 'key', type: 'string'},
    {name: 'requested', type: 'string'},
    {name: 'title', type: 'string'},
    {name: 'params', type: 'string'}
  ]);

FileDataStore = new Ext.data.Store({
        id: 'FileDataStore',
        proxy: new Ext.data.HttpProxy({
                url: '/admin/storage/files',
                method: 'POST'
        }),
        baseParams:{task: "LISTING"},
        reader: new Ext.data.JsonReader({
                root: 'rows',
                totalProperty: 'total',
                id: 'id'
            },
            File
        ),
        sortInfo:{field: 'created', direction: "DESC"}
    });

	FileColumnModel = new Ext.grid.ColumnModel(
    [   {
            header: '#',
            dataIndex: 'id', // this is where the mapped name is important!
            width: 50,
        },
        {
            header: '��������� ������',
            dataIndex: 'title',
            width: 400,
            readOnly: true
        },
        {
            header: '���� ��������',
            dataIndex: 'created',
            width: 100,
            readOnly: true
        },
        { 
            header: '���������',
            dataIndex: 'state',
            width: 120,
            editor: new Ext.form.ComboBox({
                  typeAhead: true,
                  triggerAction: 'all',
                  store: FileStatesDataStore,
                  mode: 'local',
                  displayField: 'StateName',
                  valueField: 'StateId'
              }),
            renderer: renderState,
            hidden: false
        }
    ]);
    FileColumnModel.defaultSortable = true;

    var FileGrid = new Ext.grid.EditorGridPanel({
        title: "�������� �������",
        store: FileDataStore,
        cm: FileColumnModel,
        height:400,
        selModel: new Ext.grid.RowSelectionModel({singleSelect:true}),
        enableColLock:false,
        clicksToEdit:1,
        loadMask: true,
        renderTo:'grid-container',
        tbar: [{}],
        bbar: new Ext.PagingToolbar({
                pageSize: 10,
                store: FileDataStore,
                displayInfo: true
            })
        
    });

    var storageHelper = new Vida.StorageHelper('/admin/storage/files', FileGrid);
    storageHelper.addSearchForm( {
        filterTitle: "����� ������ �� ���������",
        width: 300,
        defaultType: 'filtertextfield',
        items: searchFormItems
    });

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

        }
    }

    storageHelper.addEditForm({
            editTitle: "��������� ���������� ���������� ������",
            width: 450,
            defaultType: 'textfield',
            items: createFormItems,
            setupView: setupView.createDelegate(storageHelper)
        }        
    );

    
    FileGrid.render();
    FileDataStore.load({params:{start:0, limit:30}});

});