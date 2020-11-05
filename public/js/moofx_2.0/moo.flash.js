fx.Flash = Class.create();
Object.extend(Object.extend(fx.Flash.prototype, fx.Base.prototype), {	
	hD: "0123456789ABCDEF",
	
	initialize: function(el, options) {

		this.el = $(el);

		var color_from = (options && options.color_from) || "#ffffff";
		var color_to = (options && options.color_to) || "#ff0000";
		var color_f = this.h2d(color_from.substr(1));
		var color_t = this.h2d(color_to.substr(1));
		
		var _options = {
			red: [color_f >> 16, color_t >> 16],
			green: [(color_f >> 8) & 255, (color_t >> 8) & 255],
			blue: [color_f & 255, color_t & 255],
			count: 1
		};
		Object.extend(_options, options || {});
		if (_options.onComplete) _options.flashOnComplete = _options.onComplete;
		this.setOptions(_options);
	},
	
	increase: function() {
		var r = this.d2h(this.now * (this.options.red[0] - this.options.red[1]) / 255 + this.options.red[1]);
		var g = this.d2h(this.now * (this.options.green[0] - this.options.green[1]) / 255 + this.options.green[1]);
		var b = this.d2h(this.now * (this.options.blue[0] - this.options.blue[1]) / 255 + this.options.blue[1]);
		this.el.style.backgroundColor = "#" + r + g + b;
	},

	toggle: function() {
		if (this.flashCount == undefined) this.flashCount = this.options.count;
		this.options.onComplete = this.onComplete.bind(this);
		this.custom(255, 0);
	},
	
	onComplete: function() {
		this.flashCount--;
		if (this.flashCount == 0)
		{
			this.flashCount = undefined;
			this.options.onComplete = this.options.flashOnComplete;
		} else
			this.options.onComplete = this.toggle.bind(this);
		this.custom(0, 255);
	},
	
	d2h: function(d) {
		var h = this.hD.substr(d & 15, 1);
		while (d > 15) { d >>= 4; h = this.hD.substr(d & 15, 1) + h; }
		if (h.length == 1) h = "0" + h;
		return h;
	},
	
	h2d: function(h) {
		return parseInt(h, 16);
	}
});

