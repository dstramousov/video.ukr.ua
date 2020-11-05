Ext.ns('Vida');

var __blankText = 'Поле не может быть пустым';

/**
 *
 */
Ext.apply(Ext.form.VTypes, {
    daterange : function(val, field) {
        var date = field.parseDate(val);

        if(!date){
            return;
        }
        if (field.startDateField && (!this.dateRangeMax || (date.getTime() != this.dateRangeMax.getTime()))) {
            var start = Ext.getCmp(field.startDateField);
            start.setMaxValue(date);
            start.validate();
            this.dateRangeMax = date;
        } 
        else if (field.endDateField && (!this.dateRangeMin || (date.getTime() != this.dateRangeMin.getTime()))) {
            var end = Ext.getCmp(field.endDateField);
            end.setMinValue(date);
            end.validate();
            this.dateRangeMin = date;
        }
        /*
         * Always return true since we're only using this vtype to set the
         * min/max allowed values (these are tested for after the vtype test)
         */
        return true;
    },

    password : function(val, field) {
        if (field.initialPassField) {
            var dataPanel = field.findParentByType(Ext.FormPanel);
            var pwd = null;
            if(!Ext.isEmpty(dataPanel)) {
                var form = dataPanel.getForm();
                if(!Ext.isEmpty(form)) {
                    //var pwd = Ext.getCmp(field.initialPassField); //этот подход не работает из-за использования автогенерации Id компонентов формы
                    pwd = form.findField(field.initialPassField);
                }
            }
            return !Ext.isEmpty(pwd) && (val == pwd.getValue());
        }
        return true;
    },

    passwordText : 'Пароли не совпадают'
});

/**
 * @class Vida.TextFieldRemoteVal
 * TextFiled with remote AJAX validation
 */
Vida.TextFieldRemoteVal = function(config){
    Vida.TextFieldRemoteVal.superclass.constructor.call(this, config);
    if( this.urlRemoteVal ) {
        if( this.remoteValidation == 'onValidate' ) {
            this.on('valid', this.startRemoteVal.createDelegate(this));
        }else if( this.remoteValidation == 'onBlur' ) {
            this.on('blur', this.startRemoteVal.createDelegate(this));
        }
    }
};
Ext.extend(Vida.TextFieldRemoteVal, Ext.form.TextField, {
    paramsRemoteVal: { task: 'VALIDATE' },
    remoteValidation: 'onBlur', /* 'onValidate' or 'onBlur' */
    urlRemoteVal: null,
    timeout: 30,    
    method: 'POST',
    badServerRespText: 'Error: bad server response during validation',
    badComText: 'Error: validation unavailable',
    
    // redefinition 
    onRender : function(ct){
        Vida.TextFieldRemoteVal.superclass.onRender.call(this, ct);
        
        this.remoteCheckIcon = ct.createChild({tav:'div', cls:'x-form-remote-wait'});
        this.remoteCheckIcon.hide();
    },
    
    // private
    alignRemoteCheckIcon : function(){
        this.remoteCheckIcon.alignTo(this.el, 'tl-tr', [2, 2]);
    },
    
    // private
    getParams: function() {
        var tfp = (this.name||this.id)+'='+this.getValue();
        var p = (this.paramsRemoteVal?this.paramsRemoteVal:'');
        if(p){
            if(typeof p == "object")
                tfp += '&' + Ext.urlEncode(p);
            else if(typeof p == 'string' && p.length)
                tfp += '&' + p;
        }
        return tfp;
    },
    
    // public
    startRemoteVal: function() {
        var v = this.getValue();
        // don't start a remote validation if the value doesn't change (getFocus/lostFocus for example)
        if( this.lastValue != v ) {
            this.lastValue = v;
            if( this.transaction ) {
                this.abort();
            }
            this.alignRemoteCheckIcon();
            this.remoteCheckIcon.show();
            var params = this.getParams();
            this.transaction = Ext.lib.Ajax.request(
                                this.method,
                                this.urlRemoteVal + (this.method=='GET' ? '?' + params : ''),
                                {success: this.successRemoteVal, failure: this.failureRemoteVal, scope: this, timeout: (this.timeout*1000)},
                                params);
        }
        // but if remote validation error, show it! (because validateValue reset it)
        else if( !this.isValid ) {
            this.markInvalid(this.currentErrorTxt);
        }
    },
    
    // public
    abort : function(){
        if(this.transaction){
            Ext.lib.Ajax.abort(this.transaction);
        }
    },
    
    // private
    successRemoteVal: function(response) {
        this.transaction = null;
        this.remoteCheckIcon.hide();
        var result = this.processResponse(response);
        if(result) {
            if(result.errors) {
                this.currentErrorTxt = result.errors;
                this.markInvalid(this.currentErrorTxt);
                this.isValid = false;
            } else {
                this.isValid = true;
            }
        }else{
            this.currentErrorTxt = this.badServerRespText;
            this.markInvalid(this.currentErrorTxt);
            this.isValid = false;
        }
    },
    
    // private
    failureRemoteVal: function(response) {
        this.transaction = null;
        this.remoteCheckIcon.hide();
        this.currentErrorTxt = this.badComText;
        this.markInvalid(this.currentErrorTxt);
        this.isValid = false;
    },
    
    // private
    processResponse: function(response) {
        return (!response.responseText ? false : Ext.decode(response.responseText));
    }

});

Vida.FilterTextField = Ext.extend(Ext.form.TextField, {
    allowBlank: true,
    maxLength: 64,
    minLength: 2,
    minLengthText: 'Текст не может быть менее 2 символов',
    maxLengthText: 'Текст не может быть более 64 символов',
    regex: /^(.{2,64})$/,
    regexText: 'Пароль должен быть не менее 6 символов и не более 20',
    maskRe: /([^\ ]+)$/,
    width: 150,
    blankText: __blankText
});
Ext.reg('filtertextfield', Vida.FilterTextField);

Vida.PasswordField = Ext.extend(Ext.form.TextField, {
    allowBlank: false,
    maxLength: 20,
    minLength: 6,
    minLengthText: 'Пароль не может быть менее 6 символов',
    maxLengthText: 'Пароль не может быть более 20 символов',
    regex: /^(.{6,20})$/,
    regexText: 'Пароль должен быть не менее 6 символов и не более 20',
    maskRe: /([^\ ]+)$/,
    width: 150,
    inputType: 'password',
    blankText: __blankText
});
Ext.reg('passwordfield', Vida.PasswordField);

Vida.EmailField = Ext.extend(Vida.TextFieldRemoteVal, {
    allowBlank: false,
    maxLength: 128,
    regex: /^([a-zA-Z0-9_\.\-]+@[a-zA-Z0-9\-_]+?\.[a-zA-Z0-9]{2,6})$/,
    regexText: 'Введите корректный email адрес',
    maskRe: /([a-zA-Z0-9_\.\-@]+)$/,
    width: 250,
    blankText: __blankText,
    urlRemoteVal: '/storage/users/'
});
Ext.reg('emailfield', Vida.EmailField);
Ext.ComponentMgr.registerType('emailfield', Vida.EmailField);

Vida.NameField = Ext.extend(Ext.form.TextField, {
    allowBlank: false,
    maxLength: 64,
    maskRe: /([а-яА-Яa-zA-Z0-9]+)$/,   // alphanumeric + spaces allowed
    width: 250,
    blankText: __blankText
});
Ext.reg('namefield', Vida.NameField);

Vida.LoginField = Ext.extend(Vida.TextFieldRemoteVal, {
    allowBlank: false,
    maxLength: 32,
    regex: /^((?=[^\.]*\.?[^\.]*)(?=[^_]*_?[^_]*)(?=[^@]*@?[^@]*)[a-zA-Z0-9_]{5,15})$/,
    regexText: 'Введите корректное имя учетной записи',
    maskRe: /([a-zA-Z0-9]+)$/,
    width: 150,
    blankText: __blankText,
    urlRemoteVal: '/storage/users/'
});
Ext.reg('loginfield', Vida.LoginField);

