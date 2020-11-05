Ext.onReady(function(){
    
Ext.QuickTips.init();
Ext.form.Field.prototype.msgTarget = 'side';

/**
 * Search criteria form
 */
var searchFormItems = [
        {
            name: 'title',
            fieldLabel: 'Заголовок файла'
        },
        {
            name: 'id',
            fieldLabel: 'ID файла'
        },
    ];

/**
 * File form
 */
var fileInfoItems = [
        {
            name: 'title',
            fieldLabel: 'Заголовок',
            xtype: 'namefield'
        }, {
            name: 'email',
            fieldLabel: '',
            xtype: 'namefield'
        },
        new Ext.form.ComboBox({
            name: 'state',
            fieldLabel: 'Состояние',
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
    data: [ [0, 'Заблокирован'], [1, 'Активен'] ],
    id: 0
});


var FileCatDataStore = new Ext.data.SimpleStore({
    fields:['CatId', 'CatName'],
    data: [
    		[1, 'Автомобили и транспорт'], 
    		[2, 'Комедия'], 
    		[3, 'Образование'], 
    		[4, 'Развлечения'], 
    		[5, 'Фильм и анимация'], 
    		[6, 'Игровые'], 
    		[7, 'Стиль'], 
    		[8, 'Фильмы'], 
    		[9, 'Музыка'], 
    		[10, 'Новости и политика'], 
    		[11, 'Люди и блоги'], 
    		[12, 'Домашние животные'], 
    		[13, 'Наука и технология'], 
    		[14, 'Спорт'], 
    		[15, 'Путешествия и события']
    	  ],
    id: 0
});


/**
 * User form
 */

var createFormItems = [
        {
            name: 'id',
            fieldLabel: 'Номер',
            xtype: 'namefield',
            readOnly: true
        },
        {
            name: 'title',
            fieldLabel: 'Описание файла',
            xtype: 'namefield'
        },
        {
            name: 'filetags',
            fieldLabel: 'Теги файла',
            width:250,
            xtype: 'field'
        },
        {
            name: 'description',
            fieldLabel: 'Описание',
            xtype: 'field'
        },
        new Ext.form.ComboBox({
            name: 'category_id',
            fieldLabel: 'Категория видео',
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
            fieldLabel: 'Состояние',
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
            header: 'Заголовок ролика',
            dataIndex: 'title',
            width: 400,
            readOnly: true
        },
        {
            header: 'Дата загрузки',
            dataIndex: 'created',
            width: 100,
            readOnly: true
        },
        { 
            header: 'Состояние',
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
        title: "Менеджер роликов",
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
        filterTitle: "Поиск файлов по критериям",
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
            editTitle: "Изменение параметров выбранного ролика",
            width: 450,
            defaultType: 'textfield',
            items: createFormItems,
            setupView: setupView.createDelegate(storageHelper)
        }        
    );

    
    FileGrid.render();
    FileDataStore.load({params:{start:0, limit:30}});

});