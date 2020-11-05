function limitText(limitField, limitCount, limitNum) {
    if (limitField.innerHTML.length > limitNum) {
        limitField.innerHTML = limitField.value.substring(0, limitNum);
    } else {
        $('countchars').innerHTML = limitNum - limitField.value.length;
    }
}


function makeAnswer(parent_id){

    $('cid').innerHTML = parent_id;

//    var myElement = $('comments_make_block');
//    var myScrollEffect = new fx.Scroll;
//    myScrollEffect.scrollTo(myElement);

    document.location.href = '#comments_make_block';
    $('comment_body').focus();
}


/**
* Проверяет является ли заданное значение строки числовым
*/
String.prototype.isNumeric = function () {
  if (this == null || !this.toString().match(/^[-]?\d*\.?\d*$/)) return false;
  return true;
}


function init_toggle(container_id) 
{
    var red_alert = new fx.Flash(container_id, {color_from:"#FFFFFF", color_to:"#7f0000", count:2, duration:100});
   red_alert.toggle();
}

/**
 * Классы индикаторов
*/
var Loader = Class.create();
Loader.prototype = {
  initialize: function(container_id, className) {
    var el = $(container_id);
    this.el = null;
    if(!Object.isUndefined(el)) {
        this.el = el;
    }
    this.className = className;
  },
  show: function() {
    if(!Object.isUndefined(this.el)) {
        this.el.addClassName(this.className);
    }
  },
  hide: function() {
    if(!Object.isUndefined(this.el) && this.el.hasClassName(this.className)) {
        this.el.removeClassName(this.className);
    }
  }
};

//full screen loader
var FSLoader = Class.create();
FSLoader.prototype = {
  initialize: function() {
  },
  show: function() {
       var el = $('loader');
       if(Prototype.Browser.IE) {
          var xy = Element.cumulativeOffset($('footer_id'));
          $('loader_shadow').setStyle({'height': xy[1] + 76 + 'px'});
       }
       el.show();
  },
  hide: function() {
        $('loader').hide();
  }
};

var FakeLoader = Class.create();
FakeLoader.prototype = {
  initialize: function() {
  },
  show: function() {
  },
  hide: function() {
  }
};



var Tools = {
    isDebugMode: function () {
        return false;
    },
    processException: function (req, exception) {
        //IE hack outerHTML with null
        if(exception.number == -2146823281) {
            return true;
        }
        var msg = '';
        if(Tools.isDebugMode()) {
            msg = "\n\nResponse object:\n" + (typeof req == "object" && req != null
                    ? Object.toJSON(req)
                    : req
            );
        }
        //alert("The request had a fatal exception thrown.\n\nMessage:\n" + exception.message + msg);
        alert("The request had a fatal exception thrown.\n\nMessage:\n" + Object.toJSON(exception) + msg);
        return true;
    }
} 

/**
 * Смена языка приложения
 * @param lang {string} Какой язык установить
 */
function change_language(lang) {
    ex_setCookie('lang', lang);
    window.location.reload();
}

/**
 * Загрузка данных ошибок в элементы документа
 * @param form {string} Какой action вызвать
 * @param values {string}
 */
function populate_errors(errors) {
    for(var obj in errors){
        var el = $(obj + '_error');
        if(el != null) {
            try {
                //init_toggle(el.id);
                el.innerHTML = errors[obj];
            } catch (exc) {}
        }
    }
}

/**
 * Загрузка данных в элементы формы
 * @param form {string} Какой action вызвать
 * @param values {string}
 */
function populate_values(form_id, values) {
    var form = $(form_id);
    for(var obj in values){
        var input = form[obj];
        if(input != null && input != undefined) {
            try {
                Form.Element.setValue(input, values[obj]);
            } catch (exc) {}
        }
    }
}

/**
 * Реализует AJAX-запрос на обновление каптчи
 * @param action {string} Какой action вызвать
 * @param message {string}
 */
function renew_captcha(captcha, captcha_id) {
    ajax_request('/index/captcha', {}, null,
        function(res) {
            res = eval('(' + res + ')');
            $(captcha).innerHTML = res.captcha;
            $(captcha_id).value = res.captcha_id;
        }
    );
}

/**
 * Реализует обработку
 * @param container {integer}
 * @param callback {function}
 */
function keydown_ex(container, callback) {
    Element.childElements($(container)).each(function(el) {
        if(Element.childElements(el).size() > 0) {
            keydown_ex(el, callback);
        } else {
            if(el.match('input')) {
               Element.observe(el, 'keypress',
                function(event) {
                    if (event.keyCode == Event.KEY_RETURN) {
                        callback();
                    }
               }); 
            }
        }
    });
}

/**
 * Реализует AJAX-запрос
 * @param action {string} Какой action вызвать
 * @param message {string}
 */
function ajax_request(action, params, message, callback, loader) {
    if (message == null || confirm(message)) {
        if(Object.isUndefined(loader)) {
            loader = new FSLoader();
        }
        loader.show();
        new Ajax.Request(action, {
            contentType: 'application/x-www-form-urlencoded',
            method: 'post',
            parameters: params,
            onComplete: function(transport) {
                if (200 == transport.status) {
                    if(callback != null) {
                        callback(transport.responseText);
                    }
                } else {
                    if(callback != null) {
                        callback(null);
                    }
                }
                loader.hide();
                return true;
            },
            onException: function(req, exception) {
                loader.hide();
                return Tools.processException(req, exception);
            }
        });
    }
}


//http://habrahabr.ru/blogs/javascript/14481/
onReady = (function(ie){
 var d = document;
 return ie ? function(c){
   var n = d.firstChild,
    f = function(){
     try{
      c(n.doScroll('left'))
     }catch(e){
      setTimeout(f, 10)
     }
    }; f()
  } : 
  /webkit|safari|khtml/i.test(navigator.userAgent) ? function(c){
   var f = function(){
     /loaded|complete/.test(d.readyState) ? c() : setTimeout(f, 10)
    }; f()
  } : 
  function(c){
   d.addEventListener("DOMContentLoaded", c, false);
  }
})(/*@cc_on 1@*/);

Object.extend(Event, {
    onReady : function(f) {
        onReady(f);
    }
});

String.prototype.trim = function () {
    var str = this;
    str = str.replace(/^\s+/, '');
    for (var i = str.length - 1; i >= 0; i--) {
            if (/\S/.test(str.charAt(i))) {
                    str = str.substring(0, i + 1);
                    break;
            }
    }
    return str;
}

/**
 * Функции работы с cookies
**/
function ex_setCookie(c_name,value,expiredays) {
    var exdate=new Date();
    exdate.setDate(exdate.getDate()+expiredays);
    document.cookie=c_name+ "=" +escape(value)+
    ((expiredays==null) ? "" : ";expires="+exdate.toGMTString());
}

function ex_getCookie(c_name) {
    if (document.cookie.length>0)
      {
      c_start=document.cookie.indexOf(c_name + "=");
      if (c_start!=-1)
        { 
        c_start=c_start + c_name.length+1; 
        c_end=document.cookie.indexOf(";",c_start);
        if (c_end==-1) c_end=document.cookie.length;
        return unescape(document.cookie.substring(c_start,c_end));
        } 
      }
    return "";
}
