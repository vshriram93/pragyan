/**
 * @package pragyan
 * @author Boopathi Rajaa
 * @copyright (c) 2011 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
var form2 = (function () {
	return function(){
		
	};
})();
//The possible form elements
//Rules are defined in the later part
form2.possible = ["text", "time", "date", "checkbox", "file", "time", "number", "phone", "email", "name", "password", "ip", "list", "radio"];

//Add a new form field - action for onclick event of the "Add New Element Button"
form2.addNewFormField = (function() {
	//a simple counter - static behaviour of count variable
	var c = (function(){
		var count = 0; 
		return {
			inc: function() { //this increments
				count++;
			},
			get: function() { //and this returns the counted value
				return count;
			}
		}
	})();
	function newField() {
		var i = c.get();
		var cover = document.createElement("div");
		
		//a temporary array to store the input fields required for one form element
		var el = [];
		
		//a temporary variable to cache the form field until it is pushed to the array
		var tmp;
		
		//FIELD TEXT
		tmp = document.createElement("input");
		setAttributes(tmp, {
			type: "text",
			"name": "fieldText" + i
		});
		el.push(tmp);
		
		//VARIABLE NAME
		tmp = document.createElement("input");
		setAttributes(tmp, {
			type: "text",
			"name": "fieldName" + i 
		});
		el.push(tmp);
		
		//FIELD TYPE
		tmp = document.createElement("select");
		setAttributes(tmp, {
			"name": "fieldType" + i
		});
		for(i in form2.possible) {
			var temp = document.createElement("option");
			setAttributes(temp,{"value": form2.possible[i]});
			temp.innerHTML = form2.possible[i];
			tmp.appendChild(temp);
		}
		el.push(tmp);
		
		
		//Add all the elements in the temporary array to the cover.
		for(j=0;j<el.length;j++)
			cover.appendChild(el[j]);
			
		//return the cover, type=DOMElement
		return cover;
	}
	return function (x){
		c.inc();
		var i = c.get();
		document.getElementsByClassName(x)[0].appendChild(newField());
	};
})();
