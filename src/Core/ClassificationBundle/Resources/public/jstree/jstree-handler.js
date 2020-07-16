"use strict";

function JsTreeHandler($targetElement) {
	if (!$targetElement) return;

	this.$targetElement = $targetElement;

	var options = this.buildOptions();

	this.getJsTreeContainer()
			.jstree(options)
			.on('changed.jstree', this.keepScope(this.onChanged))
			.on('select_node.jstree', this.keepScope(this.onSelectNode));

};

JsTreeHandler.prototype.keepScope = function(methodRef) {
	var self = this;
	return function() { return methodRef.apply(self, arguments); }
}

JsTreeHandler.prototype.getJsTreeContainer = function() {
	return this.$targetElement;
}

JsTreeHandler.prototype.fetchNodes = function() {
	return this.$targetElement.data('nodes');
};

JsTreeHandler.prototype.substitute = function(string, data) {
	var replacer = function(str, p1, offset, s) {
		return data[p1] | p1;
	}
	return string.replace(/{([a-zA-Z0-9]*)}/g, replacer);
}

JsTreeHandler.prototype.buildData = function(parsedJSON, ifNodeOpened) {

	if (!parsedJSON) return [];
	var i = parsedJSON.length;
	while(i--){
		var node = parsedJSON[i];
		
		//check if node.parent is null, set to # for root elements
		if(node.parent === null || node.parent === 'undefined'){
			//console.log("before: ", node);
			node.parent = '#';
			//console.log("after: ", node);
		};
		

		//if node state doesn't exist, add as new prop
		//then change opened node state to true
		node.state = node.state || {};
		node.state.opened = node.state.opened || ifNodeOpened || true;

		node.icon = 'fa fa-gear nth-icon';
	};
	return parsedJSON;
};

JsTreeHandler.prototype.getSelectedNodePath = function(id){
	var path = [],
		nodes = this.fetchNodes(),
		findParent = function(id){
			var id = id;
			for(var i = 0, max = nodes.length;i<max;i++){
				var node = nodes[i];
				if(node.id == id){
					if(node.parent !== '#'){
						path.push(node.text);
						findParent(node.parent);
					}else{
						path.push(node.text);
						return;
					};
				};
			};
		};
	findParent(id);	
	return path.reverse();
};

JsTreeHandler.prototype.onChanged = function(e, data) {  //	event listeners for selected items. http://www.jstree.com/docs/events
	var i, j, r = [];
	for(i = 0, j = data.selected.length; i < j; i++) {
		//	node ID can also being selected:
		//	data.instance.get_node(data.selected[i]).id
		r.push(data.instance.get_node(data.selected[i]).text);
	};
	//console.log('Selected: ' + r.join(', '));
};

JsTreeHandler.prototype.onSelectNode = function(e, data) {
}

JsTreeHandler.prototype.getRequiredPlugins = function () {
	return [
		"types",
//		"checkbox",
		"unique"
	];
};

JsTreeHandler.prototype.buildOptions = function() {
	return {
		'core': { 'data': this.buildData(this.fetchNodes()) },
		'plugins' : this.getRequiredPlugins()
	}
};


JsTreeHandler.prototype.buildPathLink = function(){
	var self = this;
	return $("<a />", {
			href: '#',
			class: 'tree-path',
			text: this.getSelectedNodePath(this.getInputValue()).join(" / ")
		});
};

JsTreeHandler.prototype.updatePathLink = function(event){
	event.preventDefault();
	event.stopPropagation();

	this.$pathlink.text(this.getSelectedNodePath(this.getInputValue()).join(" / "));	
	this.$pathlink.trigger('click');
};


// --------- sitemap ---------
JsTreeHandler_Sitemap.prototype = new JsTreeHandler();

function JsTreeHandler_Sitemap() {
	if (0 === arguments.length) return;
	JsTreeHandler.apply(this, arguments);
}

JsTreeHandler_Sitemap.prototype.onSelectNode = function(e, data) {
	var href = this.$targetElement.data('href');

	if (href) {
		href = this.substitute(href, data.node);
		if (href && '#' !== href) {
			document.location.assign(href);
		}
	}
};

// --------- input ---------
JsTreeHandler_Input.prototype = new JsTreeHandler();

function JsTreeHandler_Input() {
	if (0 === arguments.length) return;
	JsTreeHandler.apply(this, arguments);

	var input = this.getInputElement();
	input.on('change keyup', this.keepScope(this.onInputChange));
	this.updateJsTreeSelectedNodes(this.getInputValue());

}

JsTreeHandler_Input.prototype.updateJsTreeSelectedNodes = function(nodeIds) {
	this.getJsTreeContainer().jstree('deselect_all');
	if (nodeIds) {
		this.getJsTreeContainer().jstree('select_node', nodeIds);
	}
}


JsTreeHandler_Input.prototype.getInputElement = function() {
	var id = this.$targetElement.data('input-id');
	return $('#'+id);
}

JsTreeHandler_Input.prototype.getInputValue = function() {
	return this.getInputElement().val();
}

JsTreeHandler_Input.prototype.setInputValue = function(value) {
	var input = this.getInputElement();
	input.val(value);
}

JsTreeHandler_Input.prototype.onInputChange = function() {
	this.updateJsTreeSelectedNodes(this.getInputElement().val());
}

JsTreeHandler_Input.prototype.getJsTreeContainer = function() {
	var $container;
	if (!($container = this.$targetElement.find('.jstree-container')).length) {
		$container = $('<div class="jstree-container"></div>');
		this.$targetElement.append($container);
	}

	return $container;
}


// --------- input/single ---------
JsTreeHandler_Input_Single.prototype = new JsTreeHandler_Input();

function JsTreeHandler_Input_Single() {
	if (0 === arguments.length) return;
	JsTreeHandler_Input.apply(this, arguments);
	this.$pathlink = this.buildPathLink();
	this.renderPathLink();
};

JsTreeHandler_Input_Single.prototype.renderPathLink = function(){
	var self = this;
	
	//	$pathlink already in memory. prepend and render:
	this.$targetElement.prepend(this.$pathlink);

	// init: hide tree, attach click handler to $pathlink
	this.$targetElement.find('.jstree-container-ul').hide();
	
	this.$pathlink
		.on('click', this.keepScope(this.showHidePathLink));

	this.$targetElement
		.on('click', this.keepScope(this.updatePathLink));

	/*
	this.$targetElement.find('.jstree-container-ul').hide();
	var self = this;
		this.$pathlink.on('click', function(event){
			event.preventDefault();
			event.stopPropagation();
			$(this).hide();
			self.$targetElement.find('.jstree-container-ul').show(600);
		});
	self.$targetElement.prepend(this.$pathlink);*/
	
};

JsTreeHandler_Input_Single.prototype.showHidePathLink = function(event){
	event.preventDefault();
	event.stopPropagation();

	this.$pathlink = $(event.currentTarget);

	if(this.$targetElement.find('.jstree-container-ul').is(":visible")){
			this.$pathlink.show(600);
			this.$targetElement.find('.jstree-container-ul').hide(600);
	}else{
			//this.$pathlink.hide(600);
			this.$targetElement.find('.jstree-container-ul').show(600);
	};
};


JsTreeHandler_Input_Single.prototype.onSelectNode = function(e, data) {
	this.setInputValue(data.node.id);
};

// --------- input/multiple ---------
JsTreeHandler_Input_Multiple.prototype = new JsTreeHandler_Input();

function JsTreeHandler_Input_Multiple() {
	if (0 === arguments.length) return;
	JsTreeHandler_Input.apply(this, arguments);

	this.getInputElement().removeClass('select2-offscreen').css('height', '300px'); // TODO: remove, only for debug
}

JsTreeHandler_Input_Multiple.prototype.onChanged = function(e, data) {
	var selectedIds = data.selected;
	this.setInputValue(selectedIds);
};

JsTreeHandler_Input_Multiple.prototype.getRequiredPlugins = function () {
	return [
		"types",
		"checkbox",
		"unique"
	];
};



// --------- initializer ---------
(function($, window) {
	var document = window.document,
			init = function() {
				var $container = $(this),
					protoType = $container.data('prototype') ? 'JsTreeHandler_' + $container.data('prototype') : 'JsTreeHandler';

				if (!$container.is('.jstree-initialized')) {
					$container
							.data('jstree-handler', new window[protoType]($container))
							.addClass('jstree-initialized');
				}
			},
			search = function(container){
				$('.jstree', container).each(init);
			};

	$(document).ready(function(){
		search(document);
	});

	$(document).on('sonata-admin-append-form-element', function(e) {
		search(e.target);
	});
})(jQuery, window);
