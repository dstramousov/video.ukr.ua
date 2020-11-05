Ext.onReady(function(){
    
Ext.QuickTips.init();
Ext.form.Field.prototype.msgTarget = 'side';

/**
 * Search criteria form
 */
var searchFormItems = [
        {
            name: 'title',
            fieldLabel: 'Заголовок жалобы'
        },
        {
            name: 'file_id',
            fieldLabel: 'ID файла'
        },
    ];

/**
 * File form
 */
var abuseInfoItems = [
        {
            name: 'reason',
            fieldLabel: 'Причина',
            xtype: 'namefield'
        }, {
            name: 'descr',
            fieldLabel: 'Суть жалобы',
            xtype: 'namefield'
        }
    ];
    
var AbuseCatDataStore = new Ext.data.SimpleStore({
    fields:['AbuseId', 'AbuseName'],
    data: [
    		[0, 'Нарушение правил лицензионного соглашения'], 
    		[1, 'Битый файл'], 
    		[2, 'Другая причина']
    	  ],
    id: 0
}); 

//http://www.youtube.com/watch?v=9uJHIzXQWXk&feature=related

/**
 * User form
 */
var createFormItems = [
        {
            name: 'id',
            fieldLabel: 'Номер',
            xtype: 'namefield'
        },
        {
            name: 'descr',
            fieldLabel: 'Описание жалобы',
            xtype: 'namefield'
        },
        {
            name: 'reason',
            fieldLabel: 'Суть жалобы',
            width:250,
            xtype: 'field'
        }
    ];

var Abuse = Ext.data.Record.create([
    {name: 'id', mapping: 'id'},
    {name: 'file_id', mapping: 'file_id'},
    {name: 'descr', type: 'string'},
    {name: 'fileinfo', type: 'string'},
    {name: 'fileview', type: 'string'},
    {name: 'filedelete', type: 'string'}
  ]);

AbuseDataStore = new Ext.data.Store({
        id: 'AbuseDataStore',
        proxy: new Ext.data.HttpProxy({
                url: '/admin/storage/abuse',
                method: 'POST'
        }),
        baseParams:{task: "LISTING"},
        reader: new Ext.data.JsonReader({
                root: 'rows',
                totalProperty: 'total',
                id: 'id'
            },
            Abuse
        ),
        sortInfo:{field: 'id', direction: "DESC"}
    });

	AbuseColumnModel = new Ext.grid.ColumnModel(
    [                     
        {
            header: '# жалобы',
            readOnly: true,
            dataIndex: 'id',
            width: 60,
            readOnly: true
        },
        {
            header: '# файла',
            readOnly: true,
            dataIndex: 'file_id',
            width: 60,
            readOnly: true
        },
        {
            header: 'Описание жалобы',
            dataIndex: 'descr',
            width: 400,
            readOnly: true
        },
        {
            name: 'fileinfo',
            header: '',
            width:25,
            xtype: 'field'
        },
        {
            name: 'fileview',
            header: '',
            width:25,
            xtype: 'field'
        }
        /*
        {
            name: 'filedelete',
            header: '',
            width:25,
            xtype: 'field'
        } */
    ]);
    AbuseColumnModel.defaultSortable = true;
                             
    var AbuseGrid = new Ext.grid.EditorGridPanel({
        title: "Менеджер жалоб",
        store: AbuseDataStore,
        cm: AbuseColumnModel,
        height:400,
        selModel: new Ext.grid.RowSelectionModel({singleSelect:true}),
        enableColLock:false,
        clicksToEdit:1,
        loadMask: true,
        renderTo:'grid-container',
        tbar: [{}],
        bbar: new Ext.PagingToolbar({
                pageSize: 10,
                store: AbuseDataStore,
                displayInfo: true
            })
        
    });
    
    var storageHelper = new Vida.StorageHelper('/admin/storage/abuse', AbuseGrid);
    storageHelper.addSearchForm( {
        filterTitle: "Поиск жалоб по критериям",
        width: 300,
        defaultType: 'filtertextfield',
        items: searchFormItems
    });
    /*

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
            editTitle: "Изменение параметров выбранного ролика",
            width: 450,
            defaultType: 'textfield',
            items: createFormItems,
            setupView: setupView.createDelegate(storageHelper)
        }        
    );
    */
    
    AbuseGrid.render();
    AbuseDataStore.load({params:{start:0, limit:30}});

});