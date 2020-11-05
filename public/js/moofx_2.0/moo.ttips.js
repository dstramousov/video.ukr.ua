/*
Script: moofilm.js
Mootools based Tooltip Fader for Images
MooTools 1.2 Beta 2 required

License:
 MIT-style license.

Author:
 janlee <email@webhike.de>
 <http://webhike.de/scripts/dd/moofilm.html>
 <http://webhike.de/moo>

Changelog:

v.0.5 Version [7 Jun. 08]
- MooFilm works fine!
- URL changed to netjard.de/labs/moofilm


v.0.42 Version [23 Mar. 08]
- FX-options: update fx_text and fx_text_prepare renamed to slidein
- new effect: slideout 
- linkname as option

v.0.4 Beta Version [20 Mar. 08]
- the intern construct class to generate the film html "on the fly" to prevent some hide bugs
- useaslink and grabtitle options

v.0.3 Version [17 Feb. 08]
- alpha as option
- Effects
- beforeShow-Event

v.0.1 Basic Version [10 Feb. 08]
- Shows a Pane with Image Title, Link and Info
- Panelink option valueing the target property
- Show/Hide Events
*/


var MooFilm = new Class ({

   Implements: [Events, Options, Chain],

   options: {
      pane: 'moofilm',										   //the class of the generated film-container
      items: 'use_film',										//class of the element, thet will be extendet with MooFilm
      infoclass: 'use_credits',								//grab the inner html/text that will appear on the filmpane

      beforeShow: $empty,
      onShow: $empty,
      onHide: $empty,
      
      alpha: 0.8,												
      fx_alpha_fade: { duration:500, transition:'cubic:out' },		
      slidein: { duration:500, transition:'cubic:out', top:-40, left:0, opacity:0 },  //prepare the slidein/slideout-directions for the contents
      slideout: { duration:500, transition:'linear', top:5, left:0, opacity:0 },     
      
      useaslink: true, // keep a href								//use the filmpane as link
      linkname: '<i>Link</i>',                        //the name of the link (can be emtpy)
      grabtitle: true											//set true to use the title-property of the link as title
   },
   

   initialize: function (options) {	
      this.setOptions (options);
      this.fx = [];
      this.active = $empty;		
      $$('.'+this.options.items+' img').addEvent ('mouseenter', this.show.bind(this));
   },


   cunstruct: function () {
      
      this.subpane = new Element ('div', {'class':this.options.pane, 
         'styles': {position:'absolute', 'z-index':999, left:-1000, top:-1000, display:'none', opacity:0}});
      this.pane = this.subpane.clone();
      this.pane.setStyles ({ 'background-color':'transparent', 'border':'0px none' });
      this.pane.adopt ([
         new Element ('div', {'class':'film_title'}),
         new Element ('div', {'class':'film_info'}),
         new Element ('a', {'class':'film_link', 'href':'#', 'html':this.options.linkname})
      ]);
      
      $(document.body).adopt (this.subpane, this.pane);
      this.pane.addEvent ('mouseleave', this.hide.bind(this));
   },


   show: function (event) {  //console.log (event);

      event.stop();
      if ($(event.target).get('tag')!='a') {
         var itemPar = $(event.target).getParent();
         var item = $(event.target);
      }
      else {
         var itemPar = $(event.target);
         var item = $(event.target).getChildren()[0];
      }
      this.active = itemPar;
      
      if ($$('.'+this.options.pane)) $$('.'+this.options.pane).destroy();
      this.cunstruct();
      
      this.fireEvent('beforeShow', this.active, itemPar, event);
      
      // ! getPosition Mootools beta 2 buggy width css-borders in ie  //console.log (item.getPosition().x, item.getCoordinates().left);
      
      this.subpane.setStyles ({
         left: item.getCoordinates().left,
         top: item.getCoordinates().top,
         height: item.getSize().y,
         width: item.getSize().x
      });
      
      this.pane.setStyles ({
         left: item.getCoordinates().left - item.getStyle('margin-left').toInt(),
         top: item.getCoordinates().top - item.getStyle('margin-top').toInt(),
         height: item.getSize().y + item.getStyle('margin-right').toInt(),
         width: item.getSize().x + item.getStyle('margin-bottom').toInt()
      });

      //linkin
      if (this.options.useaslink && itemPar.get('href')) {
         this.pane.setStyle('cursor','pointer');
         this.pane.onclick = function () { 
            if (!itemPar.get('target')) itemPar.set('target', '_self');
            window.open(itemPar.get('href'),itemPar.get('target'));
         };
      }
      else this.pane.onclick = function () { return false; };

      //console.log (itemPar, itemPar.getProperties('title', 'href', 'target', 'info'));		
      var properties = itemPar.getProperties('title', 'href');
      
      if (this.options.grabtitle && properties.title && this.pane.getElement('.film_title')) {
         this.pane.getElement('.film_title').set('text', properties.title);
         this.pane.getElement('.film_title').setStyle('display', 'block');
         //itemPar.removeProperty('title');
      } 
      else if (this.pane.getElement('.il_title')) 
         this.pane.getElement('.il_title').setStyle('display', 'none');
      
      if (properties.href && this.pane.getElement('.film_link')) {         
         this.pane.getElement('.film_link').set('href', properties.href);
         this.pane.getElement('.film_link').set('target', properties.target);
         this.pane.getElement('.film_link').setStyle('display', 'inline');
      } 
      else if (this.pane.getElement('.film_link')) 
         this.pane.getElement('.film_link').setStyle('display', 'none');
      
      if (itemPar.getElement('.'+this.options.infoclass) && this.pane.getElement('.film_info')) {
         this.pane.getElement('.film_info').set('html', itemPar.getElement('.'+this.options.infoclass).get('html'));
         this.pane.getElement('.film_info').setStyle('display', 'block');
      } 
      else if (this.pane.getElement('.film_info')) 
         this.pane.getElement('.film_info').setStyle('display', 'none');
      
      
      //fade effects
      this.subpane.style.display = 'block';
      this.pane.style.display = 'block';		
      var coords = this.pane.getCoordinates(); 
      this.fx[0] = new Fx.Morph(this.subpane, this.options.fx_alpha_fade).set({opacity:0}).start({opacity:this.options.alpha});
      this.fx[1] = new Fx.Morph(this.pane, this.options.slidein).set 
         ({top:coords.top + this.options.slidein.top, left:coords.left + this.options.slidein.left, opacity:this.options.slidein.opacity}).start
         ({top:coords.top, left:coords.left, opacity:1});

      this.fireEvent('onShow', this.active, itemPar, event);
   },


   hide: function (event) {  //console.log (event, this.pane);

      this.fx[0].cancel(); this.fx[1].cancel();
      this.pane.removeEvent ('mouseleave', this.hide.bind(this));      

      var coords = this.pane.getCoordinates(); 
      this.fx[2] = new Fx.Morph(this.subpane, this.options.slideout).start({opacity:this.options.slideout.opacity});
      this.fx[3] = new Fx.Morph(this.pane, this.options.slideout).start(
         {top:coords.top + this.options.slideout.top, left:coords.left + this.options.slideout.left, opacity:this.options.slideout.opacity});
      
      //if ($$('.'+this.options.pane)) $$('.'+this.options.pane).destroy();
      this.fireEvent('onHide', this.active, event);
   }	
});


