var addNewFormField = (function() {
	var c = (function(){
		var count = 0;
		return {
			inc: function() {
				count++;
			},
			get: function() {
				return count;
			}
		}
	})();
	function newField() {
		var i = c.get();
		var cover = document.createElement("div");
		
		var el = [];
		var tmp;
		tmp = document.createElement("input");
		setAttributes(tmp, {
			type: "text",
			"name": "fieldName" + i 
		});
		el.push(tmp);
		
		tmp = document.createElement("select");
		setAttributes(tmp, {
			"name": "fieldType" + i
		});
		var possible = ["text", "time", "date", "checkbox", "file", "time", "number", "phone", "email", "name", "password", "ip", "list", "radio"];
		for(i in possible) {
			var temp = document.createElement("option");
			setAttributes(temp,{"value": possible[i]});
			temp.innerHTML = possible[i];
			tmp.appendChild(temp);
		}
		el.push(tmp);
		
		for(j=0;j<el.length;j++)
			cover.appendChild(el[j]);
		return cover;
	}
	return function (x){
		c.inc();
		var i = c.get();
		document.getElementsByClassName(x)[0].appendChild(newField());
	};
})();

(function() {

var form2 = function() {
	var args = typeof arguments[0]
	this.type = arguments[0].type || "text";
};

form2.prototype = {
	_get: function() {
		
	},
	_formatText: function() {
		
	},
	_formatList: function() {
	
	},
	_formatRadio: function() {
	
	},
	_formatFile: function() {
	
	}
}

})();
