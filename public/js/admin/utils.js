Vida = {};

/**
 * DataForm object
 */
Vida.DataForm_Mode = {
    /**
     * Enum of form's states
     * @type {Number}
     */
    EDIT_MODE: 1,
    VIEW_MODE: 2,
    CREATE_MODE: 3,
    FILTER_MODE: 4
};

Vida.DataForm = Ext.extend(Ext.Window, {
    primaryKey: -1,
    
    acceptText: "Сохранить",
    acceptCallback: null,
    
    cancelText: "Отмена",
    cancelCallback: null,
    
    //public
    storageHelper: null,

    //private
    formPanel: null,

    // private
    initComponent : function(){
        var config = this.initialConfig;

        //автоматическая генерация id компонентов
        if(!Ext.isEmpty(this.storageHelper)) {
            for(var i=0;i<this.items.length;i++) {
                var item = this.items[i];
                item.id = this.storageHelper._genId();
            }
        }

        this.height = Ext.num(this.height, '250');
        this.width = Ext.num(this.width, '400');

        this.formPanel = new Ext.FormPanel( {
                bodyStyle:'padding:5px',
                width: this.width - 50,
                height: this.height - 50,
                labelWidth: this.labelWidth,
                defaultType: this.defaultType,
                border:false,
                items: this.items
            }
        );
        
        this.defaultType = null;
        this.labelWidth = null;

        this.setupLabels();
        
        this.items = [this.formPanel];
        this.closable = false;
        this.layout = 'fit';
        
        Vida.DataForm.superclass.initComponent.call(this);
        
        if(Ext.isEmpty(this.cancelCallback)) {
            this.cancelCallback = this.onCancel.createDelegate(this);
        }

        if(Ext.isEmpty(this.acceptCallback)) {
            this.acceptCallback = this.onAccept.createDelegate(this);
        }
        
        this.on('beforeshow', this.syncData, this);

        //добавить кнопки обработчики к форме
        this.addButton({
            text: this.acceptText,
            handler: this.acceptCallback
        });
        
        this.addButton({
            text: this.cancelText,
            handler: this.cancelCallback
        });
        
    },
    
    //private
    setupLabels: function() {
        if(this.mode == Vida.DataForm_Mode.EDIT_MODE) {
            if(Ext.isEmpty(this.editTitle)) {
                this.setTitle("Изменение текущей записи");
            } else {
                this.setTitle(this.editTitle);
            }
        }
        if(this.mode == Vida.DataForm_Mode.CREATE_MODE) {
            if(Ext.isEmpty(this.createTitle)) {
                this.setTitle("Создание новой записи");
            } else {
                this.setTitle(this.createTitle);
            }
        }
        if(this.mode == Vida.DataForm_Mode.FILTER_MODE) {
            if(Ext.isEmpty(this.filterTitle)) {
                this.setTitle("Поиск записей по критериям");
            } else {
                this.setTitle(this.filterTitle);
            }
        }
    },
    
    //private    
    onCancel: function () {
        this.storageHelper.setToolbarState(true);
        this.hide()
        if( this.mode == Vida.DataForm_Mode.FILTER_MODE ) {
            var store = this.storageHelper._getStore();
            if(!Ext.isEmpty(store)) {
                var _params = {start: 0 };
                store.baseParams = {task: "LISTING"};
                var pager = this.storageHelper.grid.getBottomToolbar();
                if(!Ext.isEmpty(pager) && pager instanceof Ext.PagingToolbar) {
                    _params.limit = pager.pageSize;
                }
                store.reload({params: _params}); //hack: clearing previous request parameters
            }
        }
        
    },
    
    //private
    _getValues: function() {
        var form = this.formPanel.getForm();
        if(form.isValid()) {
            var data = form.getValues();
            var params = {};
            if(!Ext.isEmpty(this.primaryKey)) {
                params[this.storageHelper.primaryKey] = this.primaryKey;
            }
            for(prop in data) {
                var field = form.findField(prop);
                if(Ext.isEmpty(data[prop]) && field.allowBlank) {
                    params[prop] = '';
                } else if (!Ext.isEmpty(data[prop])) {
                    params[prop] = data[prop];
                }
            }
            return params;
        }
        return null;
    },

    //private    
    onAccept: function () {
        var data = this._getValues();
        if(Ext.isEmpty(data)) {
            return;
        }        
        if(this.mode == Vida.DataForm_Mode.EDIT_MODE || this.mode == Vida.DataForm_Mode.CREATE_MODE) {
            this.storageHelper.updateEntity(data);
            this.hide();
        } else if( this.mode == Vida.DataForm_Mode.FILTER_MODE ) {
            var store = this.storageHelper._getStore();
            if(!Ext.isEmpty(store)) {
                store.baseParams['task'] = 'SEARCH';
                //перекладывание данных в параметры запроса
                Ext.apply(store.baseParams, data);
                store.reload();
            }
        }
        this.storageHelper.setToolbarState(true);
    },
    
    //public
    displayForm: function (disable) {
        if(!this.isVisible()){
            if(this.mode != Vida.DataForm_Mode.EDIT_MODE) {
                this.formPanel.getForm().reset();
            }
            this.show();
        } else {
            this.toFront();
        }
        if(Ext.isEmpty(disable)) {
            disable = true;
        }
        if(disable && this.mode != Vida.DataForm_Mode.FILTER_MODE) {
            this.storageHelper.setToolbarState(!disable);
        }
    },    
    
    /**
     * Synchronizing form data
     */
    syncData: function () {
        if(this.mode == Vida.DataForm_Mode.CREATE_MODE || this.mode == Vida.DataForm_Mode.EDIT_MODE) {
            this.formPanel.getForm().reset();
        }
        if((this.mode == Vida.DataForm_Mode.EDIT_MODE || this.mode == Vida.DataForm_Mode.VIEW_MODE) && !Ext.isEmpty(this.storageHelper)) {
            var params = {};
            params[this.storageHelper.primaryKey] = this.primaryKey;
            this.formPanel.disable();
            this.storageHelper.fetchEntity(params, this.loadData.createDelegate(this))
        }
        
        if(this.mode == Vida.DataForm_Mode.CREATE_MODE) {
            if(typeof this.setupView == 'function') {
                this.setupView(this.formPanel.getForm(), this.mode);
            }
        }
    },
    
    //private load data callback method
    loadData: function(obj, data) {
        //alert(Object.toJSON(data));
        this.formPanel.getForm().setValues(data);
        this.formPanel.enable();
        if(typeof this.setupView == 'function') {
            this.setupView(this.formPanel.getForm(), this.mode);
        }
    },
    
    /**
     * Form state variable
     * @type {Number}
     */
    mode: Vida.DataForm_Mode.VIEW_MODE,
    
    /**
    * Set form mode
    * @param {Number} _mode
    * @return none
    */
    setMode: function (_mode) {
        if(this.mode == _mode) {
            return;    
        }
        this.mode = _mode;
        if(this.mode == Vida.DataForm_Mode.CREATE_MODE) {
            this.primaryKey = 0;
        }
        if(this.mode == Vida.DataForm_Mode.FILTER_MODE) {
            this.primaryKey = null;
        }
        this.setupLabels();
    },
    
    /**
    * Setup form's controls state
    * @param {Number} _mode
    * @return none
    */
    setupView: Ext.emptyFn
    
});
Ext.reg('dataform', Vida.DataForm);

/**
 * @class StorageHelper
 * Class-helper for GridPanel change data request
 * @singleton
 */
Vida.StorageHelper = function(url, grid){
    
    this.sequence = 1;
    
    /**
     * Url to send data-change request
     * @type String
     */
    this.url = url;
    
    /**
     * Grid objects to attach
     * @type Ext.grid.EditorGridPanel
     */
    this.grid = grid;
    
    /**
     * Form for editing new entity
     * @type Ext.FormPanel
     */
    this.editForm = null;
    
    /**
     * Form for search items
     * @type Ext.FormPanel
     */
    this.searchForm = null;

    //вешаем обработчик на редактирование
    this.grid.on('afteredit', (function (obj) { return function (e) { obj.updateHelper(e) } } )(this));

    //добавляем кнопку удаления в тулбар
    var tbar = this.grid.getTopToolbar();
    tbar.addText('Операции:');
    tbar.addButton({
        text: 'Удалить запись',
        tooltip: 'Удаляет выбранные записи таблицы',
        handler: (function (obj) { return function () { obj.deleteEntity() } } )(this)
    });
    
}

Vida.StorageHelper.prototype = {
    fetchTask : 'FETCH',
    updateTask : 'UPDATE',
    deleteTask : 'DELETE',
    /**
     * Primary key column name
     * @type String
     */
    primaryKey : 'id',
    
    //private
    _genId: function() {
        return (this.sequence++).toString();
    },
    
    //private
    _getStore: function() {
        return this.grid.store;
    },
    
    //public
    setToolbarState: function(enabled) {
        if(Ext.isEmpty(enabled)) {
            enabled = true;
        }
        var tbar = this.grid.getTopToolbar();
        if(!Ext.isEmpty(tbar)) {
            if(enabled) { 
                tbar.enable();
            } else {
                tbar.disable();
            }
        }
    },

    /**
     * Add form for search items
     * @param {Ext.FormPanel} form Form
     */
    addSearchForm: function (config) {
        this.searchForm = new Vida.DataForm(
            Ext.apply( {
                acceptText: "Фильтровать",
                bodyStyle:'padding:5px',
                border:false,
                storageHelper: this
            }, config)
        );
        this.searchForm.setMode(Vida.DataForm_Mode.FILTER_MODE);
        
        var tbar = this.grid.getTopToolbar();
        tbar.addSeparator();
        tbar.addButton({
            text: 'Поиск',
            tooltip: 'Расширенный поиск записей по критериям',
            handler: this.filterEntities.createDelegate(this)
        });
        
    },

    //public
    filterEntities: function() {
        this.searchForm.displayForm(true);
    },

    /**
     * Add form for create new entity
     * @param {Ext.FormPanel} form Form
     */
    addEditForm: function (config) {
        if(Ext.isEmpty(config)) {
           return;
        }

        this.editForm = new Vida.DataForm(
            Ext.apply( {
                bodyStyle:'padding:5px',
                border:false,
                storageHelper: this
            }, config)
        );
        
        var tbar = this.grid.getTopToolbar();
        tbar.insertButton(2, {
            text: 'Добавить запись',
            tooltip: 'Создать новую запись',
            handler: this.createEntity.createDelegate(this)
        });
        tbar.insertButton(3, new Ext.Toolbar.Separator());
        tbar.insertButton(4, {
            text: 'Изменить запись',
            tooltip: 'Измение выбранной записи',
            handler: this.editEntity.createDelegate(this)
        });
        tbar.insertButton(5, new Ext.Toolbar.Separator());
    },

    //public
    createEntity: function() {
        this.editForm.setMode(Vida.DataForm_Mode.CREATE_MODE);
        this.editForm.displayForm(true);
    },
    
    //public    
    editEntity: function() {
        var data = {};
        var selections = this.grid.selModel.getSelections();
        if(this.grid.selModel.getCount() == 1 && !Ext.isEmpty(this.editForm)) {
            this.editForm.primaryKey = selections[0].json[this.primaryKey];
            this.editForm.setMode(Vida.DataForm_Mode.EDIT_MODE);
            this.editForm.displayForm(true);
        } else {
            var message = "Не выделенно ни одной строки для редактирования";
            if(this.grid.selModel.getCount() > 1) {
                message = "Одновременно можно изменять только одну строку";
            }
            Ext.MessageBox.alert('Ошибка', message);
        }
    },
    
    /**
     * Making AJAX request to for data
     * @param {String} task Task name 
     * @param {Mixed} data Data to send with request
     */
    requestEx: function (task, data, callback) {
        var _params = {};
        
        //нечего сохранять
        if(Ext.isEmpty(data)) {
            return;
        }
        _params.task = task;
        //перекладывание данных в параметры запроса
        for(prop in data) {
            _params[prop] = data[prop];
        }
        Ext.Ajax.request({   
           waitMsg: 'Пожалуйста, подождите...',
           url: this.url,
           params: _params,
           success: (function (obj, callback) { return function (response) {
                var res=Ext.util.JSON.decode(response.responseText);
                if(!Ext.isEmpty(res)) {
                   callback(obj, res);
                } else {
                   Ext.MessageBox.alert('Ошибка', 'Ошибка выборки/модификации данных');
                }
           } } )(this, callback),
           failure: function(response){
              var result=response.responseText;
              Ext.MessageBox.alert('Ошибка','Невозможно выполнить операцию выборки/модификации данных. Пожалуйста, попробуйте повторить запрос через некоторое время');
           }									    
        });   
    },
    
    /**
     * Making AJAX request to fetch data
     * @param {Mixed} data oGrid_event to send with request
     */
    fetchEntity: function (params, callback) {
        this.requestEx(this.fetchTask, params, callback);
    },
    
    /**
     * Making AJAX request to change data
     * @param {Mixed} data oGrid_event to send with request
     */
    updateEntity: function (data) {
        this.requestEx(this.updateTask, data,
            function(obj, res) {
                if(Ext.num(res, 0) == 1) {
                    obj.grid.store.commitChanges();
                    obj.grid.store.reload();
                }
            }
        );
    },
    updateHelper: function (oGrid_event) {
        var data = null;
        if(!Ext.isEmpty(oGrid_event) && oGrid_event.record.data){
            data = oGrid_event.record.data;
        }
        this.updateEntity(data);
    },
    
    /**
     * Making AJAX request to delete data
     * @param {Mixed} data Data to send with request
     * Note: "хитрый вызов" подсмотрен http://stackoverflow.com/questions/789675/how-to-get-class-objects-name-as-a-string-in-javascript/789720#789720
     */
    deleteEntity: function () {
        if(this.grid.selModel.getCount() == 1) {
            Ext.MessageBox.confirm('Подтверждение','Вы действительно хотите удалить эту запись?',
                (function (obj) { return function (btn) { obj.deleteHelper(btn) } } )(this) );
        } else if(this.grid.selModel.getCount() > 1) {
            Ext.MessageBox.confirm('Подтверждение','Вы действительно хотите удалить отмеченные записи?',
                (function (obj) { return function (btn) { obj.deleteHelper(btn) } } )(this) );
        } else {
            Ext.MessageBox.alert('Ошибка','Не выделенно ни одной строки для удаления');
        }
    },
    deleteHelper: function (btn) {
        if(btn == 'yes') {
            var data = {};
            var selections = this.grid.selModel.getSelections();
            var entities = [];
            for(i = 0; i< this.grid.selModel.getCount(); i++){
                entities.push(selections[i].json.id);
            }
            data.ids = Ext.encode(entities);
            this.requestEx(this.deleteTask, data,
                function(obj, res) {
                    if(Ext.num(res, 0) == 1) {
                        obj.grid.store.commitChanges();
                        obj.grid.store.reload();
                    }
                }
            );
        }
    }
}    
